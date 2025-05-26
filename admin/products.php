<?php
// Page de gestion des produits
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth_functions.php';

// Démarrer la session
session_start();

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../login.php");
    exit;
}

// Initialisation des variables
$error = '';
$success = '';
$products = [];
$total = 0;
$currentPage = 1;
$totalPages = 0;
$limit = 10; // Nombre de produits par page

// Récupération de la page courante
if (isset($_GET['page']) && is_numeric($_GET['page'])) {
    $currentPage = (int)$_GET['page'];
}

// Connexion à la base de données
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$conn) {
    $error = "Erreur de connexion à la base de données: " . mysqli_connect_error();
} else {
    // Calcul de l'offset pour la pagination
    $offset = ($currentPage - 1) * $limit;
    
    // Récupération des produits avec pagination
    $query = "SELECT p.*, c.name as category_name, 
             (SELECT image_url FROM product_images WHERE product_id = p.id AND is_main = 1 LIMIT 1) as main_image 
             FROM products p 
             LEFT JOIN categories c ON p.categoryId = c.id 
             ORDER BY p.created_at DESC 
             LIMIT ?, ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $offset, $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    // Récupération des produits
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    
    // Récupération du nombre total de produits
    $countQuery = "SELECT COUNT(*) as total FROM products";
    $countResult = mysqli_query($conn, $countQuery);
    $countRow = mysqli_fetch_assoc($countResult);
    $total = $countRow['total'];
    
    // Calcul du nombre total de pages
    $totalPages = ceil($total / $limit);
    
    // Traitement de la suppression d'un produit
    if (isset($_POST['delete_product']) && isset($_POST['product_id'])) {
        $productId = $_POST['product_id'];
        
        // Suppression des images du produit
        $deleteImagesQuery = "DELETE FROM product_images WHERE product_id = ?";
        $stmtImages = mysqli_prepare($conn, $deleteImagesQuery);
        mysqli_stmt_bind_param($stmtImages, "s", $productId);
        mysqli_stmt_execute($stmtImages);
        
        // Suppression du produit
        $deleteProductQuery = "DELETE FROM products WHERE id = ?";
        $stmtProduct = mysqli_prepare($conn, $deleteProductQuery);
        mysqli_stmt_bind_param($stmtProduct, "s", $productId);
        $deleteResult = mysqli_stmt_execute($stmtProduct);
        
        if ($deleteResult) {
            $success = "Produit supprimé avec succès.";
            // Redirection pour éviter la soumission multiple du formulaire
            header("Location: products.php?page=$currentPage&success=deleted");
            exit;
        } else {
            $error = "Erreur lors de la suppression du produit: " . mysqli_error($conn);
        }
    }
    
    // Traitement des messages de succès via GET
    if (isset($_GET['success'])) {
        switch ($_GET['success']) {
            case 'deleted':
                $success = "Produit supprimé avec succès.";
                break;
            case 'updated':
                $success = "Produit mis à jour avec succès.";
                break;
            case 'added':
                $success = "Produit ajouté avec succès.";
                break;
        }
    }
    
    mysqli_close($conn);
}

// Inclusion de l'en-tête d'administration
require_once 'admin_header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-3">
            <!-- Menu latéral d'administration -->
            <div class="list-group">
                <a href="index.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-tachometer-alt me-2"></i>Tableau de bord
                </a>
                <a href="products.php" class="list-group-item list-group-item-action active">
                    <i class="fas fa-box me-2"></i>Gestion des produits
                </a>
                <a href="categories.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-tags me-2"></i>Gestion des catégories
                </a>
                <a href="users.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-users me-2"></i>Gestion des utilisateurs
                </a>
                <a href="register.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-user-plus me-2"></i>Ajouter un administrateur
                </a>
                <a href="orders.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-shopping-cart me-2"></i>Gestion des commandes
                </a>
                <a href="settings.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-cog me-2"></i>Paramètres
                </a>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-box me-2"></i>Gestion des produits</h4>
                    <a href="product_edit.php" class="btn btn-light btn-sm">
                        <i class="fas fa-plus me-1"></i>Ajouter un produit
                    </a>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <!-- Filtres et recherche -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <form action="" method="GET" class="d-flex">
                                <input type="text" name="search" class="form-control me-2" placeholder="Rechercher un produit..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                            </form>
                        </div>
                        <div class="col-md-6 text-end">
                            <form action="" method="GET" class="d-inline-flex">
                                <select name="category" class="form-select me-2">
                                    <option value="">Toutes les catégories</option>
                                    <!-- Les options des catégories seraient générées dynamiquement ici -->
                                </select>
                                <button type="submit" class="btn btn-secondary">
                                    <i class="fas fa-filter me-1"></i>Filtrer
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Tableau des produits -->
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th width="60">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="selectAll">
                                        </div>
                                    </th>
                                    <th width="80">Image</th>
                                    <th>Nom</th>
                                    <th>Catégorie</th>
                                    <th>Prix</th>
                                    <th>Stock</th>
                                    <th width="150">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($products)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">Aucun produit trouvé</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input item-checkbox" type="checkbox" value="<?php echo $product['id']; ?>">
                                            </div>
                                        </td>
                                        <td>
                                            <img src="<?php echo !empty($product['main_image']) ? $product['main_image'] : 'https://via.placeholder.com/50'; ?>" 
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                                 class="img-thumbnail" width="50">
                                        </td>
                                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                                        <td><?php echo htmlspecialchars($product['category_name'] ?? 'Non catégorisé'); ?></td>
                                        <td><?php echo number_format($product['price'], 2, ',', ' '); ?> €</td>
                                        <td>
                                            <?php if ($product['stock'] > 10): ?>
                                                <span class="badge bg-success"><?php echo $product['stock']; ?></span>
                                            <?php elseif ($product['stock'] > 0): ?>
                                                <span class="badge bg-warning"><?php echo $product['stock']; ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">0</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="product_edit.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="" method="POST" class="d-inline">
                                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                <button type="submit" name="delete_product" class="btn btn-sm btn-danger confirm-action" 
                                                        data-confirm-message="Êtes-vous sûr de vouloir supprimer ce produit ?" title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            <a href="../product.php?id=<?php echo $product['id']; ?>" target="_blank" class="btn btn-sm btn-info" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Actions en masse -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-danger bulk-action" disabled>
                                    <i class="fas fa-trash me-1"></i>Supprimer la sélection
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <!-- Pagination -->
                            <?php if ($totalPages > 1): ?>
                            <nav aria-label="Pagination des produits">
                                <ul class="pagination justify-content-end mb-0">
                                    <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $currentPage - 1; ?>" aria-label="Précédent">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                    
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $currentPage + 1; ?>" aria-label="Suivant">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'admin_footer.php'; ?>