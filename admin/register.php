<?php
// Page d'inscription administrateur
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth_functions.php';

// Démarrer la session
session_start();

// Vérifier si l'utilisateur est connecté et est un administrateur
// Pour la page d'inscription admin, nous autorisons uniquement les administrateurs existants à créer d'autres administrateurs
if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../login.php");
    exit;
}

$error = '';
$success = '';
$email = '';
$firstname = '';
$lastname = '';

// Traitement du formulaire d'inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    
    if (empty($email) || empty($password) || empty($confirmPassword) || empty($firstname) || empty($lastname)) {
        $error = "Veuillez remplir tous les champs.";
    } elseif ($password !== $confirmPassword) {
        $error = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($password) < 8) {
        $error = "Le mot de passe doit contenir au moins 8 caractères.";
    } else {
        // Créer un nouvel administrateur
        $success = registerUser($email, $password, $firstname, $lastname, 'admin');
        
        if ($success) {
            $success = "Administrateur créé avec succès !";
            $email = $firstname = $lastname = ''; // Réinitialiser les champs
        } else {
            $error = "Cette adresse email est déjà utilisée.";
        }
    }
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
                <a href="products.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-box me-2"></i>Gestion des produits
                </a>
                <a href="categories.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-tags me-2"></i>Gestion des catégories
                </a>
                <a href="users.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-users me-2"></i>Gestion des utilisateurs
                </a>
                <a href="register.php" class="list-group-item list-group-item-action active">
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
                    <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i>Ajouter un administrateur</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="firstname" class="form-label">Prénom</label>
                                <input type="text" class="form-control" id="firstname" name="firstname" value="<?php echo htmlspecialchars($firstname); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="lastname" class="form-label">Nom</label>
                                <input type="text" class="form-control" id="lastname" name="lastname" value="<?php echo htmlspecialchars($lastname); ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="form-text">Le mot de passe doit contenir au moins 8 caractères.</div>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Créer l'administrateur</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'admin_footer.php'; ?>