<?php
// Page d'administration pour la gestion des synchronisations de produits
require_once 'includes/config.php';
require_once 'includes/db_sync.php';

// Fonction pour obtenir les journaux de synchronisation
function getSyncLogs($limit = 10) {
    $conn = connectDB();
    if (!$conn) return [];
    
    $logs = [];
    $query = "SELECT * FROM sync_logs ORDER BY sync_date DESC LIMIT ?";
    $stmt = $conn->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }
        
        $stmt->close();
    }
    
    $conn->close();
    return $logs;
}

// Fonction pour obtenir les statistiques des produits
function getProductStats() {
    $conn = connectDB();
    if (!$conn) return [];
    
    $stats = [
        'total' => 0,
        'categories' => 0,
        'last_update' => null
    ];
    
    // Nombre total de produits
    $query = "SELECT COUNT(*) as total FROM products";
    $result = $conn->query($query);
    if ($result && $row = $result->fetch_assoc()) {
        $stats['total'] = $row['total'];
    }
    
    // Nombre de catégories
    $query = "SELECT COUNT(*) as total FROM categories";
    $result = $conn->query($query);
    if ($result && $row = $result->fetch_assoc()) {
        $stats['categories'] = $row['total'];
    }
    
    // Dernière mise à jour
    $query = "SELECT MAX(updated_at) as last_update FROM products";
    $result = $conn->query($query);
    if ($result && $row = $result->fetch_assoc()) {
        $stats['last_update'] = $row['last_update'];
    }
    
    $conn->close();
    return $stats;
}

// Fonction pour obtenir les derniers produits ajoutés
function getLatestProducts($limit = 5) {
    $conn = connectDB();
    if (!$conn) return [];
    
    $products = [];
    $query = "SELECT * FROM products ORDER BY created_at DESC LIMIT ?";
    $stmt = $conn->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        
        $stmt->close();
    }
    
    $conn->close();
    return $products;
}

// Traitement des actions
$action = isset($_GET['action']) ? $_GET['action'] : '';
$message = '';

switch ($action) {
    case 'sync':
        // Lancer une synchronisation manuelle
        $allPages = isset($_GET['all']) && $_GET['all'] === '1';
        $stats = syncProducts(1, 50, $allPages);
        
        if ($stats['status'] === 'success' || $stats['status'] === 'partial_error') {
            $message = "Synchronisation terminée. {$stats['added']} produits ajoutés, {$stats['updated']} produits mis à jour.";
            if ($stats['status'] === 'partial_error') {
                $message .= " Certaines erreurs sont survenues: {$stats['error_message']}";
            }
        } else {
            $message = "Erreur lors de la synchronisation: {$stats['error_message']}";
        }
        break;
        
    case 'truncate':
        // Vider les tables (avec confirmation)
        if (isset($_GET['confirm']) && $_GET['confirm'] === '1') {
            $conn = connectDB();
            if ($conn) {
                // Désactiver les contraintes de clé étrangère temporairement
                $conn->query("SET FOREIGN_KEY_CHECKS = 0");
                
                // Vider les tables
                $conn->query("TRUNCATE TABLE product_images");
                $conn->query("TRUNCATE TABLE products");
                $conn->query("TRUNCATE TABLE categories");
                
                // Réactiver les contraintes
                $conn->query("SET FOREIGN_KEY_CHECKS = 1");
                
                $message = "Toutes les données de produits ont été supprimées.";                
                $conn->close();
            }
        }
        break;
}

// Récupération des données pour l'affichage
$syncLogs = getSyncLogs();
$productStats = getProductStats();
$latestProducts = getLatestProducts();

// Affichage de la page
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration des synchronisations - <?php echo SITE_NAME; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        header {
            background-color: #333;
            color: #fff;
            padding: 10px 20px;
            margin-bottom: 20px;
        }
        header h1 {
            margin: 0;
        }
        .card {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .stat-card {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 20px;
            text-align: center;
        }
        .stat-card h3 {
            margin-top: 0;
            color: #666;
        }
        .stat-card .number {
            font-size: 2.5em;
            font-weight: bold;
            color: #4CAF50;
            margin: 10px 0;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 10px;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #45a049;
        }
        .btn-danger {
            background-color: #f44336;
        }
        .btn-danger:hover {
            background-color: #d32f2f;
        }
        .btn-info {
            background-color: #2196F3;
        }
        .btn-info:hover {
            background-color: #0b7dda;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background-color: #f2f2f2;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #dff0d8;
            border-left: 5px solid #4CAF50;
            color: #3c763d;
        }
        .alert-danger {
            background-color: #f2dede;
            border-left: 5px solid #f44336;
            color: #a94442;
        }
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }
        .product-card {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .product-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        .product-card .product-info {
            padding: 15px;
        }
        .product-card h3 {
            margin-top: 0;
            font-size: 1em;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .product-card .price {
            font-weight: bold;
            color: #4CAF50;
        }
    </style>
</head>
<body>
    <header>
        <h1>Administration des synchronisations - <?php echo SITE_NAME; ?></h1>
    </header>
    
    <div class="container">
        <?php if (!empty($message)): ?>
        <div class="alert <?php echo strpos($message, 'Erreur') !== false ? 'alert-danger' : 'alert-success'; ?>">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Actions</h2>
            <a href="admin_sync.php?action=sync" class="btn">Synchroniser les produits (page courante)</a>
            <a href="admin_sync.php?action=sync&all=1" class="btn">Synchronisation complète</a>
            <a href="sync_products.php" class="btn btn-info" target="_blank">Page de synchronisation détaillée</a>
            <a href="admin_sync.php?action=truncate" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer toutes les données de produits?');">Vider les tables</a>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Produits en base</h3>
                <div class="number"><?php echo $productStats['total']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Catégories</h3>
                <div class="number"><?php echo $productStats['categories']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Dernière mise à jour</h3>
                <div style="font-size: 1.2em; margin: 10px 0;">
                    <?php echo $productStats['last_update'] ? date('d/m/Y H:i', strtotime($productStats['last_update'])) : 'Jamais'; ?>
                </div>
            </div>
        </div>
        
        <div class="card">
            <h2>Derniers produits ajoutés</h2>
            <?php if (empty($latestProducts)): ?>
                <p>Aucun produit n'a encore été synchronisé.</p>
            <?php else: ?>
                <div class="product-grid">
                    <?php foreach ($latestProducts as $product): ?>
                    <div class="product-card">
                        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="price"><?php echo number_format($product['price'], 2, ',', ' '); ?> €</p>
                            <p>Stock: <?php echo $product['stock']; ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h2>Historique des synchronisations</h2>
            <?php if (empty($syncLogs)): ?>
                <p>Aucune synchronisation n'a encore été effectuée.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Ajoutés</th>
                            <th>Mis à jour</th>
                            <th>Échecs</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($syncLogs as $log): ?>
                        <tr>
                            <td><?php echo date('d/m/Y H:i:s', strtotime($log['sync_date'])); ?></td>
                            <td><?php echo $log['products_added']; ?></td>
                            <td><?php echo $log['products_updated']; ?></td>
                            <td><?php echo $log['products_failed']; ?></td>
                            <td>
                                <?php if ($log['status'] === 'success'): ?>
                                    <span style="color: #4CAF50;">Succès</span>
                                <?php elseif ($log['status'] === 'partial_error'): ?>
                                    <span style="color: #FF9800;">Partiel</span>
                                <?php else: ?>
                                    <span style="color: #f44336;">Erreur</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>