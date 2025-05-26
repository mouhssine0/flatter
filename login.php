<?php
// Page de connexion
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth_functions.php';

// Démarrer la session
require_once 'includes/header.php';

// Rediriger si déjà connecté
if (isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$error = '';
$email = '';

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        $user = loginUser($email, $password);
        
        if ($user) {
            // Démarrer la session et stocker les informations utilisateur
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_firstname'] = $user['firstname'];
            $_SESSION['user_lastname'] = $user['lastname'];
            $_SESSION['user_role'] = $user['role'];
            
            // Rediriger selon le rôle
            if ($user['role'] === 'admin') {
                header("Location: admin/index.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $error = "Email ou mot de passe incorrect.";
        }
    }
}

// Inclusion de l'en-tête

?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><i class="fas fa-sign-in-alt me-2"></i>Connexion</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Se connecter</button>
                        </div>
                    </form>
                    
                    <div class="mt-3 text-center">
                        <p>Vous n'avez pas de compte ? <a href="register.php">Inscrivez-vous</a></p>
                        <p class="mt-2 mb-0"><a href="admin/register.php">Inscription administrateur</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>