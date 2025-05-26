<?php
// Page de profil utilisateur
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth_functions.php';
// Inclusion de l'en-tête
require_once 'includes/header.php';


// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

// Récupérer les informations de l'utilisateur
$user_id = $_SESSION['user_id'];
$user = getUserById($user_id);

$success = '';
$error = '';

// Traitement du formulaire de mise à jour du profil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $postal_code = trim($_POST['postal_code'] ?? '');
    $country = trim($_POST['country'] ?? '');
    
    if (empty($firstname) || empty($lastname) || empty($email)) {
        $error = "Les champs prénom, nom et email sont obligatoires.";
    } else {
        $conn = connectDB();
        
        // Vérifier si l'email existe déjà pour un autre utilisateur
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check_stmt->bind_param("si", $email, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = "Cette adresse email est déjà utilisée par un autre utilisateur.";
        } else {
            // Mettre à jour les informations de l'utilisateur
            $update_stmt = $conn->prepare("UPDATE users SET firstname = ?, lastname = ?, email = ?, phone = ?, address = ?, city = ?, postal_code = ?, country = ? WHERE id = ?");
            $update_stmt->bind_param("ssssssssi", $firstname, $lastname, $email, $phone, $address, $city, $postal_code, $country, $user_id);
            
            if ($update_stmt->execute()) {
                $success = "Votre profil a été mis à jour avec succès.";
                
                // Mettre à jour les informations de session
                $_SESSION['user_firstname'] = $firstname;
                $_SESSION['user_lastname'] = $lastname;
                $_SESSION['user_email'] = $email;
                
                // Récupérer les informations mises à jour
                $user = getUserById($user_id);
            } else {
                $error = "Une erreur est survenue lors de la mise à jour de votre profil.";
            }
            
            $update_stmt->close();
        }
        
        $check_stmt->close();
        $conn->close();
    }
}


?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user-circle me-2"></i>Mon Profil</h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <img src="https://via.placeholder.com/150" class="rounded-circle img-thumbnail" alt="Photo de profil">
                    </div>
                    <h4><?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?></h4>
                    <p class="text-muted">
                        <i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($user['email']); ?>
                    </p>
                    <p class="text-muted">
                        <i class="fas fa-user-tag me-2"></i>Rôle: <?php echo ucfirst(htmlspecialchars($user['role'])); ?>
                    </p>
                    <p class="text-muted">
                        <i class="fas fa-calendar-alt me-2"></i>Membre depuis: <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                    </p>
                    <div class="d-grid gap-2">
                        <a href="settings.php" class="btn btn-outline-primary">
                            <i class="fas fa-cog me-2"></i>Paramètres du compte
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Modifier mes informations</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="firstname" class="form-label">Prénom</label>
                                <input type="text" class="form-control" id="firstname" name="firstname" value="<?php echo htmlspecialchars($user['firstname'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="lastname" class="form-label">Nom</label>
                                <input type="text" class="form-control" id="lastname" name="lastname" value="<?php echo htmlspecialchars($user['lastname'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Adresse</label>
                            <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>">
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="city" class="form-label">Ville</label>
                                <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="postal_code" class="form-label">Code postal</label>
                                <input type="text" class="form-control" id="postal_code" name="postal_code" value="<?php echo htmlspecialchars($user['postal_code'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="country" class="form-label">Pays</label>
                            <select class="form-select" id="country" name="country">
                                <option value="">Sélectionnez un pays</option>
                                <option value="France" <?php echo (isset($user['country']) && $user['country'] === 'France') ? 'selected' : ''; ?>>France</option>
                                <option value="Belgique" <?php echo (isset($user['country']) && $user['country'] === 'Belgique') ? 'selected' : ''; ?>>Belgique</option>
                                <option value="Suisse" <?php echo (isset($user['country']) && $user['country'] === 'Suisse') ? 'selected' : ''; ?>>Suisse</option>
                                <option value="Canada" <?php echo (isset($user['country']) && $user['country'] === 'Canada') ? 'selected' : ''; ?>>Canada</option>
                                <option value="Luxembourg" <?php echo (isset($user['country']) && $user['country'] === 'Luxembourg') ? 'selected' : ''; ?>>Luxembourg</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Enregistrer les modifications
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>