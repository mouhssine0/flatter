<?php
// Page de gestion des commandes
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
$orders = [];
$total = 0;
$currentPage = 1;
$totalPages = 0;
$limit = 10; // Nombre de commandes par page

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
    
    // Préparation de la requête de base
    $query = "SELECT o.id, o.user_id, o.total_amount, o.status, o.created_at, o.updated_at, 
             u.email, u.firstname, u.lastname
             FROM orders o
             LEFT JOIN users u ON o.user_id = u.id";
    
    // Ajout des filtres si présents
    $whereClause = [];
    $params = [];
    $types = "";
    
    // Filtre par statut
    if (isset($_GET['status']) && !empty($_GET['status'])) {
        $whereClause[] = "o.status = ?";
        $params[] = $_GET['status'];
        $types .= "s";
    }
    
    // Filtre par date (de)
    if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
        $whereClause[] = "o.created_at >= ?";
        $params[] = $_GET['date_from'] . " 00:00:00";
        $types .= "s";
    }
    
    // Filtre par date (à)
    if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
        $whereClause[] = "o.created_at <= ?";
        $params[] = $_GET['date_to'] . " 23:59:59";
        $types .= "s";
    }
    
    // Recherche par email ou nom du client
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search = "%" . $_GET['search'] . "%";
        $whereClause[] = "(u.email LIKE ? OR u.firstname LIKE ? OR u.lastname LIKE ?)";
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $types .= "sss";
    }
    
    // Construction de la clause WHERE complète
    if (!empty($whereClause)) {
        $query .= " WHERE " . implode(" AND ", $whereClause);
    }
    
    // Ajout de l'ordre et de la pagination
    $query .= " ORDER BY o.created_at DESC LIMIT ?, ?";
    $params[] = $offset;
    $params[] = $limit;
    $types .= "ii";
    
    // Exécution de la requête
    $stmt = mysqli_prepare($conn, $query);
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    // Récupération des commandes
    while ($row = mysqli_fetch_assoc($result)) {
        $orders[] = $row;
    }
    
    // Requête pour compter le nombre total de commandes (avec les mêmes filtres)
    $countQuery = "SELECT COUNT(*) as total FROM orders o LEFT JOIN users u ON o.user_id = u.id";
    if (!empty($whereClause)) {
        $countQuery .= " WHERE " . implode(" AND ", $whereClause);
    }
    
    // Exécution de la requête de comptage
    $countStmt = mysqli_prepare($conn, $countQuery);
    if (!empty($params)) {
        // Enlever les deux derniers paramètres (offset et limit)
        array_pop($params);
        array_pop($params);
        $types = substr($types, 0, -2);
        if (!empty($params)) {
            mysqli_stmt_bind_param($countStmt, $types, ...$params);
        }
    }
    mysqli_stmt_execute($countStmt);
    $countResult = mysqli_stmt_get_result($countStmt);
    $countRow = mysqli_fetch_assoc($countResult);
    $total = $countRow['total'];
    
    // Calcul du nombre total de pages
    $totalPages = ceil($total / $limit);
    
    // Traitement de la mise à jour du statut d'une commande
    if (isset($_POST['update_status']) && isset($_POST['order_id']) && isset($_POST['status'])) {
        $orderId = $_POST['order_id'];
        $newStatus = $_POST['status'];
        
        // Vérifier que le statut est valide
        $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        if (in_array($newStatus, $validStatuses)) {
            // Mettre à jour le statut de la commande
            $updateQuery = "UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?";
            $updateStmt = mysqli_prepare($conn, $updateQuery);
            mysqli_stmt_bind_param($updateStmt, "si", $newStatus, $orderId);
            $updateResult = mysqli_stmt_execute($updateStmt);
            
            if ($updateResult) {
                $success = "Statut de la commande mis à jour avec succès.";
                // Redirection pour éviter la soumission multiple du formulaire
                header("Location: orders.php?page=$currentPage&success=updated");
                exit;
            } else {
                $error = "Erreur lors de la mise à jour du statut de la commande: " . mysqli_error($conn);
            }
        } else {
            $error = "Statut invalide.";
        }
    }
    
    // Traitement des messages de succès via GET
    if (isset($_GET['success'])) {
        switch ($_GET['success']) {
            case 'updated':
                $success = "Commande mise à jour avec succès.";
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
                <a href="users.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-users me-2"></i>Gestion des utilisateurs
                </a>
                <a href="register.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-user-plus me-2"></i>Ajouter un administrateur
                </a>
                <a href="orders.php" class="list-group-item list-group-item-action active">
                    <i class="fas fa-shopping-cart me-2"></i>Gestion des commandes
                </a>
                <a href="settings.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-cog me-2"></i>Paramètres
                </a>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Gestion des commandes</h4>
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
                        <div class="col-md-12">
                            <form action="" method="GET" class="row g-3">
                                <div class="col-md-3">
                                    <input type="text" name="search" class="form-control" placeholder="Rechercher un client..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                </div>
                                <div class="col-md-2">
                                    <select name="status" class="form-select">
                                        <option value="">Tous les statuts</option>
                                        <option value="pending" <?php echo isset($_GET['status']) && $_GET['status'] === 'pending' ? 'selected' : ''; ?>>En attente</option>
                                        <option value="processing" <?php echo isset($_GET['status']) && $_GET['status'] === 'processing' ? 'selected' : ''; ?>>En traitement</option>
                                        <option value="shipped" <?php echo isset($_GET['status']) && $_GET['status'] === 'shipped' ? 'selected' : ''; ?>>Expédiée</option>
                                        <option value="delivered" <?php echo isset($_GET['status']) && $_GET['status'] === 'delivered' ? 'selected' : ''; ?>>Livrée</option>
                                        <option value="cancelled" <?php echo isset($_GET['status']) && $_GET['status'] === 'cancelled' ? 'selected' : ''; ?>>Annulée</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="date" name="date_from" class="form-control" placeholder="Date de début" value="<?php echo isset($_GET['date_from']) ? htmlspecialchars($_GET['date_from']) : ''; ?>">
                                </div>
                                <div class="col-md-2">
                                    <input type="date" name="date_to" class="form-control" placeholder="Date de fin" value="<?php echo isset($_GET['date_to']) ? htmlspecialchars($_GET['date_to']) : ''; ?>">
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="fas fa-search me-1"></i>Rechercher
                                    </button>
                                    <a href="orders.php" class="btn btn-secondary">
                                        <i class="fas fa-redo me-1"></i>Réinitialiser
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Tableau des commandes -->
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Client</th>
                                    <th>Montant</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                    <th>Dernière mise à jour</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($orders)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">Aucune commande trouvée</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td><?php echo $order['id']; ?></td>
                                        <td>
                                            <?php if (!empty($order['firstname']) && !empty($order['lastname'])): ?>
                                                <?php echo htmlspecialchars($order['firstname'] . ' ' . $order['lastname']); ?><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($order['email']); ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">Client inconnu</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo number_format($order['total_amount'], 2, ',', ' '); ?> €</td>
                                        <td>
                                            <?php 
                                            $statusClass = '';
                                            $statusText = '';
                                            
                                            switch ($order['status']) {
                                                case 'pending':
                                                    $statusClass = 'bg-warning';
                                                    $statusText = 'En attente';
                                                    break;
                                                case 'processing':
                                                    $statusClass = 'bg-info';
                                                    $statusText = 'En traitement';
                                                    break;
                                                case 'shipped':
                                                    $statusClass = 'bg-primary';
                                                    $statusText = 'Expédiée';
                                                    break;
                                                case 'delivered':
                                                    $statusClass = 'bg-success';
                                                    $statusText = 'Livrée';
                                                    break;
                                                case 'cancelled':
                                                    $statusClass = 'bg-danger';
                                                    $statusText = 'Annulée';
                                                    break;
                                                default:
                                                    $statusClass = 'bg-secondary';
                                                    $statusText = 'Inconnu';
                                            }
                                            ?>
                                            <span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($order['updated_at'])); ?></td>
                                        <td>
                                            <!-- Bouton pour voir les détails de la commande -->
                                            <button type="button" class="btn btn-sm btn-info view-order-details" data-bs-toggle="modal" data-bs-target="#orderDetailsModal" data-order-id="<?php echo $order['id']; ?>" title="Voir les détails">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            
                                            <!-- Bouton pour changer le statut -->
                                            <button type="button" class="btn btn-sm btn-warning ms-1" data-bs-toggle="modal" data-bs-target="#changeStatusModal" data-order-id="<?php echo $order['id']; ?>" data-current-status="<?php echo $order['status']; ?>" title="Changer le statut">
                                                <i class="fas fa-exchange-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <nav aria-label="Pagination des commandes">
                        <ul class="pagination justify-content-end mb-0">
                            <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $currentPage - 1; ?><?php echo isset($_GET['status']) ? '&status=' . htmlspecialchars($_GET['status']) : ''; ?><?php echo isset($_GET['search']) ? '&search=' . htmlspecialchars($_GET['search']) : ''; ?><?php echo isset($_GET['date_from']) ? '&date_from=' . htmlspecialchars($_GET['date_from']) : ''; ?><?php echo isset($_GET['date_to']) ? '&date_to=' . htmlspecialchars($_GET['date_to']) : ''; ?>" aria-label="Précédent">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo isset($_GET['status']) ? '&status=' . htmlspecialchars($_GET['status']) : ''; ?><?php echo isset($_GET['search']) ? '&search=' . htmlspecialchars($_GET['search']) : ''; ?><?php echo isset($_GET['date_from']) ? '&date_from=' . htmlspecialchars($_GET['date_from']) : ''; ?><?php echo isset($_GET['date_to']) ? '&date_to=' . htmlspecialchars($_GET['date_to']) : ''; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $currentPage + 1; ?><?php echo isset($_GET['status']) ? '&status=' . htmlspecialchars($_GET['status']) : ''; ?><?php echo isset($_GET['search']) ? '&search=' . htmlspecialchars($_GET['search']) : ''; ?><?php echo isset($_GET['date_from']) ? '&date_from=' . htmlspecialchars($_GET['date_from']) : ''; ?><?php echo isset($_GET['date_to']) ? '&date_to=' . htmlspecialchars($_GET['date_to']) : ''; ?>" aria-label="Suivant">
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

<!-- Modal pour changer le statut d'une commande -->
<div class="modal fade" id="changeStatusModal" tabindex="-1" aria-labelledby="changeStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changeStatusModalLabel">Changer le statut de la commande</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="order_id" id="statusOrderId" value="">
                    <div class="mb-3">
                        <label for="orderStatus" class="form-label">Nouveau statut</label>
                        <select name="status" id="orderStatus" class="form-select" required>
                            <option value="pending">En attente</option>
                            <option value="processing">En traitement</option>
                            <option value="shipped">Expédiée</option>
                            <option value="delivered">Livrée</option>
                            <option value="cancelled">Annulée</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" name="update_status" class="btn btn-primary">Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal pour afficher les détails d'une commande -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderDetailsModalLabel">Détails de la commande</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <div id="orderDetailsContent">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                        <p>Chargement des détails de la commande...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script>
// Script pour charger les détails d'une commande dans la modal
document.addEventListener('DOMContentLoaded', function() {
    // Gestion du modal de changement de statut
    const changeStatusModal = document.getElementById('changeStatusModal');
    if (changeStatusModal) {
        changeStatusModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const orderId = button.getAttribute('data-order-id');
            const currentStatus = button.getAttribute('data-current-status');
            
            const orderIdInput = document.getElementById('statusOrderId');
            const statusSelect = document.getElementById('orderStatus');
            
            orderIdInput.value = orderId;
            statusSelect.value = currentStatus;
        });
    }
    
    // Gestion du modal de détails de commande
    const orderDetailsModal = document.getElementById('orderDetailsModal');
    if (orderDetailsModal) {
        orderDetailsModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const orderId = button.getAttribute('data-order-id');
            const orderDetailsContent = document.getElementById('orderDetailsContent');
            
            // Réinitialiser le contenu avec un spinner de chargement
            orderDetailsContent.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <p>Chargement des détails de la commande...</p>
                </div>
            `;
            
            // Simuler le chargement des détails (à remplacer par un appel AJAX réel)
            // Note: Dans une implémentation réelle, vous devriez créer un endpoint AJAX pour récupérer les détails
            setTimeout(function() {
                orderDetailsContent.innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>Cette fonctionnalité nécessite l'implémentation d'un endpoint AJAX pour récupérer les détails de la commande.
                    </div>
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Commande #${orderId}</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Les détails de la commande seraient affichés ici, y compris:</p>
                            <ul>
                                <li>Informations du client</li>
                                <li>Adresse de livraison</li>
                                <li>Méthode de paiement</li>
                                <li>Liste des produits commandés</li>
                                <li>Sous-total, taxes, frais de livraison et total</li>
                                <li>Historique des statuts</li>
                            </ul>
                        </div>
                    </div>
                `;
            }, 1000);
        });
    }
});
</script>

<?php require_once 'admin_footer.php'; ?>