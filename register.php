<?php
// Page d'inscription
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
        $success = registerUser($email, $password, $firstname, $lastname);
        
        if ($success) {
            $success = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
            $email = $firstname = $lastname = ''; // Réinitialiser les champs
        } else {
            $error = "Cette adresse email est déjà utilisée.";
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
                    <h3 class="mb-0"><i class="fas fa-user-plus me-2"></i>Inscription</h3>
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
                            <button type="submit" class="btn btn-primary">S'inscrire</button>
                        </div>
                    </form>
                    
                    <div class="mt-3 text-center">
                        <p>Vous avez déjà un compte ? <a href="login.php">Connectez-vous</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>