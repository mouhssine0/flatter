<?php
// Script de synchronisation automatique pour les tâches planifiées (cron jobs)
// Exemple d'utilisation avec cron: 
// * * * * * php /path/to/cron_sync.php > /dev/null 2>&1

// Désactiver l'affichage des erreurs pour les tâches cron
ini_set('display_errors', 0);

// Charger les dépendances
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db_sync.php';

// Définir les limites d'exécution pour les grandes synchronisations
set_time_limit(600); // 10 minutes
ini_set('memory_limit', '512M');

// Paramètres de synchronisation (modifiables selon vos besoins)
$limit = 100; // Nombre de produits par page
$allPages = true; // Synchroniser toutes les pages

// Fichier de verrouillage pour éviter les exécutions simultanées
$lockFile = __DIR__ . '/sync.lock';

// Vérifier si une synchronisation est déjà en cours
if (file_exists($lockFile)) {
    $lockTime = filemtime($lockFile);
    $currentTime = time();
    
    // Si le verrou existe depuis plus d'une heure, il est probablement obsolète
    if ($currentTime - $lockTime > 3600) {
        unlink($lockFile); // Supprimer le verrou obsolète
    } else {
        // Journaliser et quitter
        error_log("Sync already in progress. Exiting.");
        exit(0);
    }
}

// Créer le fichier de verrouillage
touch($lockFile);

// Journaliser le début de la synchronisation
error_log("Starting CJdropshipping product sync at " . date('Y-m-d H:i:s'));

try {
    // Exécuter la synchronisation
    $startTime = microtime(true);
    $stats = syncProducts(1, $limit, $allPages);
    $executionTime = round(microtime(true) - $startTime, 2);
    
    // Journaliser les résultats
    $logMessage = "Sync completed in {$executionTime}s. ";
    $logMessage .= "Added: {$stats['added']}, Updated: {$stats['updated']}, Failed: {$stats['failed']}, Status: {$stats['status']}";
    
    if (!empty($stats['error_message'])) {
        $logMessage .= ", Error: {$stats['error_message']}";
    }
    
    error_log($logMessage);
    
} catch (Exception $e) {
    // Journaliser les erreurs
    error_log("Sync error: " . $e->getMessage());
} finally {
    // Supprimer le fichier de verrouillage
    if (file_exists($lockFile)) {
        unlink($lockFile);
    }
}

// Sortie pour la tâche cron
echo "Sync completed at " . date('Y-m-d H:i:s') . "\n";
exit(0);
?>