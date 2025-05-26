<?php
// Fichier contenant les fonctions utilitaires du site
require_once 'api.php';
require_once 'db_sync.php';

/**
 * Récupère les produits à afficher sur le site
 * @param int $page Numéro de page
 * @param int $limit Nombre de produits par page
 * @return array Liste des produits formatés pour l'affichage
 */
function getProducts($page = 1, $limit = 12) {
    // Vérification des informations d'API
    if (CJ_API_KEY === 'VOTRE_CLE_API' || CJ_EMAIL === 'votre_email@exemple.com') {
        error_log('CJ API Error: API credentials not configured');
        return [
            'products' => [],
            'total' => 0,
            'currentPage' => $page,
            'totalPages' => 0,
            'error' => 'Configuration API manquante'
        ];
    }
    
    // Récupération des produits depuis l'API
    $apiResponse = getProductsFromAPI($page, $limit);
    
    // Vérification des erreurs
    if (isset($apiResponse['error'])) {
        error_log('CJ API Error (getProducts): ' . $apiResponse['error']);
        return [
            'products' => [],
            'total' => 0,
            'currentPage' => $page,
            'totalPages' => 0,
            'error' => $apiResponse['error']
        ];
    }
    
    $products = [];
    $apiProducts = $apiResponse['products'] ?? [];
    
    // Formatage des données pour l'affichage
    foreach ($apiProducts as $product) {
        // Vérification des données minimales requises
        if (!isset($product['pid']) || !isset($product['productNameEn'])) {
            error_log('CJ API Warning: Product missing required fields - ' . json_encode($product));
            continue;
        }
        
        // Récupération de l'image principale (ou image par défaut)
        $image = isset($product['productImage']) && !empty($product['productImage']) 
            ? $product['productImage'] 
            : 'https://via.placeholder.com/300x300';
        
        // Récupération du prix (ou prix par défaut)
        $price = isset($product['sellPrice']) && !empty($product['sellPrice']) 
            ? floatval($product['sellPrice']) 
            : 0;
        
        // Construction du tableau de produit formaté
        $products[] = [
            'id' => $product['pid'],
            'name' => $product['productNameEn'],
            'description' => $product['description'] ?? 'Aucune description disponible',
            'image' => $image,
            'price' => $price,
            'category' => $product['categoryName'] ?? 'Non catégorisé',
            'stock' => isset($product['inventory']) ? intval($product['inventory']) : 0
        ];
    }
    
    // Calcul du nombre total de pages
    $total = $apiResponse['total'] ?? 0;
    $totalPages = ceil($total / $limit);
    
    return [
        'products' => $products,
        'total' => $total,
        'currentPage' => intval($apiResponse['pageNum'] ?? $page),
        'totalPages' => $totalPages,
        'error' => null
    ];

}

/**
 * Récupère les détails d'un produit spécifique
 * @param string $productId Identifiant du produit
 * @return array|null Détails du produit ou null si non trouvé
 */
function getProductById($productId) {
    // Vérification des informations d'API
    if (CJ_API_KEY === 'VOTRE_CLE_API' || CJ_EMAIL === 'votre_email@exemple.com') {
        return null;
    }
    
    $apiProduct = getProductDetails($productId);
    
    if (!$apiProduct) {
        return null;
    }
    
    // Formatage des données pour l'affichage
    $product = [
        'id' => $apiProduct['pid'],
        'name' => $apiProduct['productNameEn'],
        'price' => isset($apiProduct['sellPrice']) ? $apiProduct['sellPrice'] : 0,
        'images' => []
    ];
    
    // Récupération des images
    $images = [];
    
    if (isset($apiProduct['productImage'])) {
        $imageData = $apiProduct['productImage'];
        if (substr($imageData, 0, 2) === '["' || substr($imageData, 0, 2) === '["`') {
            $imageUrls = json_decode($imageData);
            if (is_array($imageUrls)) {
                foreach ($imageUrls as $url) {
                    $cleanUrl = trim($url, '"`[] ');
                    if (!empty($cleanUrl) && !in_array($cleanUrl, $images)) {
                        $images[] = $cleanUrl;
                    }
                }
            }
        } else {
            $cleanUrl = trim($imageData, '"`[] ');
            if (!empty($cleanUrl)) {
                $images[] = $cleanUrl;
            }
        }
    }
    
    if (isset($apiProduct['productImages']) && is_array($apiProduct['productImages'])) {
        foreach ($apiProduct['productImages'] as $image) {
            $cleanUrl = trim($image, '"`[] ');
            if (!empty($cleanUrl) && !in_array($cleanUrl, $images)) {
                $images[] = $cleanUrl;
            }
        }
    }
    
    if (empty($images)) {
        $images[] = 'https://via.placeholder.com/600x400';
    }
    
    $product['images'] = $images;
    
    // Traitement de la description pour supprimer les balises img
    $description = isset($apiProduct['description']) ? $apiProduct['description'] : 'Aucune description disponible';
    $description = preg_replace('/<img[^>]+>/i', '', $description);
    $product['description'] = $description;
    
    return $product;
}

/**
 * Formate un prix pour l'affichage
 * @param float $price Prix à formater
 * @return string Prix formaté
 */
function formatPrice($price) {
    return number_format($price, 2, ',', ' ');
}

/**
 * Génère une URL sécurisée
 * @param string $url URL à sécuriser
 * @return string URL sécurisée
 */
function safeUrl($url) {
    return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
}

/**
 * Récupère les produits depuis la base de données avec leurs images associées
 * @param int $page Numéro de page
 * @param int $limit Nombre de produits par page
 * @return array Liste des produits avec leurs images
 */
/**
 * Récupère les catégories depuis la base de données
 * @return array Liste des catégories
 */
function getCategories() {
    // Connexion à la base de données
    $conn = connectDB();
    if (!$conn) {
        error_log('Erreur de connexion à la base de données lors de la récupération des catégories');
        return null;
    }
    
    // Récupération des catégories
    $query = "SELECT id as categoryId, name as categoryName FROM categories ORDER BY name ASC";
    $result = $conn->query($query);
    
    if (!$result) {
        error_log('Erreur lors de la récupération des catégories: ' . $conn->error);
        $conn->close();
        return null;
    }
    
    $categories = [];
    while ($category = $result->fetch_assoc()) {
        $categories[] = $category;
    }
    
    $conn->close();
    return $categories;
}

function getProductsFromDatabase($page = 1, $limit = 12) {
    $offset = ($page - 1) * $limit;
    
    // Connexion à la base de données
    $conn = connectDB();
    if (!$conn) {
        return [
            'products' => [],
            'total' => 0,
            'currentPage' => $page,
            'totalPages' => 0,
            'error' => 'Erreur de connexion à la base de données'
        ];
    }
    
    // Récupération du nombre total de produits
    $totalResult = $conn->query("SELECT COUNT(*) as total FROM products");
    $total = $totalResult->fetch_assoc()['total'];
    $totalPages = ceil($total / $limit);
    
    // Récupération des produits avec pagination
    $query = "SELECT p.*, c.name as category_name 
              FROM products p 
              LEFT JOIN categories c ON p.categoryId = c.id 
              ORDER BY p.created_at DESC 
              LIMIT ? OFFSET ?"; 
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    
    while ($product = $result->fetch_assoc()) {
        // Récupération des images pour ce produit
        $imageQuery = "SELECT * FROM product_images WHERE product_id = ? ORDER BY is_main DESC";
        $imageStmt = $conn->prepare($imageQuery);
        $imageStmt->bind_param("s", $product['id']);
        $imageStmt->execute();
        $imageResult = $imageStmt->get_result();
        
        $images = [];
        while ($image = $imageResult->fetch_assoc()) {
            $images[] = $image['image_url'];
        }
        
        // Si aucune image n'est trouvée, utiliser l'image par défaut
        if (empty($images) && !empty($product['image'])) {
            $images[] = $product['image'];
        } elseif (empty($images)) {
            $images[] = 'https://via.placeholder.com/300x300';
        }
        
        // Construction du tableau de produit formaté
        $products[] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'description' => $product['description'] ?? 'Aucune description disponible',
            'image' => $images[0], // Image principale
            'images' => $images,   // Toutes les images
            'price' => floatval($product['price']),
            'category' => $product['category'] ?? $product['category_name'] ?? 'Non catégorisé',
            'stock' => intval($product['stock'])
        ];
        
        $imageStmt->close();
    }
    
    $stmt->close();
    $conn->close();
    
    return [
        'products' => $products,
        'total' => $total,
        'currentPage' => $page,
        'totalPages' => $totalPages,
        'error' => null
    ];
}
?>