<?php
// Page de gestion des utilisateurs
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

// Initialisation des variables
$error = '';
$success = '';
$users = [];
$total = 0;
$currentPage = 1;
$totalPages = 0;
$limit = 10; // Nombre d'utilisateurs par page

// Récupération de la page courante
if (isset($_GET['page']) && is_numeric($_GET['page'])) {
    $currentPage = (int)$_GET['page'];
}

// Connexion à la base de données
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$conn) {
    $error = "Erreur de connexion à la base de données: " . mysqli_connect_error();
} else {
    // Calcul de l'offset pour la pagination
    $offset = ($currentPage - 1) * $limit;
    
    // Récupération des utilisateurs avec pagination
    $query = "SELECT id, email, firstname, lastname, role, created_at, last_login 
             FROM users 
             ORDER BY created_at DESC 
             LIMIT ?, ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $offset, $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    // Récupération des utilisateurs
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
    
    // Récupération du nombre total d'utilisateurs
    $countQuery = "SELECT COUNT(*) as total FROM users";
    $countResult = mysqli_query($conn, $countQuery);
    $countRow = mysqli_fetch_assoc($countResult);
    $total = $countRow['total'];
    
    // Calcul du nombre total de pages
    $totalPages = ceil($total / $limit);
    
    // Traitement de la suppression d'un utilisateur
    if (isset($_POST['delete_user']) && isset($_POST['user_id'])) {
        $userId = $_POST['user_id'];
        $currentUserId = $_SESSION['user_id'];
        
        // Empêcher la suppression de son propre compte
        if ($userId == $currentUserId) {
            $error = "Vous ne pouvez pas supprimer votre propre compte.";
        } else {
            // Supprimer l'utilisateur
            $deleteQuery = "DELETE FROM users WHERE id = ?";
            $deleteStmt = mysqli_prepare($conn, $deleteQuery);
            mysqli_stmt_bind_param($deleteStmt, "i", $userId);
            $deleteResult = mysqli_stmt_execute($deleteStmt);
            
            if ($deleteResult) {
                $success = "Utilisateur supprimé avec succès.";
                // Redirection pour éviter la soumission multiple du formulaire
                header("Location: users.php?page=$currentPage&success=deleted");
                exit;
            } else {
                $error = "Erreur lors de la suppression de l'utilisateur: " . mysqli_error($conn);
            }
        }
    }
    
    // Traitement du changement de rôle d'un utilisateur
    if (isset($_POST['change_role']) && isset($_POST['user_id']) && isset($_POST['role'])) {
        $userId = $_POST['user_id'];
        $newRole = $_POST['role'];
        $currentUserId = $_SESSION['user_id'];
        
        // Empêcher le changement de son propre rôle
        if ($userId == $currentUserId) {
            $error = "Vous ne pouvez pas changer votre propre rôle.";
        } else {
            // Vérifier que le rôle est valide
            if ($newRole === 'admin' || $newRole === 'user') {
                // Mettre à jour le rôle de l'utilisateur
                $updateQuery = "UPDATE users SET role = ? WHERE id = ?";
                $updateStmt = mysqli_prepare($conn, $updateQuery);
                mysqli_stmt_bind_param($updateStmt, "si", $newRole, $userId);
                $updateResult = mysqli_stmt_execute($updateStmt);
                
                if ($updateResult) {
                    $success = "Rôle de l'utilisateur mis à jour avec succès.";
                    // Redirection pour éviter la soumission multiple du formulaire
                    header("Location: users.php?page=$currentPage&success=updated");
                    exit;
                } else {
                    $error = "Erreur lors de la mise à jour du rôle de l'utilisateur: " . mysqli_error($conn);
                }
            } else {
                $error = "Rôle invalide.";
            }
        }
    }
    
    // Traitement des messages de succès via GET
    if (isset($_GET['success'])) {
        switch ($_GET['success']) {
            case 'deleted':
                $success = "Utilisateur supprimé avec succès.";
                break;
            case 'updated':
                $success = "Utilisateur mis à jour avec succès.";
                break;
        }
    }
    
    mysqli_close($conn);
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
                <a href="users.php" class="list-group-item list-group-item-action active">
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
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-users me-2"></i>Gestion des utilisateurs</h4>
                    <a href="register.php" class="btn btn-light btn-sm">
                        <i class="fas fa-user-plus me-1"></i>Ajouter un administrateur
                    </a>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <!-- Filtres et recherche -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <form action="" method="GET" class="d-flex">
                                <input type="text" name="search" class="form-control me-2" placeholder="Rechercher un utilisateur..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                            </form>
                        </div>
                        <div class="col-md-6 text-end">
                            <form action="" method="GET" class="d-inline-flex">
                                <select name="role" class="form-select me-2">
                                    <option value="">Tous les rôles</option>
                                    <option value="admin" <?php echo isset($_GET['role']) && $_GET['role'] === 'admin' ? 'selected' : ''; ?>>Administrateurs</option>
                                    <option value="user" <?php echo isset($_GET['role']) && $_GET['role'] === 'user' ? 'selected' : ''; ?>>Utilisateurs</option>
                                </select>
                                <button type="submit" class="btn btn-secondary">
                                    <i class="fas fa-filter me-1"></i>Filtrer
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Tableau des utilisateurs -->
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Rôle</th>
                                    <th>Inscription</th>
                                    <th>Dernière connexion</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">Aucun utilisateur trouvé</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td><?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <?php if ($user['role'] === 'admin'): ?>
                                                <span class="badge bg-danger">Administrateur</span>
                                            <?php else: ?>
                                                <span class="badge bg-info">Utilisateur</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <?php echo $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Jamais'; ?>
                                        </td>
                                        <td>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <!-- Changer le rôle -->
                                                <form action="" method="POST" class="d-inline">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <input type="hidden" name="role" value="<?php echo $user['role'] === 'admin' ? 'user' : 'admin'; ?>">
                                                    <button type="submit" name="change_role" class="btn btn-sm btn-warning confirm-action" 
                                                            data-confirm-message="Êtes-vous sûr de vouloir changer le rôle de cet utilisateur ?" title="Changer le rôle">
                                                        <i class="fas fa-exchange-alt"></i>
                                                    </button>
                                                </form>
                                                
                                                <!-- Supprimer l'utilisateur -->
                                                <form action="" method="POST" class="d-inline">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <button type="submit" name="delete_user" class="btn btn-sm btn-danger confirm-action" 
                                                            data-confirm-message="Êtes-vous sûr de vouloir supprimer cet utilisateur ?" title="Supprimer">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Vous-même</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <nav aria-label="Pagination des utilisateurs">
                        <ul class="pagination justify-content-end mb-0">
                            <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $currentPage - 1; ?><?php echo isset($_GET['role']) ? '&role=' . htmlspecialchars($_GET['role']) : ''; ?>" aria-label="Précédent">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo isset($_GET['role']) ? '&role=' . htmlspecialchars($_GET['role']) : ''; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $currentPage + 1; ?><?php echo isset($_GET['role']) ? '&role=' . htmlspecialchars($_GET['role']) : ''; ?>" aria-label="Suivant">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'admin_footer.php'; ?>