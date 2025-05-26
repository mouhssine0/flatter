<?php
require_once 'includes/config.php';
require_once 'includes/api.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

// Récupération des catégories depuis l'API
$categories = getCategories();

// Log du résultat pour le debugging
if ($categories === null) {
    error_log('Categories retrieval failed');
} else {
    error_log('Retrieved ' . count($categories) . ' categories');
}
?>

<style>
    /* Styles for category cards and animations */
    .category-card {
        transition: all 0.3s ease;
        opacity: 0;
        transform: translateY(20px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        border-radius: 10px;
        overflow: hidden;
        border: none;
    }
    
    .category-card .card-body {
        padding: 1.5rem;
        background: linear-gradient(to bottom, #ffffff, #f8f9fa);
    }
    
    .category-card .card-title {
        color: #333;
        font-weight: 600;
        margin-bottom: 1rem;
        border-bottom: 2px solid #f8f9fa;
        padding-bottom: 0.5rem;
    }
    
    .pulse-animation {
        animation: pulse 1s;
    }
    
    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.7);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(0, 123, 255, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(0, 123, 255, 0);
        }
    }
    
    .page-title-animation {
        transition: all 0.5s ease;
    }
</style>

<main class="container mt-4">
    <h1 class="text-center mb-4 page-title-animation">Catégories de produits</h1>
    
    <?php if (!empty($categories)): ?>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php foreach ($categories as $category): ?>
                <div class="col">
                    <div class="card h-100 category-card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($category['categoryName'] ?? ''); ?></h5>
                            <?php if (isset($category['categoryId']) && !empty($category['categoryId'])): ?>
                                <p class="card-text">ID: <?php echo htmlspecialchars($category['categoryId']); ?></p>
                            <?php endif; ?>
                            <a href="products.php?category=<?php echo urlencode($category['categoryId'] ?? ''); ?>" class="btn btn-primary">
                                Voir les produits
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center">
            <?php if (CJ_API_KEY === 'VOTRE_CLE_API' || CJ_EMAIL === 'votre_email@exemple.com'): ?>
                <h4>Configuration requise</h4>
                <p>Les informations d'authentification de l'API CJdropshipping n'ont pas été configurées.</p>
                <p>Veuillez modifier le fichier <code>includes/config.php</code> et remplacer les valeurs par défaut par vos informations d'API.</p>
            <?php else: ?>
                <p>Aucune catégorie disponible pour le moment. Veuillez réessayer plus tard.</p>
                <p><small>Si le problème persiste, veuillez vérifier les logs pour plus d'informations.</small></p>
                <?php if (DEBUG_MODE): ?>
                    <p><small>Mode debug activé : consultez les logs PHP pour plus de détails.</small></p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</main>

<script src="js/categories.js"></script>
<?php require_once 'includes/footer.php'; ?>