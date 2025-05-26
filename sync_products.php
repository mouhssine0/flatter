<?php
// Script de synchronisation des produits CJdropshipping avec la base de données
// Ce script peut être exécuté manuellement ou via une tâche cron

require_once 'includes/config.php';
require_once 'includes/db_sync.php';

// Définir l'en-tête pour éviter les timeouts sur les grandes synchronisations
set_time_limit(300); // 5 minutes
ini_set('memory_limit', '256M');

// Paramètres de synchronisation
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
$allPages = isset($_GET['all']) && $_GET['all'] === '1';

// Vérification de sécurité (optionnelle) - Décommenter pour ajouter une protection
/*
$apiKey = isset($_GET['key']) ? $_GET['key'] : '';
$secureKey = 'votre_clé_secrète'; // À définir selon vos besoins

if ($apiKey !== $secureKey) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Accès non autorisé']);
    exit;
}
*/

// Mode d'affichage
$outputMode = isset($_GET['output']) ? $_GET['output'] : 'html';

// Exécution de la synchronisation
$startTime = microtime(true);
$stats = syncProducts($page, $limit, $allPages);
$executionTime = round(microtime(true) - $startTime, 2);

// Préparation des données de sortie
$output = [
    'stats' => $stats,
    'execution_time' => $executionTime,
    'date' => date('Y-m-d H:i:s'),
    'params' => [
        'page' => $page,
        'limit' => $limit,
        'all_pages' => $allPages
    ]
];

// Affichage des résultats selon le mode demandé
switch ($outputMode) {
    case 'json':
        header('Content-Type: application/json');
        echo json_encode($output);
        break;
        
    case 'cli':
        echo "=== Synchronisation des produits CJdropshipping ===\n";
        echo "Date: {$output['date']}\n";
        echo "Temps d'exécution: {$output['execution_time']} secondes\n\n";
        echo "Produits ajoutés: {$stats['added']}\n";
        echo "Produits mis à jour: {$stats['updated']}\n";
        echo "Produits en échec: {$stats['failed']}\n";
        echo "Statut: {$stats['status']}\n";
        
        if (!empty($stats['error_message'])) {
            echo "\nMessage d'erreur: {$stats['error_message']}\n";
        }
        break;
        
    case 'html':
    default:
        // Affichage HTML par défaut
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Synchronisation des produits - <?php echo SITE_NAME; ?></title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    margin: 0;
                    padding: 20px;
                    background-color: #f5f5f5;
                }
                .container {
                    max-width: 800px;
                    margin: 0 auto;
                    background-color: #fff;
                    padding: 20px;
                    border-radius: 5px;
                    box-shadow: 0 0 10px rgba(0,0,0,0.1);
                }
                h1 {
                    color: #333;
                    border-bottom: 1px solid #eee;
                    padding-bottom: 10px;
                }
                .stats {
                    margin: 20px 0;
                    padding: 15px;
                    background-color: #f9f9f9;
                    border-left: 4px solid #4CAF50;
                }
                .error {
                    background-color: #ffebee;
                    border-left: 4px solid #f44336;
                    padding: 15px;
                    margin: 20px 0;
                }
                .info {
                    color: #666;
                    font-size: 0.9em;
                }
                .actions {
                    margin-top: 20px;
                }
                .btn {
                    display: inline-block;
                    padding: 8px 16px;
                    background-color: #4CAF50;
                    color: white;
                    text-decoration: none;
                    border-radius: 4px;
                    margin-right: 10px;
                }
                .btn:hover {
                    background-color: #45a049;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>Synchronisation des produits CJdropshipping</h1>
                
                <div class="info">
                    <p>Date: <?php echo $output['date']; ?></p>
                    <p>Temps d'exécution: <?php echo $output['execution_time']; ?> secondes</p>
                    <p>Page: <?php echo $page; ?> | Limite: <?php echo $limit; ?> produits | Mode: <?php echo $allPages ? 'Toutes les pages' : 'Page unique'; ?></p>
                </div>
                
                <div class="stats">
                    <h2>Résultats</h2>
                    <p>Produits ajoutés: <strong><?php echo $stats['added']; ?></strong></p>
                    <p>Produits mis à jour: <strong><?php echo $stats['updated']; ?></strong></p>
                    <p>Produits en échec: <strong><?php echo $stats['failed']; ?></strong></p>
                    <p>Statut: <strong><?php echo $stats['status']; ?></strong></p>
                </div>
                
                <?php if (!empty($stats['error_message'])): ?>
                <div class="error">
                    <h3>Erreur</h3>
                    <p><?php echo $stats['error_message']; ?></p>
                </div>
                <?php endif; ?>
                
                <div class="actions">
                    <a href="sync_products.php" class="btn">Synchroniser à nouveau</a>
                    <a href="sync_products.php?all=1" class="btn">Synchroniser toutes les pages</a>
                    <a href="index.php" class="btn" style="background-color: #2196F3;">Retour au site</a>
                </div>
            </div>
        </body>
        </html>
        <?php
        break;
}
?>