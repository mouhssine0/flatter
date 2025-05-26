<?php
// Page de gestion des catégories
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
$categories = [];
$categoryName = '';
$categoryId = 0;
$isEditing = false;

// Connexion à la base de données
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$conn) {
    $error = "Erreur de connexion à la base de données: " . mysqli_connect_error();
} else {
    // Traitement de l'ajout d'une catégorie
    if (isset($_POST['add_category'])) {
        $categoryName = trim($_POST['category_name']);
        
        if (empty($categoryName)) {
            $error = "Le nom de la catégorie ne peut pas être vide.";
        } else {
            // Vérifier si la catégorie existe déjà
            $checkQuery = "SELECT id FROM categories WHERE name = ?";
            $checkStmt = mysqli_prepare($conn, $checkQuery);
            mysqli_stmt_bind_param($checkStmt, "s", $categoryName);
            mysqli_stmt_execute($checkStmt);
            mysqli_stmt_store_result($checkStmt);
            
            if (mysqli_stmt_num_rows($checkStmt) > 0) {
                $error = "Cette catégorie existe déjà.";
            } else {
                // Ajouter la nouvelle catégorie
                $insertQuery = "INSERT INTO categories (name) VALUES (?)";
                $insertStmt = mysqli_prepare($conn, $insertQuery);
                mysqli_stmt_bind_param($insertStmt, "s", $categoryName);
                $insertResult = mysqli_stmt_execute($insertStmt);
                
                if ($insertResult) {
                    $success = "Catégorie ajoutée avec succès.";
                    $categoryName = ''; // Réinitialiser le champ
                } else {
                    $error = "Erreur lors de l'ajout de la catégorie: " . mysqli_error($conn);
                }
            }
        }
    }
    
    // Traitement de la mise à jour d'une catégorie
    if (isset($_POST['update_category'])) {
        $categoryId = $_POST['category_id'];
        $categoryName = trim($_POST['category_name']);
        
        if (empty($categoryName)) {
            $error = "Le nom de la catégorie ne peut pas être vide.";
        } else {
            // Vérifier si la catégorie existe déjà (sauf celle en cours d'édition)
            $checkQuery = "SELECT id FROM categories WHERE name = ? AND id != ?";
            $checkStmt = mysqli_prepare($conn, $checkQuery);
            mysqli_stmt_bind_param($checkStmt, "si", $categoryName, $categoryId);
            mysqli_stmt_execute($checkStmt);
            mysqli_stmt_store_result($checkStmt);
            
            if (mysqli_stmt_num_rows($checkStmt) > 0) {
                $error = "Cette catégorie existe déjà.";
            } else {
                // Mettre à jour la catégorie
                $updateQuery = "UPDATE categories SET name = ? WHERE id = ?";
                $updateStmt = mysqli_prepare($conn, $updateQuery);
                mysqli_stmt_bind_param($updateStmt, "si", $categoryName, $categoryId);
                $updateResult = mysqli_stmt_execute($updateStmt);
                
                if ($updateResult) {
                    $success = "Catégorie mise à jour avec succès.";
                    $categoryName = ''; // Réinitialiser le champ
                    $categoryId = 0;
                    $isEditing = false;
                } else {
                    $error = "Erreur lors de la mise à jour de la catégorie: " . mysqli_error($conn);
                }
            }
        }
    }
    
    // Traitement de la suppression d'une catégorie
    if (isset($_POST['delete_category'])) {
        $categoryId = $_POST['category_id'];
        
        // Vérifier si des produits utilisent cette catégorie
        $checkProductsQuery = "SELECT COUNT(*) as count FROM products WHERE categoryId = ?";
        $checkProductsStmt = mysqli_prepare($conn, $checkProductsQuery);
        mysqli_stmt_bind_param($checkProductsStmt, "i", $categoryId);
        mysqli_stmt_execute($checkProductsStmt);
        $checkProductsResult = mysqli_stmt_get_result($checkProductsStmt);
        $productCount = mysqli_fetch_assoc($checkProductsResult)['count'];
        
        if ($productCount > 0) {
            $error = "Impossible de supprimer cette catégorie car elle est utilisée par $productCount produit(s).";
        } else {
            // Supprimer la catégorie
            $deleteQuery = "DELETE FROM categories WHERE id = ?";
            $deleteStmt = mysqli_prepare($conn, $deleteQuery);
            mysqli_stmt_bind_param($deleteStmt, "i", $categoryId);
            $deleteResult = mysqli_stmt_execute($deleteStmt);
            
            if ($deleteResult) {
                $success = "Catégorie supprimée avec succès.";
            } else {
                $error = "Erreur lors de la suppression de la catégorie: " . mysqli_error($conn);
            }
        }
    }
    
    // Récupération d'une catégorie pour édition
    if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
        $categoryId = (int)$_GET['edit'];
        $editQuery = "SELECT id, name FROM categories WHERE id = ?";
        $editStmt = mysqli_prepare($conn, $editQuery);
        mysqli_stmt_bind_param($editStmt, "i", $categoryId);
        mysqli_stmt_execute($editStmt);
        $editResult = mysqli_stmt_get_result($editStmt);
        
        if ($category = mysqli_fetch_assoc($editResult)) {
            $categoryName = $category['name'];
            $isEditing = true;
        } else {
            $error = "Catégorie non trouvée.";
        }
    }
    
    // Récupération de toutes les catégories
    $query = "SELECT c.id, c.name, COUNT(p.id) as product_count 
             FROM categories c 
             LEFT JOIN products p ON c.id = p.categoryId 
             GROUP BY c.id 
             ORDER BY c.name ASC";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $categories[] = $row;
        }
    } else {
        $error = "Erreur lors de la récupération des catégories: " . mysqli_error($conn);
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
                <a href="categories.php" class="list-group-item list-group-item-action active">
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
            <div class="row">
                <!-- Formulaire d'ajout/édition de catégorie -->
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <?php echo $isEditing ? '<i class="fas fa-edit me-2"></i>Modifier la catégorie' : '<i class="fas fa-plus me-2"></i>Ajouter une catégorie'; ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>
                            
                            <?php if (!empty($success)): ?>
                                <div class="alert alert-success"><?php echo $success; ?></div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <?php if ($isEditing): ?>
                                    <input type="hidden" name="category_id" value="<?php echo $categoryId; ?>">
                                <?php endif; ?>
                                
                                <div class="mb-3">
                                    <label for="category_name" class="form-label">Nom de la catégorie</label>
                                    <input type="text" class="form-control" id="category_name" name="category_name" value="<?php echo htmlspecialchars($categoryName); ?>" required>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <?php if ($isEditing): ?>
                                        <button type="submit" name="update_category" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i>Mettre à jour
                                        </button>
                                        <a href="categories.php" class="btn btn-secondary">
                                            <i class="fas fa-times me-1"></i>Annuler
                                        </a>
                                    <?php else: ?>
                                        <button type="submit" name="add_category" class="btn btn-success">
                                            <i class="fas fa-plus me-1"></i>Ajouter
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Liste des catégories -->
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Liste des catégories</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nom</th>
                                            <th>Produits</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($categories)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-4">Aucune catégorie trouvée</td>
                                        </tr>
                                        <?php else: ?>
                                            <?php foreach ($categories as $category): ?>
                                            <tr>
                                                <td><?php echo $category['id']; ?></td>
                                                <td><?php echo htmlspecialchars($category['name']); ?></td>
                                                <td>
                                                    <span class="badge bg-info"><?php echo $category['product_count']; ?></span>
                                                </td>
                                                <td>
                                                    <a href="?edit=<?php echo $category['id']; ?>" class="btn btn-sm btn-primary" title="Modifier">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="" method="POST" class="d-inline">
                                                        <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                                        <button type="submit" name="delete_category" class="btn btn-sm btn-danger confirm-action" 
                                                                data-confirm-message="Êtes-vous sûr de vouloir supprimer cette catégorie ?" title="Supprimer"
                                                                <?php echo $category['product_count'] > 0 ? 'disabled' : ''; ?>>
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'admin_footer.php'; ?>