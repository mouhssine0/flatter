<?php
// Page d'affichage de tous les produits avec pagination
// Intégration de l'API CJdropshipping

// Inclusion des fichiers nécessaires
require_once 'includes/config.php';
require_once 'includes/api.php';
require_once 'includes/functions.php';
require_once 'includes/db_sync.php';

// Récupération du numéro de page depuis l'URL
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page); // S'assurer que la page est au moins 1

// Récupération de la catégorie depuis l'URL
$categoryId = isset($_GET['category']) ? $_GET['category'] : null;

// Nombre de produits par page
$limit = 20;

// Récupération des produits depuis la base de données
// Si une catégorie est spécifiée, filtrer les produits par cette catégorie
$conn = connectDB();
$result = [];

if ($conn) {
    // Récupération du nombre total de produits (avec ou sans filtre de catégorie)
    $countQuery = "SELECT COUNT(*) as total FROM products";
    $countParams = [];
    
    if ($categoryId) {
        $countQuery .= " WHERE categoryId = ?";
        $countParams[] = $categoryId;
    }
    
    $stmt = $conn->prepare($countQuery);
    
    if (!empty($countParams)) {
        $stmt->bind_param(str_repeat('s', count($countParams)), ...$countParams);
    }
    
    $stmt->execute();
    $totalResult = $stmt->get_result();
    $total = $totalResult->fetch_assoc()['total'];
    $totalPages = ceil($total / $limit);
    
    // Calcul de l'offset pour la pagination
    $offset = ($page - 1) * $limit;
    
    // Requête pour récupérer les produits avec pagination et filtre de catégorie
    $query = "SELECT p.*, c.name as category_name 
              FROM products p 
              LEFT JOIN categories c ON p.categoryId = c.id";
    
    $queryParams = [];
    
    if ($categoryId) {
        $query .= " WHERE p.categoryId = ?";
        $queryParams[] = $categoryId;
    }
    
    $query .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
    $queryParams[] = $limit;
    $queryParams[] = $offset;
    
    $stmt = $conn->prepare($query);
    
    // Création du type de paramètres pour bind_param
    $types = str_repeat('s', count($queryParams) - 2) . 'ii'; // 's' pour les strings, 'i' pour les integers (limit et offset)
    $stmt->bind_param($types, ...$queryParams);
    
    $stmt->execute();
    $productResult = $stmt->get_result();
    
    $products = [];
    
    while ($product = $productResult->fetch_assoc()) {
        // Récupération des images pour ce produit
        $imageQuery = "SELECT * FROM product_images WHERE product_id = ? ORDER BY is_main DESC";
        $imageStmt = $conn->prepare($imageQuery);
        $imageStmt->bind_param("s", $product['id']);
        $imageStmt->execute();
        $imageResult = $imageStmt->get_result();
        
        $images = [];
        while ($image = $imageResult->fetch_assoc()) {
            $images[] = $image['image_url'];
        }
        
        // Si aucune image n'est trouvée, utiliser l'image par défaut
        if (empty($images) && !empty($product['image'])) {
            $images[] = $product['image'];
        } elseif (empty($images)) {
            $images[] = 'https://via.placeholder.com/300x300';
        }
        
        // Construction du tableau de produit formaté
        $products[] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'description' => $product['description'] ?? 'Aucune description disponible',
            'image' => $images[0], // Image principale
            'images' => $images,   // Toutes les images
            'price' => floatval($product['price']),
            'category' => $product['category'] ?? $product['category_name'] ?? 'Non catégorisé',
            'stock' => intval($product['stock'])
        ];
        
        $imageStmt->close();
    }
    
    $stmt->close();
    $conn->close();
    
    $result = [
        'products' => $products,
        'total' => $total,
        'currentPage' => $page,
        'totalPages' => $totalPages,
        'error' => null
    ];
} else {
    $result = [
        'products' => [],
        'total' => 0,
        'currentPage' => $page,
        'totalPages' => 0,
        'error' => 'Erreur de connexion à la base de données'
    ];
}
$products = $result['products'];

// Récupération des catégories depuis la base de données pour le filtre
$categories = [];
$conn = connectDB();
if ($conn) {
    $categoryQuery = "SELECT id, name FROM categories ORDER BY name ASC";
    $categoryResult = $conn->query($categoryQuery);
    if ($categoryResult && $categoryResult->num_rows > 0) {
        while ($row = $categoryResult->fetch_assoc()) {
            $categories[] = $row;
        }
    }
    $conn->close();
}

// Inclusion de l'en-tête
require_once 'includes/header.php';
?>

<main class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <?php if ($categoryId): ?>
                <?php 
                // Récupérer le nom de la catégorie sélectionnée
                $categoryName = 'Catégorie';
                foreach ($categories as $cat) {
                    if ($cat['id'] == $categoryId) {
                        $categoryName = $cat['name'];
                        break;
                    }
                }
                ?>
                <h1>Produits: <?php echo htmlspecialchars($categoryName); ?></h1>
                <p class="text-muted">Découvrez notre sélection de produits dans la catégorie <?php echo htmlspecialchars($categoryName); ?></p>
            <?php else: ?>
                <h1>Tous nos produits</h1>
                <p class="text-muted">Découvrez notre sélection de produits de qualité</p>
            <?php endif; ?>
        </div>
        <div class="col-md-4">
            <form action="search.php" method="GET" class="d-flex">
                <input type="text" name="q" class="form-control me-2" placeholder="Rechercher un produit...">
                <button type="submit" class="btn btn-primary">Rechercher</button>
            </form>
        </div>
    </div>
    
    <?php if (!empty($categories)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="category-filter">
                <span class="me-2">Filtrer par catégorie:</span>
                <div class="btn-group">
                    <a href="products.php" class="btn btn-outline-secondary btn-sm <?php echo !$categoryId ? 'active' : ''; ?>">Toutes</a>
                    <?php foreach ($categories as $category): ?>
                        <a href="products.php?category=<?php echo $category['id']; ?>" class="btn btn-outline-secondary btn-sm <?php echo $categoryId == $category['id'] ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="row" id="products-container">
        <?php if (!empty($products) && !isset($result['error'])): ?>
            <?php foreach ($products as $index => $product): ?>
                <div class="col-sm-6 col-md-4 col-lg-3 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $index * 50; ?>">
                    <div class="product-card card h-100">
                        <div class="product-image-wrapper">
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <div class="product-overlay">
                                <a href="product.php?id=<?php echo htmlspecialchars($product['id']); ?>" class="btn btn-sm btn-primary">Voir détails</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title text-truncate"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p class="card-text category-badge"><span class="badge bg-secondary"><?php echo htmlspecialchars($product['category']); ?></span></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <p class="card-text price-tag"><?php echo formatPrice($product['price']); ?> €</p>
                                <p class="card-text stock-info <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                                    <i class="fas <?php echo $product['stock'] > 0 ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                                    <?php echo $product['stock'] > 0 ? 'En stock' : 'Épuisé'; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if ($result['totalPages'] > 1): ?>
                <div class="col-12 mt-4">
                    <nav aria-label="Navigation des pages">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php echo $result['currentPage'] <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="products.php?page=<?php echo $result['currentPage'] - 1; ?><?php echo $categoryId ? '&category=' . urlencode($categoryId) : ''; ?>">
                                    <i class="fas fa-chevron-left"></i> Précédent
                                </a>
                            </li>
                            <?php for ($i = 1; $i <= $result['totalPages']; $i++): ?>
                                <li class="page-item <?php echo $i === $result['currentPage'] ? 'active' : ''; ?>">
                                    <a class="page-link" href="products.php?page=<?php echo $i; ?><?php echo $categoryId ? '&category=' . urlencode($categoryId) : ''; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo $result['currentPage'] >= $result['totalPages'] ? 'disabled' : ''; ?>">
                                <a class="page-link" href="products.php?page=<?php echo $result['currentPage'] + 1; ?><?php echo $categoryId ? '&category=' . urlencode($categoryId) : ''; ?>">
                                    Suivant <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="col-12 text-center">
                <?php if (isset($result['error'])): ?>
                    <div class="alert alert-warning">
                        <h4>Erreur de chargement</h4>
                        <p><?php echo htmlspecialchars($result['error']); ?></p>
                        <?php if ($result['error'] === 'Configuration API manquante'): ?>
                            <p>Veuillez modifier le fichier <code>includes/config.php</code> et remplacer les valeurs par défaut par vos informations d'API.</p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <p>Aucun produit disponible pour le moment.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<script src="js/products.js"></script>

<?php require_once 'includes/footer.php'; ?>