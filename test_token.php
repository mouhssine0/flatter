<?php
// Script pour obtenir un jeton d'accès CJdropshipping
require_once 'includes/config.php';

// URL pour obtenir le jeton d'accès
$url = 'https://developers.cjdropshipping.com/api2.0/v1/authentication/getAccessToken';

// Paramètres de la requête
$params = [
    'email' => CJ_EMAIL,
    'apiKey' => CJ_API_KEY
];

// Configuration de la requête cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

// Exécution de la requête
$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

// Affichage des résultats
echo "=== Test d'authentification CJdropshipping ===\n";
echo "Date/Heure: " . date('Y-m-d H:i:s') . "\n";
echo "Email: " . CJ_EMAIL . "\n";
echo "API Key: " . substr(CJ_API_KEY, 0, 8) . "..." . substr(CJ_API_KEY, -4) . "\n\n";

echo "Paramètres de la requête:\n";
echo json_encode($params, JSON_PRETTY_PRINT) . "\n\n";

if ($error) {
    echo "❌ Erreur cURL: $error\n";
} else {
    $result = json_decode($response, true);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    echo "Code HTTP: $httpCode\n";
    echo "Réponse de l'API:\n";
    echo json_encode($result, JSON_PRETTY_PRINT) . "\n\n";
    
    if ($httpCode !== 200) {
        echo "❌ Erreur HTTP $httpCode\n";
        if (isset($result['message'])) {
            echo "Message: {$result['message']}\n";
        }
    } elseif (isset($result['data']['accessToken'])) {
        echo "✅ Jeton d'accès obtenu avec succès!\n";
        echo "Token: " . substr($result['data']['accessToken'], 0, 10) . "...\n";
        echo "Expire dans: " . $result['data']['accessTokenExpiryTime'] . " secondes\n";
        
        // Vérifier le fichier de cache
        $cacheFile = __DIR__ . '/includes/token_cache.json';
        if (file_exists($cacheFile)) {
            $cache = json_decode(file_get_contents($cacheFile), true);
            echo "\nCache du token:\n";
            echo "- Créé le: " . date('Y-m-d H:i:s', $cache['lastAttempt']) . "\n";
            echo "- Expire le: " . date('Y-m-d H:i:s', $cache['expiry']) . "\n";
        } else {
            echo "\n⚠️ Fichier de cache non trouvé\n";
        }
    } else {
        echo "❌ Format de réponse invalide\n";
    }
}
?>