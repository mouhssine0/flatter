<?php
// Page d'administration
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

// Inclusion de l'en-tête d'administration (à créer)
require_once 'admin_header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-3">
            <!-- Menu latéral d'administration -->
            <div class="list-group">
                <a href="index.php" class="list-group-item list-group-item-action active">
                    <i class="fas fa-tachometer-alt me-2"></i>Tableau de bord
                </a>
                <a href="products.php" class="list-group-item list-group-item-action">
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
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-tachometer-alt me-2"></i>Tableau de bord</h4>
                </div>
                <div class="card-body">
                    <h5>Bienvenue, <?php echo htmlspecialchars($_SESSION['user_firstname'] . ' ' . $_SESSION['user_lastname']); ?> !</h5>
                    <p>Vous êtes connecté en tant qu'administrateur.</p>
                    
                    <div class="row mt-4">
                        <div class="col-md-4 mb-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-box me-2"></i>Produits</h5>
                                    <p class="card-text display-6">0</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-users me-2"></i>Utilisateurs</h5>
                                    <p class="card-text display-6">0</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-shopping-cart me-2"></i>Commandes</h5>
                                    <p class="card-text display-6">0</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'admin_footer.php'; ?>