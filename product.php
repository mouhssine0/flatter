<?php
// Page de détail d'un produit
// Intégration de l'API CJdropshipping

// Inclusion des fichiers nécessaires
require_once 'includes/config.php';
require_once 'includes/api.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

// Récupération de l'ID du produit depuis l'URL
$productId = isset($_GET['id']) ? $_GET['id'] : null;

// Si aucun ID n'est fourni, rediriger vers la page d'accueil
if (!$productId) {
    header('Location: index.php');
    exit;
}

// Récupération des détails du produit
$product = getProductById($productId);

// Si le produit n'existe pas, afficher un message d'erreur
if (!$product) {
    $error = "Le produit demandé n'existe pas ou n'est plus disponible.";
}
?>

<main class="container mt-4">
    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <?php if (CJ_API_KEY === 'VOTRE_CLE_API' || CJ_EMAIL === 'votre_email@exemple.com'): ?>
                <h4>Configuration requise</h4>
                <p>Les informations d'authentification de l'API CJdropshipping n'ont pas été configurées.</p>
                <p>Veuillez modifier le fichier <code>includes/config.php</code> et remplacer les valeurs par défaut par vos informations d'API.</p>
            <?php else: ?>
                <?php echo htmlspecialchars($error); ?>
            <?php endif; ?>
            <p><a href="index.php" class="btn btn-primary mt-2">Retour à l'accueil</a></p>
        </div>
    <?php else: ?>
        <div class="row">
            <!-- Galerie d'images -->
            <div class="col-md-6">
                <div id="productCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <?php foreach ($product['images'] as $index => $image): ?>
                            <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                <img src="<?php echo htmlspecialchars($image); ?>" class="d-block w-100 product-detail-img" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($product['images']) > 1): ?>
                        <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Précédent</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Suivant</span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Informations du produit -->
            <div class="col-md-6">
                <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                <p class="fs-4 fw-bold text-primary"><?php echo htmlspecialchars($product['price']); ?> €</p>
                
                <div class="mb-4">
                    <h5>Description</h5>
                    <div class="product-description">
                        <?php echo $product['description']; ?>
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <button class="btn btn-primary btn-lg">Ajouter au panier</button>
                    <button class="btn btn-outline-secondary">Ajouter aux favoris</button>
                </div>
            </div>
        </div>
        
        <!-- Produits similaires -->
        <div class="mt-5 similar-products-section">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3 class="mb-1">Produits similaires</h3>
                    <p class="text-muted">Des articles qui pourraient vous intéresser basés sur votre sélection</p>
                </div>
            </div>
            <div class="row" id="similar-products">
                <!-- Les produits similaires seront chargés via JavaScript -->
                <p>Chargement des produits similaires...</p>
            </div>
        </div>
    <?php endif; ?>
</main>

<style>
    /* Styles pour la section "Produits similaires" vide */
    .empty-similar-products {
        background: linear-gradient(to right, #f8f9fa, #ffffff);
        border: 1px solid #e9ecef;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }
    
    .empty-similar-products:hover {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        transform: translateY(-5px);
    }
    
    .empty-state-icon {
        background-color: #f1f3f5;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        transition: all 0.3s ease;
    }
    
    .empty-similar-products:hover .empty-state-icon {
        background-color: #e9ecef;
        transform: scale(1.05);
    }
    
    .fade-in {
        animation: fadeIn 0.8s ease-in-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<script src="js/product-detail.js"></script>

<?php require_once 'includes/footer.php'; ?>