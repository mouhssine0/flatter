<?php
// Script de test pour l'API CJdropshipping
require_once 'includes/config.php';
require_once 'includes/api.php';

// Afficher les constantes API
echo "Constantes API:\n";
echo "CJ_API_KEY = " . CJ_API_KEY . "\n";
echo "CJ_EMAIL = " . CJ_EMAIL . "\n\n";

// Tester l'appel à l'API
echo "Test d'appel à l'API:\n";
$response = callCJApi('product/list', ['pageNum' => 1, 'pageSize' => 5]);
echo "Réponse API: " . json_encode($response, JSON_PRETTY_PRINT) . "\n";
?>