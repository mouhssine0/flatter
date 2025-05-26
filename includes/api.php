<?php
// Fichier de gestion des appels à l'API CJdropshipping
require_once 'config.php';

/**
 * Fonction pour obtenir un jeton d'accès à l'API CJdropshipping
 * @return string|null Jeton d'accès ou null en cas d'erreur
 */
function getAccessToken() {
    static $cachedToken = null;
    static $tokenExpiry = 0;
    static $lastAttempt = 0;
    $cacheFile = __DIR__ . '/token_cache.json';
    
    // Essayer de charger le token depuis le cache fichier
    if (!$cachedToken && file_exists($cacheFile)) {
        $cache = json_decode(file_get_contents($cacheFile), true);
        if ($cache && isset($cache['token']) && isset($cache['expiry']) && time() < $cache['expiry']) {
            $cachedToken = $cache['token'];
            $tokenExpiry = $cache['expiry'];
            $lastAttempt = $cache['lastAttempt'];
            return $cachedToken;
        }
    }
    
    // Vérifier si nous avons un token en cache valide
    if ($cachedToken && time() < $tokenExpiry) {
        return $cachedToken;
    }
    
    // Respecter la limite de l'API (1 requête/300 secondes)
    if (time() - $lastAttempt < 300) {
        error_log("CJ API Warning: Rate limit reached (1 request/300 seconds). Waiting...");
        sleep(300 - (time() - $lastAttempt));
    }
    $lastAttempt = time();
    
    // Vérification des informations d'API
    if (empty(CJ_API_KEY) || empty(CJ_EMAIL)) {
        error_log("CJ API Error: Missing API credentials");
        return null;
    }
    
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
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Timeout après 30 secondes
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Vérification SSL
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    // Exécution de la requête
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    // Gestion des erreurs cURL
    if ($error) {
        error_log("CJ API Error (getAccessToken): CURL error - $error");
        return null;
    }
    
    // Vérification de la réponse HTTP
    if ($httpCode !== 200) {
        error_log("CJ API Error (getAccessToken): HTTP $httpCode - Response: $response");
        return null;
    }
    
    // Décodage et validation de la réponse JSON
    $result = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("CJ API Error (getAccessToken): Invalid JSON response - " . json_last_error_msg());
        return null;
    }
    
    // Vérification du statut de l'API
    if (!isset($result['code']) || $result['code'] !== 200) {
        $errorMsg = isset($result['message']) ? $result['message'] : 'Unknown error';
        error_log("CJ API Error (getAccessToken): API error - $errorMsg");
        return null;
    }
    
    // Vérification et stockage du token
    if (isset($result['data']['accessToken'])) {
        $cachedToken = $result['data']['accessToken'];
        $tokenExpiry = time() + 3600; // Token valide pendant 1 heure
        
        // Sauvegarder le token dans le cache fichier
        try {
            $cacheDir = dirname($cacheFile);
            if (!is_dir($cacheDir)) {
                mkdir($cacheDir, 0755, true);
            }
            
            if (!is_writable($cacheDir)) {
                error_log("CJ API Warning: Cache directory is not writable: $cacheDir");
                return $cachedToken;
            }
            
            $cache = [
                'token' => $cachedToken,
                'expiry' => $tokenExpiry,
                'lastAttempt' => $lastAttempt
            ];
            
            if (file_put_contents($cacheFile, json_encode($cache)) === false) {
                error_log("CJ API Warning: Failed to write token cache file: $cacheFile");
            } else {
                chmod($cacheFile, 0644);
            }
        } catch (Exception $e) {
            error_log("CJ API Warning: Error writing token cache - " . $e->getMessage());
        }
        
        return $cachedToken;
    }
    
    error_log("CJ API Error (getAccessToken): No access token in response - " . json_encode($result));
    return null;
}

/**
 * Fonction pour effectuer un appel à l'API CJdropshipping
 * @param string $endpoint Point d'accès de l'API
 * @param array $params Paramètres de la requête
 * @param string $method Méthode HTTP (GET, POST, etc.)
 * @return array Réponse de l'API
 */
function callCJApi($endpoint, $params = [], $method = 'GET') {
    // URL de base de l'API CJdropshipping
    $baseUrl = 'https://developers.cjdropshipping.com/api2.0/v1/';
    $url = $baseUrl . $endpoint;
    
    // Nombre maximum de tentatives
    $maxRetries = 3;
    $retryCount = 0;
    $success = false;
    
    while (!$success && $retryCount < $maxRetries) {
        // Configuration de la requête cURL
        $ch = curl_init();
        
        // Configuration de base
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        // Configuration des en-têtes selon l'endpoint
        $headers = ['Content-Type: application/json', 'Accept: application/json'];
        
        if ($endpoint !== 'authentication/getAccessToken') {
            $accessToken = getAccessToken();
            if (!$accessToken) {
                error_log("CJ API Error ($endpoint): Failed to obtain access token");
                curl_close($ch);
                sleep(1);
                $retryCount++;
                continue;
            }
            $headers[] = 'CJ-Access-Token: ' . $accessToken;
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
         
         // Configuration de la méthode et des paramètres
         if ($method === 'GET') {
             $fullUrl = $url . (!empty($params) ? '?' . http_build_query($params) : '');
             curl_setopt($ch, CURLOPT_URL, $fullUrl);
         } else {
             curl_setopt($ch, CURLOPT_POST, 1);
             curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
         }
         
         // Exécution de la requête
         $response = curl_exec($ch);
         $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
         $error = curl_error($ch);
         curl_close($ch);
         
         // Gestion des erreurs cURL
         if ($error) {
             error_log("CJ API Error ($endpoint): CURL error - $error");
             sleep(1);
             $retryCount++;
             continue;
         }
         
         // Décodage de la réponse JSON
         $result = json_decode($response, true);
         
         // Vérification de la validité de la réponse JSON
         if (json_last_error() !== JSON_ERROR_NONE) {
             error_log("CJ API Error ($endpoint): Invalid JSON response - " . json_last_error_msg());
             sleep(1);
             $retryCount++;
             continue;
         }
         
         // Vérification du code HTTP
         if ($httpCode === 401 && $endpoint !== 'authentication/getAccessToken') {
             error_log("CJ API Error ($endpoint): Token expired or invalid - HTTP 401");
             // Forcer le renouvellement du token en réinitialisant le cache
             global $cachedToken, $tokenExpiry;
             $cachedToken = null;
             $tokenExpiry = 0;
             sleep(1);
             $retryCount++;
             continue;
         } elseif ($httpCode !== 200) {
             error_log("CJ API Error ($endpoint): HTTP $httpCode - Response: " . json_encode($result));
             sleep(1);
             $retryCount++;
             continue;
         }
         
         // Vérification du statut de la réponse API
         if (isset($result['code'])) {
             if ($result['code'] === 1600002 && $endpoint !== 'authentication/getAccessToken') { // Token vide ou invalide
                 error_log("CJ API Error ($endpoint): Empty or invalid token");
                 global $cachedToken, $tokenExpiry;
                 $cachedToken = null;
                 $tokenExpiry = 0;
                 sleep(1);
                 $retryCount++;
                 continue;
             } elseif ($result['code'] !== 200) {
                 error_log("CJ API Error ($endpoint): API error - Code: {$result['code']}, Message: {$result['message']}");
                 return ['error' => $result['message'] ?? 'Erreur de l\'API'];
             }
         }
         
         $success = true;
         return $result;
     }
     
     // Si toutes les tentatives ont échoué
     return ['error' => 'Échec après ' . $maxRetries . ' tentatives'];
}

/**
 * Fonction pour récupérer les produits depuis l'API
 * @param int $page Numéro de page
 * @param int $limit Nombre de produits par page
 * @return array Liste des produits
 */
function getProductsFromAPI($page = 1, $limit = 20) {
    // Vérification des informations d'API
    if (CJ_API_KEY === 'VOTRE_CLE_API' || CJ_EMAIL === 'votre_email@exemple.com') {
        error_log('CJ API Error: API credentials not configured');
        return ['error' => 'Configuration API manquante'];
    }

    $maxRetries = 3;
    $retryCount = 0;
    $retryDelay = 1; // 1 seconde entre les tentatives

    while ($retryCount < $maxRetries) {
        $params = [
            'pageNum' => $page,
            'pageSize' => min($limit, 200), // Maximum 200 résultats par page selon la documentation
            'searchType' => 0, // 0 = Tous les produits
            'sort' => 'desc',
            'orderBy' => 'createAt'
        ];

        $response = callCJApi('product/list', $params);

        // Vérification des erreurs de l'API
        if (isset($response['error'])) {
            error_log('CJ API Error (getProductsFromAPI): ' . $response['error']);
            $retryCount++;
            if ($retryCount < $maxRetries) {
                sleep($retryDelay);
                continue;
            }
            return ['error' => $response['error']];
        }

        // Vérification et retour des données
        if (isset($response['data'])) {
            return [
                'products' => $response['data']['list'] ?? [],
                'total' => $response['data']['total'] ?? 0,
                'pageNum' => $response['data']['pageNum'] ?? $page,
                'pageSize' => $response['data']['pageSize'] ?? $limit
            ];
        }

        $retryCount++;
        if ($retryCount < $maxRetries) {
            sleep($retryDelay);
        }
    }

    error_log('CJ API Error (getProductsFromAPI): Maximum retry attempts reached');
    return ['error' => 'Erreur lors de la récupération des produits'];

}

/**
 * Fonction pour récupérer les détails d'un produit
 * @param string $productId Identifiant du produit
 * @return array|null Détails du produit ou null si non trouvé
 */
function getProductDetails($productId) {
    $params = [
        'pid' => $productId
    ];
    
    $response = callCJApi('product/query', $params);
    
    // Vérification de la réponse
    if (isset($response['data']) && !empty($response['data'])) {
        return $response['data'];
    }
    
    // En cas d'erreur ou de produit non trouvé
    return null;
}

/**
 * Fonction pour rechercher des produits
 * @param string $keyword Mot-clé de recherche
 * @param int $page Numéro de page
 * @param int $limit Nombre de produits par page
 * @return array Liste des produits correspondants
 */
function searchProducts($keyword, $page = 1, $limit = 20) {
    // Vérification des informations d'API
    if (CJ_API_KEY === 'VOTRE_CLE_API' || CJ_EMAIL === 'votre_email@exemple.com') {
        // Retourner un tableau vide si les informations d'API ne sont pas configurées
        return [];
    }
    
    $params = [
        'pageNum' => $page,
        'pageSize' => $limit,
        'keyword' => $keyword
    ];
    
    $response = callCJApi('product/list', $params);
    
    // Vérification de la réponse
    if (isset($response['data']['list']) && is_array($response['data']['list'])) {
        return $response['data']['list'];
    }
    
    // En cas d'erreur ou de réponse vide
    return [];
}
