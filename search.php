<?php
// Page de recherche de produits
// Intégration de l'API CJdropshipping

// Inclusion des fichiers nécessaires
require_once 'includes/config.php';
require_once 'includes/api.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

// Récupération du terme de recherche
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

// Si aucun terme n'est fourni, rediriger vers la page d'accueil
if (empty($query)) {
    header('Location: index.php');
    exit;
}

// Récupération de la page courante
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Recherche des produits
$products = searchProducts($query, $page);
?>

<main class="container mt-4">
    <h1 class="mb-4">Résultats de recherche pour "<?php echo htmlspecialchars($query); ?>"</h1>
    
    <div class="row" id="products-container">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $product): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="<?php echo htmlspecialchars($product['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?></p>
                            <p class="card-text font-weight-bold"><?php echo htmlspecialchars($product['price']); ?> €</p>
                            <a href="product.php?id=<?php echo htmlspecialchars($product['id']); ?>" class="btn btn-primary">Voir détails</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center">
                <?php if (CJ_API_KEY === 'VOTRE_CLE_API' || CJ_EMAIL === 'votre_email@exemple.com'): ?>
                    <div class="alert alert-warning">
                        <h4>Configuration requise</h4>
                        <p>Les informations d'authentification de l'API CJdropshipping n'ont pas été configurées.</p>
                        <p>Veuillez modifier le fichier <code>includes/config.php</code> et remplacer les valeurs par défaut par vos informations d'API.</p>
                    </div>
                <?php else: ?>
                    <p>Aucun produit ne correspond à votre recherche.</p>
                <?php endif; ?>
                <a href="index.php" class="btn btn-primary mt-2">Retour à l'accueil</a>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Pagination -->
    <?php if (!empty($products)): ?>
        <nav aria-label="Pagination des résultats">
            <ul class="pagination justify-content-center mt-4">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="search.php?q=<?php echo urlencode($query); ?>&page=<?php echo $page - 1; ?>">Précédent</a>
                    </li>
                <?php endif; ?>
                
                <li class="page-item active">
                    <span class="page-link"><?php echo $page; ?></span>
                </li>
                
                <li class="page-item">
                    <a class="page-link" href="search.php?q=<?php echo urlencode($query); ?>&page=<?php echo $page + 1; ?>">Suivant</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</main>

<script src="js/products.js"></script>

<?php require_once 'includes/footer.php'; ?>