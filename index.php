<?php
// Page d'accueil du site
// Intégration de l'API CJdropshipping

// Inclusion des fichiers nécessaires
require_once 'includes/config.php';
require_once 'includes/api.php';
require_once 'includes/functions.php';
require_once 'includes/db_sync.php';
require_once 'includes/auth_functions.php';


// Récupération des produits depuis la base de données
$result = getProductsFromDatabase();
$products = $result['products'];

// Récupération des catégories depuis la base de données
$categories = [];
$conn = connectDB();
if ($conn) {
    $categoryQuery = "SELECT id, name FROM categories ORDER BY name ASC LIMIT 8";
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

<!-- Hero Section avec Slider - Full Height avec Gradients et Animations -->
<style>
    /* Enhanced Hero Section Styles */
    .hero-section {
        margin-bottom: 0;
        position: relative;
        height: 100vh;
        overflow: hidden;
    }
    
    .hero-slide {
        height: 100vh;
        background-size: cover;
        background-position: center;
        position: relative;
        color: white;
        transition: all 0.8s ease-in-out;
    }
    
    /* Gradient Overlays */
    .gradient-overlay-1 {
        background: linear-gradient(135deg, rgba(76, 0, 255, 0.7) 0%, rgba(0, 159, 253, 0.6) 50%, rgba(0, 221, 255, 0.5) 100%);
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
    }
    
    .gradient-overlay-2 {
        background: linear-gradient(135deg, rgba(255, 0, 87, 0.7) 0%, rgba(230, 0, 161, 0.6) 50%, rgba(168, 0, 247, 0.5) 100%);
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
    }
    
    .gradient-overlay-3 {
        background: linear-gradient(135deg, rgba(0, 176, 155, 0.7) 0%, rgba(0, 196, 196, 0.6) 50%, rgba(0, 255, 176, 0.5) 100%);
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
    }
    
    /* Hero Content */
    .hero-content {
    position: relative;
    z-index: 2;
    text-align: center;
    max-width: 800px;
    margin: 0 auto;
    opacity: 1; /* Changé de 0 à 1 */
    transform: translateY(0); /* Supprimé le décalage initial */
    animation: none; /* Supprimé l'animation qui cause le problème */
}
    
    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Hero Badge */
    .hero-badge {
        display: inline-block;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        padding: 8px 16px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.9rem;
        margin-bottom: 1.5rem;
        border: 1px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    
    /* Gradient Text */
    .gradient-text-1 {
        background: linear-gradient(to right, #fff 0%, #a0e9ff 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        text-fill-color: transparent;
        font-size: 3.5rem;
        font-weight: 800;
        margin-bottom: 1.5rem;
        text-shadow: none;
    }
    
    .gradient-text-2 {
        background: linear-gradient(to right, #fff 0%, #ffb8d9 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        text-fill-color: transparent;
        font-size: 3.5rem;
        font-weight: 800;
        margin-bottom: 1.5rem;
        text-shadow: none;
    }
    
    .gradient-text-3 {
        background: linear-gradient(to right, #fff 0%, #b8ffe8 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        text-fill-color: transparent;
        font-size: 3.5rem;
        font-weight: 800;
        margin-bottom: 1.5rem;
        text-shadow: none;
    }
    
    /* Hero Subtitle */
    .hero-subtitle {
        font-size: 1.4rem;
        margin-bottom: 2.5rem;
        color: rgba(255, 255, 255, 0.9);
        font-weight: 300;
        max-width: 80%;
        margin-left: auto;
        margin-right: auto;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
    }
    
    /* Hero Buttons */
    .hero-buttons {
        display: flex;
        justify-content: center;
        gap: 20px;
    }
    
    .btn-hero {
        padding: 12px 30px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 1.1rem;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        z-index: 1;
        border: none;
        color: white;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
    }
    
    .btn-gradient-1 {
        background: linear-gradient(45deg, #4c00ff, #00d0ff);
    }
    
    .btn-gradient-2 {
        background: linear-gradient(45deg, #ff0057, #a800f7);
    }
    
    .btn-gradient-3 {
        background: linear-gradient(45deg, #00b09b, #00ffb0);
    }
    
    .btn-hero:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
    }
    
    .btn-hero::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.2);
        z-index: -1;
        transform: scaleX(0);
        transform-origin: right;
        transition: transform 0.5s ease;
    }
    
    .btn-hero:hover::after {
        transform: scaleX(1);
        transform-origin: left;
    }
    
    .btn-hero-outline {
        padding: 12px 30px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 1.1rem;
        letter-spacing: 0.5px;
        background: transparent;
        color: white;
        border: 2px solid rgba(255, 255, 255, 0.6);
        transition: all 0.3s ease;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
    
    .btn-hero-outline:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.9);
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        color: white;
    }
    
    .btn-hero-outline i {
        transition: transform 0.3s ease;
        margin-left: 5px;
    }
    
    .btn-hero-outline:hover i {
        transform: translateX(5px);
    }
    
    /* Scroll Indicator */
    .scroll-indicator {
        position: absolute;
        bottom: 30px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 10;
        text-align: center;
        color: white;
        cursor: pointer;
        animation: fadeInUp 1s ease-out forwards 1.5s, bounce 2s infinite 2.5s;
        opacity: 0;
    }
    
    .scroll-indicator span {
        display: block;
        font-size: 0.9rem;
        margin-bottom: 10px;
        font-weight: 500;
        letter-spacing: 1px;
        text-transform: uppercase;
    }
    
    .scroll-indicator i {
        font-size: 1.5rem;
    }
    
    @keyframes bounce {
        0%, 20%, 50%, 80%, 100% { transform: translateY(0) translateX(-50%); }
        40% { transform: translateY(-20px) translateX(-50%); }
        60% { transform: translateY(-10px) translateX(-50%); }
    }
    
    /* Carousel Controls */
    .carousel-control-prev, .carousel-control-next {
        width: 5%;
        opacity: 0.7;
        transition: opacity 0.3s ease;
    }
    
    .carousel-control-prev:hover, .carousel-control-next:hover {
        opacity: 1;
    }
    
    .carousel-indicators button {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin: 0 8px;
        background-color: rgba(255, 255, 255, 0.5);
        border: 2px solid rgba(255, 255, 255, 0.7);
        transition: all 0.3s ease;
    }
    
    .carousel-indicators button.active {
        background-color: white;
        transform: scale(1.2);
    }
</style>

<section class="hero-section">
    <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80');">
                    <div class="gradient-overlay-1"></div>
                    <div class="container h-100 d-flex align-items-center justify-content-center">
                        <div class="hero-content">
                            <div class="hero-badge">Premium</div>
                            <h1 class="hero-title gradient-text-1">Découvrez notre sélection premium</h1>
                            <p class="hero-subtitle">Des produits de qualité sélectionnés pour vous</p>
                            <div class="hero-buttons">
                                <a href="#featured-products" class="btn btn-hero btn-gradient-1">Voir les produits</a>
                                <a href="#categories" class="btn btn-hero-outline">Explorer <i class="bi bi-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="carousel-item">
                <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1607083206968-13611e3d76db?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80');">
                    <div class="gradient-overlay-2"></div>
                    <div class="container h-100 d-flex align-items-center justify-content-center">
                        <div class="hero-content">
                            <div class="hero-badge">International</div>
                            <h1 class="hero-title gradient-text-2">Livraison mondiale rapide</h1>
                            <p class="hero-subtitle">Expédition directe depuis nos entrepôts internationaux</p>
                            <div class="hero-buttons">
                                <a href="#categories" class="btn btn-hero btn-gradient-2">Explorer les catégories</a>
                                <a href="#" class="btn btn-hero-outline">En savoir plus <i class="bi bi-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="carousel-item">
                <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1607082349566-187342175e2f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80');">
                    <div class="gradient-overlay-3"></div>
                    <div class="container h-100 d-flex align-items-center justify-content-center">
                        <div class="hero-content">
                            <div class="hero-badge">Nouveautés</div>
                            <h1 class="hero-title gradient-text-3">Nouveautés chaque semaine</h1>
                            <p class="hero-subtitle">Restez à la pointe des tendances avec nos nouveaux produits</p>
                            <div class="hero-buttons">
                                <a href="nouveautes.php" class="btn btn-hero btn-gradient-3">Voir les nouveautés</a>
                                <a href="#" class="btn btn-hero-outline">S'abonner <i class="bi bi-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Précédent</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Suivant</span>
        </button>
        
        <!-- Animated scroll indicator -->
        <div class="scroll-indicator" onclick="document.getElementById('categories').scrollIntoView({behavior: 'smooth'})">
            <span>Découvrir</span>
            <i class="bi bi-chevron-down"></i>
        </div>
                <div class="wheel"></div>
            </div>
            <div>
                <span class="scroll-text">Scroll</span>
            </div>
        </div>
    </div>
</section>

<!-- Hero Section Styles -->
<style>
    /* Hero Section Styles */
    .hero-section {
        position: relative;
        height: 100vh;
        margin-bottom: 0;
        overflow: hidden;
    }
    
    .hero-slide {
        height: 100vh;
        background-size: cover;
        background-position: center;
        position: relative;
        color: white;
    }
    
    .hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
    }
    
    .gradient-overlay-1 {
        background: linear-gradient(135deg, rgba(76, 29, 149, 0.85) 0%, rgba(124, 58, 237, 0.75) 50%, rgba(139, 92, 246, 0.65) 100%);
    }
    
    .gradient-overlay-2 {
        background: linear-gradient(135deg, rgba(6, 78, 59, 0.85) 0%, rgba(5, 150, 105, 0.75) 50%, rgba(16, 185, 129, 0.65) 100%);
    }
    
    .gradient-overlay-3 {
        background: linear-gradient(135deg, rgba(30, 58, 138, 0.85) 0%, rgba(37, 99, 235, 0.75) 50%, rgba(59, 130, 246, 0.65) 100%);
    }
    
    .hero-content {
        position: relative;
        z-index: 2;
        max-width: 800px;
        animation: fadeInUp 1s ease-out;
    }
    
    .hero-badge {
        display: inline-block;
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        padding: 8px 16px;
        border-radius: 50px;
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
        letter-spacing: 1px;
        text-transform: uppercase;
        animation: fadeInDown 0.8s ease-out 0.2s both;
    }
    
    .hero-title {
        font-size: 4rem;
        font-weight: 800;
        line-height: 1.2;
        margin-bottom: 1.5rem;
        letter-spacing: -0.5px;
        animation: fadeInUp 0.8s ease-out 0.4s both;
    }
    
    .gradient-text-1 {
        background: linear-gradient(to right, #fff 0%, #d8b4fe 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        text-fill-color: transparent;
    }
    
    .gradient-text-2 {
        background: linear-gradient(to right, #fff 0%, #6ee7b7 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        text-fill-color: transparent;
    }
    
    .gradient-text-3 {
        background: linear-gradient(to right, #fff 0%, #93c5fd 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        text-fill-color: transparent;
    }
    
    .hero-subtitle {
        font-size: 1.5rem;
        margin-bottom: 2.5rem;
        opacity: 0.9;
        max-width: 80%;
        animation: fadeInUp 0.8s ease-out 0.6s both;
    }
    
    .hero-buttons {
        display: flex;
        gap: 1rem;
        animation: fadeInUp 0.8s ease-out 0.8s both;
    }
    
    .btn-hero {
        padding: 1rem 2rem;
        font-size: 1.1rem;
        font-weight: 600;
        border-radius: 50px;
        border: none;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }
    
    .btn-hero:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
    }
    
    .btn-gradient-1 {
        background: linear-gradient(to right, #8b5cf6, #d8b4fe);
        color: white;
    }
    
    .btn-gradient-2 {
        background: linear-gradient(to right, #10b981, #6ee7b7);
        color: white;
    }
    
    .btn-gradient-3 {
        background: linear-gradient(to right, #3b82f6, #93c5fd);
        color: white;
    }
    
    .btn-hero-outline {
        padding: 1rem 2rem;
        font-size: 1.1rem;
        font-weight: 600;
        border-radius: 50px;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }
    
    .btn-hero-outline:hover {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.3);
        transform: translateY(-5px);
    }
    
    .btn-hero-outline i {
        margin-left: 0.5rem;
        transition: transform 0.3s ease;
    }
    
    .btn-hero-outline:hover i {
        transform: translateX(5px);
    }
    
    /* Carousel Fade Effect */
    .carousel-fade .carousel-item {
        opacity: 0;
        transition: opacity 0.9s ease-in-out;
    }
    
    .carousel-fade .carousel-item.active {
        opacity: 1;
    }
    
    /* Scroll Indicator */
    .scroll-indicator {
        position: absolute;
        bottom: 30px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 10;
        text-align: center;
        animation: fadeInUp 1s ease-out 1.5s both;
    }
    
    .mouse {
        width: 30px;
        height: 50px;
        border: 2px solid rgba(255, 255, 255, 0.8);
        border-radius: 20px;
        margin: 0 auto 10px;
        position: relative;
    }
    
    .wheel {
        width: 4px;
        height: 10px;
        background: rgba(255, 255, 255, 0.8);
        position: absolute;
        top: 10px;
        left: 50%;
        transform: translateX(-50%);
        border-radius: 2px;
        animation: scrollWheel 2s infinite;
    }
    
    .scroll-text {
        color: rgba(255, 255, 255, 0.8);
        font-size: 0.8rem;
        letter-spacing: 1px;
    }
    
    /* Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes scrollWheel {
        0% {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
        100% {
            opacity: 0;
            transform: translateX(-50%) translateY(20px);
        }
    }
    
    /* Responsive adjustments */
    @media (max-width: 992px) {
        .hero-title {
            font-size: 3rem;
        }
        
        .hero-subtitle {
            font-size: 1.2rem;
        }
    }
    
    @media (max-width: 768px) {
        .hero-title {
            font-size: 2.5rem;
        }
        
        .hero-subtitle {
            font-size: 1.1rem;
            max-width: 100%;
        }
        
        .hero-buttons {
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .btn-hero, .btn-hero-outline {
            width: 100%;
            padding: 0.75rem 1.5rem;
        }
    }
</style>

<!-- Section des catégories animées -->
<section id="categories" class="categories-section py-5">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="display-5 fw-bold">Explorez nos catégories</h2>
            <p class="text-muted">Trouvez les meilleurs produits par catégorie</p>
        </div>
        
        <div class="row g-4 justify-content-center category-container">
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $index => $category): ?>
                    <div class="col-6 col-md-3">
                        <div class="category-card" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                            <div class="category-icon">
                                <i class="fas <?php echo getCategoryIcon($category['name']); ?>"></i>
                            </div>
                            <h3 class="category-title"><?php echo htmlspecialchars($category['name']); ?></h3>
                            <a href="categories.php?id=<?php echo $category['id']; ?>" class="stretched-link"></a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p>Aucune catégorie disponible pour le moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Section des produits en vedette -->
<section id="featured-products" class="featured-products py-5 bg-light">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="display-5 fw-bold">Nos produits en vedette</h2>
            <p class="text-muted">Découvrez notre sélection de produits populaires</p>
        </div>
        
        <div class="row g-4" id="products-container">
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
                                    <p class="card-text price-tag"><?php echo number_format($product['price'], 2); ?> €</p>
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
                                    <a class="page-link" href="#" data-page="<?php echo $result['currentPage'] - 1; ?>">
                                        <i class="fas fa-chevron-left"></i> Précédent
                                    </a>
                                </li>
                                <?php for ($i = 1; $i <= $result['totalPages']; $i++): ?>
                                    <li class="page-item <?php echo $i === $result['currentPage'] ? 'active' : ''; ?>">
                                        <a class="page-link" href="#" data-page="<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $result['currentPage'] >= $result['totalPages'] ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="#" data-page="<?php echo $result['currentPage'] + 1; ?>">
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
    </div>
</section>

<!-- Section des statistiques -->
<section class="stats-section py-5 bg-dark text-white">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3" data-aos="fade-up" data-aos-delay="0">
                <div class="stat-item">
                    <div class="stat-number"><span class="counter">10000</span>+</div>
                    <div class="stat-label">Produits</div>
                </div>
            </div>
            <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
                <div class="stat-item">
                    <div class="stat-number"><span class="counter">200</span>+</div>
                    <div class="stat-label">Pays livrés</div>
                </div>
            </div>
            <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
                <div class="stat-item">
                    <div class="stat-number"><span class="counter">50000</span>+</div>
                    <div class="stat-label">Clients satisfaits</div>
                </div>
            </div>
            <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
                <div class="stat-item">
                    <div class="stat-number"><span class="counter">24</span>/7</div>
                    <div class="stat-label">Support client</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section des avantages -->
<section class="benefits-section py-5">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="display-5 fw-bold">Pourquoi nous choisir</h2>
            <p class="text-muted">Découvrez les avantages qui font notre différence</p>
        </div>
        <div class="row g-4 text-center">
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="0">
                <div class="benefit-card shadow-sm p-4 rounded h-100">
                    <div class="benefit-icon mb-3">
                        <i class="fas fa-shipping-fast fa-3x text-primary"></i>
                    </div>
                    <h3 class="h4 mb-3">Livraison mondiale</h3>
                    <p class="text-muted mb-0">Expédition directe depuis nos entrepôts vers plus de 200 pays avec suivi en temps réel de vos commandes.</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="benefit-card shadow-sm p-4 rounded h-100">
                    <div class="benefit-icon mb-3">
                        <i class="fas fa-shield-alt fa-3x text-primary"></i>
                    </div>
                    <h3 class="h4 mb-3">Paiement sécurisé</h3>
                    <p class="text-muted mb-0">Transactions sécurisées et multiples options de paiement pour une expérience d'achat en toute confiance.</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="benefit-card shadow-sm p-4 rounded h-100">
                    <div class="benefit-icon mb-3">
                        <i class="fas fa-headset fa-3x text-primary"></i>
                    </div>
                    <h3 class="h4 mb-3">Support 24/7</h3>
                    <p class="text-muted mb-0">Notre équipe d'experts est disponible pour vous aider à tout moment, par chat, email ou téléphone.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section newsletter -->
<section class="newsletter-section py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <h2 class="mb-3 fw-bold">Restez informé des nouveautés</h2>
                <p class="mb-lg-0 lead">Inscrivez-vous à notre newsletter pour recevoir nos offres exclusives, les dernières nouveautés et des promotions spéciales réservées à nos abonnés.</p>
                <div class="mt-3 d-none d-lg-block">
                    <div class="d-flex">
                        <div class="me-3">
                            <i class="fas fa-check-circle text-light"></i> Offres exclusives
                        </div>
                        <div class="me-3">
                            <i class="fas fa-check-circle text-light"></i> Nouveautés en avant-première
                        </div>
                        <div>
                            <i class="fas fa-check-circle text-light"></i> Conseils d'experts
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="card shadow border-0">
                    <div class="card-body p-4">
                        <h3 class="text-primary mb-3">Rejoignez notre communauté</h3>
                        <form class="newsletter-form" id="newsletter-form">
                            <div class="mb-3">
                                <input type="text" class="form-control" placeholder="Votre nom" required>
                            </div>
                            <div class="mb-3">
                                <input type="email" class="form-control" placeholder="Votre adresse email" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">S'inscrire à la newsletter</button>
                        </form>
                        <p class="text-muted small mt-2 mb-0">En vous inscrivant, vous acceptez de recevoir nos communications marketing et vous confirmez avoir lu notre politique de confidentialité.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Ajout des scripts JS supplémentaires -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/countup.js@2.0.7/dist/countUp.min.js"></script>
<script>
    // Initialisation de la bibliothèque AOS pour les animations au scroll
    document.addEventListener('DOMContentLoaded', function() {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
        
        // Animation des compteurs dans la section statistiques
        const counterElements = document.querySelectorAll('.counter');
        const options = {
            duration: 2.5,
            useEasing: true,
            useGrouping: true,
            separator: ' '
        };
        
        // Fonction pour animer les compteurs lorsqu'ils sont visibles
        function animateCounters() {
            counterElements.forEach(counter => {
                const target = parseInt(counter.textContent, 10);
                const countUp = new CountUp(counter, target, options);
                if (!countUp.error) {
                    countUp.start();
                } else {
                    console.error(countUp.error);
                }
            });
        }
        
        // Observer pour détecter quand la section de statistiques est visible
        const statsSection = document.querySelector('.stats-section');
        if (statsSection) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        animateCounters();
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 });
            
            observer.observe(statsSection);
        }
        
        // Amélioration du formulaire newsletter avec feedback visuel
        const newsletterForm = document.getElementById('newsletter-form');
        if (newsletterForm) {
            newsletterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const emailInput = this.querySelector('input[type="email"]');
                const nameInput = this.querySelector('input[type="text"]');
                const submitBtn = this.querySelector('button[type="submit"]');
                
                if (emailInput.value.trim() && nameInput.value.trim()) {
                    // Désactiver le bouton et montrer le chargement
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Inscription en cours...';
                    
                    // Simuler un délai d'envoi (à remplacer par un vrai envoi AJAX)
                    setTimeout(() => {
                        // Créer un élément de succès
                        const successDiv = document.createElement('div');
                        successDiv.className = 'alert alert-success mt-3';
                        successDiv.innerHTML = '<i class="fas fa-check-circle me-2"></i> Merci de vous être inscrit à notre newsletter!';
                        
                        // Remplacer le formulaire par le message de succès
                        newsletterForm.parentNode.replaceChild(successDiv, newsletterForm);
                    }, 1500);
                }
            });
        }
    });
    
    // Fonction pour obtenir une icône en fonction du nom de la catégorie
    function getCategoryIcon(categoryName) {
        const iconMap = {
            'Électronique': 'fa-laptop',
            'Vêtements': 'fa-tshirt',
            'Maison': 'fa-home',
            'Jardin': 'fa-leaf',
            'Sports': 'fa-running',
            'Beauté': 'fa-spa',
            'Jouets': 'fa-gamepad',
            'Automobile': 'fa-car'
        };
        
        // Recherche insensible à la casse
        for (const [key, value] of Object.entries(iconMap)) {
            if (categoryName.toLowerCase().includes(key.toLowerCase())) {
                return value;
            }
        }
        
        // Icône par défaut
        return 'fa-tag';
    }
</script>

<?php 
// Ajout de la fonction getCategoryIcon pour PHP
function getCategoryIcon($categoryName) {
    $iconMap = [
        'Électronique' => 'fa-laptop',
        'Vêtements' => 'fa-tshirt',
        'Maison' => 'fa-home',
        'Jardin' => 'fa-leaf',
        'Sports' => 'fa-running',
        'Beauté' => 'fa-spa',
        'Jouets' => 'fa-gamepad',
        'Automobile' => 'fa-car'
    ];
    
    // Recherche insensible à la casse
    foreach ($iconMap as $key => $value) {
        if (stripos($categoryName, $key) !== false) {
            return $value;
        }
    }
    
    // Icône par défaut
    return 'fa-tag';
}
?>
<!-- Hero Animations Script -->
<script src="js/hero-animations.js"></script>

<?php require_once 'includes/footer.php'; 
?>