<?php
// Page de paramètres utilisateur
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth_functions.php';

// Démarrer la session
session_start();

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

// Traitement du formulaire de mise à jour des paramètres
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mise à jour du mot de passe
    if (isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = "Veuillez remplir tous les champs.";
        } elseif ($new_password !== $confirm_password) {
            $error = "Les nouveaux mots de passe ne correspondent pas.";
        } elseif (strlen($new_password) < 8) {
            $error = "Le nouveau mot de passe doit contenir au moins 8 caractères.";
        } else {
            // Vérifier le mot de passe actuel
            $conn = connectDB();
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $hashed_password = $row['password'];
                
                if (password_verify($current_password, $hashed_password)) {
                    // Mettre à jour le mot de passe
                    $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $update_stmt->bind_param("si", $new_hashed_password, $user_id);
                    
                    if ($update_stmt->execute()) {
                        $success = "Votre mot de passe a été mis à jour avec succès.";
                    } else {
                        $error = "Une erreur est survenue lors de la mise à jour du mot de passe.";
                    }
                    
                    $update_stmt->close();
                } else {
                    $error = "Le mot de passe actuel est incorrect.";
                }
            } else {
                $error = "Utilisateur non trouvé.";
            }
            
            $stmt->close();
            $conn->close();
        }
    }
    
    // Mise à jour des préférences de notification
    if (isset($_POST['update_preferences'])) {
        $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
        
        $conn = connectDB();
        $stmt = $conn->prepare("UPDATE users SET email_notifications = ? WHERE id = ?");
        $stmt->bind_param("ii", $email_notifications, $user_id);
        
        if ($stmt->execute()) {
            $success = "Vos préférences ont été mises à jour avec succès.";
            // Mettre à jour les informations de l'utilisateur
            $user = getUserById($user_id);
        } else {
            $error = "Une erreur est survenue lors de la mise à jour de vos préférences.";
        }
        
        $stmt->close();
        $conn->close();
    }
}

// Inclusion de l'en-tête
require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-3">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Menu Paramètres</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="#password" class="list-group-item list-group-item-action" data-bs-toggle="pill">
                        <i class="fas fa-key me-2"></i>Changer de mot de passe
                    </a>
                    <a href="#preferences" class="list-group-item list-group-item-action" data-bs-toggle="pill">
                        <i class="fas fa-bell me-2"></i>Préférences de notification
                    </a>
                    <a href="#privacy" class="list-group-item list-group-item-action" data-bs-toggle="pill">
                        <i class="fas fa-shield-alt me-2"></i>Confidentialité
                    </a>
                    <a href="profile.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-user me-2"></i>Retour au profil
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-lg-9">
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="tab-content">
                <!-- Changement de mot de passe -->
                <div class="tab-pane fade show active" id="password">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-key me-2"></i>Changer de mot de passe</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Mot de passe actuel</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">Nouveau mot de passe</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                    <div class="form-text">Le mot de passe doit contenir au moins 8 caractères.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirmer le nouveau mot de passe</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                <button type="submit" name="update_password" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Mettre à jour le mot de passe
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Préférences de notification -->
                <div class="tab-pane fade" id="preferences">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-bell me-2"></i>Préférences de notification</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="email_notifications" name="email_notifications" <?php echo isset($user['email_notifications']) && $user['email_notifications'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="email_notifications">Recevoir des notifications par email</label>
                                </div>
                                <button type="submit" name="update_preferences" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Enregistrer les préférences
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Confidentialité -->
                <div class="tab-pane fade" id="privacy">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Confidentialité</h5>
                        </div>
                        <div class="card-body">
                            <p>Nous prenons votre confidentialité au sérieux. Voici comment nous utilisons vos données :</p>
                            <ul class="list-group mb-3">
                                <li class="list-group-item">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    Vos informations personnelles ne sont jamais partagées avec des tiers.
                                </li>
                                <li class="list-group-item">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    Nous utilisons des cookies uniquement pour améliorer votre expérience sur notre site.
                                </li>
                                <li class="list-group-item">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    Vous pouvez demander la suppression de votre compte à tout moment.
                                </li>
                            </ul>
                            <p>Pour toute question concernant la confidentialité, veuillez nous contacter à <a href="mailto:privacy@flatter.com">privacy@flatter.com</a>.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Activer les onglets Bootstrap
    document.addEventListener('DOMContentLoaded', function() {
        const pillElements = document.querySelectorAll('.list-group-item[data-bs-toggle="pill"]');
        
        pillElements.forEach(function(pill) {
            pill.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Supprimer la classe active de tous les éléments
                pillElements.forEach(function(item) {
                    item.classList.remove('active');
                });
                
                // Ajouter la classe active à l'élément cliqué
                this.classList.add('active');
                
                // Obtenir l'ID de l'onglet cible
                const target = this.getAttribute('href');
                
                // Masquer tous les onglets
                document.querySelectorAll('.tab-pane').forEach(function(pane) {
                    pane.classList.remove('show', 'active');
                });
                
                // Afficher l'onglet cible
                document.querySelector(target).classList.add('show', 'active');
            });
        });
    });
</script>

<?php require_once 'includes/footer.php'; ?>