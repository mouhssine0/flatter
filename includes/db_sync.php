<?php
// Script d'automatisation pour stocker les produits CJdropshipping dans la base de données
require_once 'config.php';
require_once 'api.php';
require_once 'functions.php';

/**
 * Fonction pour établir une connexion à la base de données
 * @return mysqli|null Connexion à la base de données ou null en cas d'erreur
 */
function connectDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Vérification de la connexion
    if ($conn->connect_error) {
        error_log("DB Connection Error: " . $conn->connect_error);
        return null;
    }
    
    // Définir l'encodage UTF-8
    $conn->set_charset("utf8mb4");
    
    return $conn;
}

/**
 * Fonction pour créer les tables nécessaires si elles n'existent pas
 * @param mysqli $conn Connexion à la base de données
 * @return bool Succès de l'opération
 */
function createTables($conn) {
    // Table des catégories (doit être créée avant products pour la clé étrangère)
    $categoryTable = "CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    // Table des produits
    $productTable = "CREATE TABLE IF NOT EXISTS products (
        id VARCHAR(50) PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        image VARCHAR(255),
        price DECIMAL(10,2) NOT NULL DEFAULT 0,
        category VARCHAR(100),
        categoryId INT,
        stock INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (categoryId) REFERENCES categories(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    // Table des images de produits (pour stocker plusieurs images par produit)
    $imageTable = "CREATE TABLE IF NOT EXISTS product_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id VARCHAR(50),
        image_url VARCHAR(255) NOT NULL,
        is_main TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    // Table des synchronisations (pour suivre les mises à jour)
    $syncTable = "CREATE TABLE IF NOT EXISTS sync_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sync_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        products_added INT NOT NULL DEFAULT 0,
        products_updated INT NOT NULL DEFAULT 0,
        products_failed INT NOT NULL DEFAULT 0,
        status VARCHAR(50) NOT NULL,
        error_message TEXT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    // Exécution des requêtes dans le bon ordre (catégories d'abord, puis produits, puis images)
    $success = true;
    
    // Créer d'abord la table des catégories
    if (!$conn->query($categoryTable)) {
        error_log("Error creating categories table: " . $conn->error);
        $success = false;
    }
    
    // Ensuite créer la table des produits
    if (!$conn->query($productTable)) {
        error_log("Error creating products table: " . $conn->error);
        $success = false;
    }
    
    // Vérifier si la colonne categoryId existe déjà dans la table products
    $checkColumn = $conn->query("SHOW COLUMNS FROM products LIKE 'categoryId'");
    if ($checkColumn->num_rows === 0) {
        // La colonne n'existe pas, l'ajouter
        $alterTable = "ALTER TABLE products ADD COLUMN categoryId INT, ADD FOREIGN KEY (categoryId) REFERENCES categories(id) ON DELETE SET NULL";
        if (!$conn->query($alterTable)) {
            error_log("Error adding categoryId column: " . $conn->error);
            $success = false;
        }
    }
    
    if (!$conn->query($imageTable)) {
        error_log("Error creating product_images table: " . $conn->error);
        $success = false;
    }
    
    if (!$conn->query($syncTable)) {
        error_log("Error creating sync_logs table: " . $conn->error);
        $success = false;
    }
    
    return $success;
}

/**
 * Fonction pour insérer ou mettre à jour un produit dans la base de données
 * @param mysqli $conn Connexion à la base de données
 * @param array $product Données du produit
 * @return bool Succès de l'opération
 */
function saveProduct($conn, $product) {
    // Gestion de la catégorie et récupération de son ID
    $categoryId = null;
    if (!empty($product['category'])) {
        // Vérifier si la catégorie existe déjà
        $categoryStmt = $conn->prepare("SELECT id FROM categories WHERE name = ? LIMIT 1");
        if ($categoryStmt) {
            $categoryStmt->bind_param("s", $product['category']);
            $categoryStmt->execute();
            $categoryResult = $categoryStmt->get_result();
            
            if ($categoryResult->num_rows > 0) {
                // La catégorie existe, récupérer son ID
                $categoryRow = $categoryResult->fetch_assoc();
                $categoryId = $categoryRow['id'];
            } else {
                // La catégorie n'existe pas, l'insérer
                $insertCatStmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
                if ($insertCatStmt) {
                    $insertCatStmt->bind_param("s", $product['category']);
                    $insertCatStmt->execute();
                    $categoryId = $conn->insert_id;
                    $insertCatStmt->close();
                }
            }
            $categoryStmt->close();
        }
    }
    
    // Préparation de la requête d'insertion/mise à jour avec categoryId
    $stmt = $conn->prepare("INSERT INTO products (id, name, description, image, price, category, stock, categoryId) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?) 
                          ON DUPLICATE KEY UPDATE 
                          name = VALUES(name), 
                          description = VALUES(description), 
                          image = VALUES(image), 
                          price = VALUES(price), 
                          category = VALUES(category), 
                          stock = VALUES(stock), 
                          categoryId = VALUES(categoryId)");
    
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }
    
    // Liaison des paramètres
    $stmt->bind_param(
        "sssdssis",
        $product['id'],
        $product['name'],
        $product['description'],
        $product['image'],
        $product['price'],
        $product['category'],
        $product['stock'],
        $categoryId
    );
    
    // Exécution de la requête
    $result = $stmt->execute();
    
    if (!$result) {
        error_log("Error saving product {$product['id']}: " . $stmt->error);
    }
    
    $stmt->close();
    
    // Si le produit a été inséré/mis à jour avec succès, gérer les images
    if ($result && isset($product['images']) && is_array($product['images'])) {
        // Supprimer les anciennes images
        $deleteImagesStmt = $conn->prepare("DELETE FROM product_images WHERE product_id = ?");
        if ($deleteImagesStmt) {
            $deleteImagesStmt->bind_param("s", $product['id']);
            $deleteImagesStmt->execute();
            $deleteImagesStmt->close();
        }
        
        // Insérer les nouvelles images
        $insertImageStmt = $conn->prepare("INSERT INTO product_images (product_id, image_url, is_main) VALUES (?, ?, ?)");
        if ($insertImageStmt) {
            foreach ($product['images'] as $index => $imageUrl) {
                $isMain = ($index === 0 || $imageUrl === $product['image']) ? 1 : 0;
                $insertImageStmt->bind_param("ssi", $product['id'], $imageUrl, $isMain);
                $insertImageStmt->execute();
            }
            $insertImageStmt->close();
        }
    } elseif ($result && !empty($product['image'])) {
        // Si pas de tableau d'images mais une image principale existe
        $deleteImagesStmt = $conn->prepare("DELETE FROM product_images WHERE product_id = ?");
        if ($deleteImagesStmt) {
            $deleteImagesStmt->bind_param("s", $product['id']);
            $deleteImagesStmt->execute();
            $deleteImagesStmt->close();
        }
        
        $insertImageStmt = $conn->prepare("INSERT INTO product_images (product_id, image_url, is_main) VALUES (?, ?, 1)");
        if ($insertImageStmt) {
            $insertImageStmt->bind_param("ss", $product['id'], $product['image']);
            $insertImageStmt->execute();
            $insertImageStmt->close();
        }
    }
    
    return $result;
}

/**
 * Fonction pour enregistrer une entrée dans le journal de synchronisation
 * @param mysqli $conn Connexion à la base de données
 * @param array $stats Statistiques de synchronisation
 * @return bool Succès de l'opération
 */
function logSync($conn, $stats) {
    $stmt = $conn->prepare("INSERT INTO sync_logs (products_added, products_updated, products_failed, status, error_message) 
                          VALUES (?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        error_log("Prepare failed for sync log: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param(
        "iiiss",
        $stats['added'],
        $stats['updated'],
        $stats['failed'],
        $stats['status'],
        $stats['error_message']
    );
    
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

/**
 * Fonction principale pour synchroniser les produits avec la base de données
 * @param int $page Numéro de page à synchroniser
 * @param int $limit Nombre de produits par page
 * @param bool $allPages Synchroniser toutes les pages disponibles
 * @return array Statistiques de synchronisation
 */
function syncProducts($page = 1, $limit = 50, $allPages = false) {
    // Statistiques de synchronisation
    $stats = [
        'added' => 0,
        'updated' => 0,
        'failed' => 0,
        'status' => 'success',
        'error_message' => ''
    ];
    
    // Connexion à la base de données
    $conn = connectDB();
    if (!$conn) {
        return [
            'added' => 0,
            'updated' => 0,
            'failed' => 0,
            'status' => 'error',
            'error_message' => 'Échec de connexion à la base de données'
        ];
    }
    
    // Création des tables si nécessaire
    if (!createTables($conn)) {
        $conn->close();
        return [
            'added' => 0,
            'updated' => 0,
            'failed' => 0,
            'status' => 'error',
            'error_message' => 'Échec de création des tables'
        ];
    }
    
    try {
        // Récupération des produits depuis l'API
        $apiResponse = getProductsFromAPI($page, $limit);
        
        // Vérification des erreurs
        if (isset($apiResponse['error'])) {
            throw new Exception($apiResponse['error']);
        }
        
        $products = $apiResponse['products'] ?? [];
        $totalPages = ceil(($apiResponse['total'] ?? 0) / $limit);
        
        // Fonction pour récupérer les images d'un produit
        function getProductImages($productId) {
            // Récupérer les détails du produit pour obtenir toutes les images
            $productDetails = getProductById($productId);
            $images = [];
            
            if ($productDetails && isset($productDetails['productImageSet']) && is_array($productDetails['productImageSet'])) {
                foreach ($productDetails['productImageSet'] as $image) {
                    if (!empty($image)) {
                        $images[] = $image;
                    }
                }
            }
            
            return $images;
        }
        
        // Traitement des produits
        foreach ($products as $apiProduct) {
            // Récupérer les images du produit
            $productImages = getProductImages($apiProduct['pid']);
            
            // Formatage du produit pour la base de données
            $product = [
                'id' => $apiProduct['pid'],
                'name' => $apiProduct['productNameEn'],
                'description' => $apiProduct['description'] ?? 'Aucune description disponible',
                'image' => $apiProduct['productImage'] ?? '',
                'images' => $productImages, // Ajout du tableau d'images
                'price' => floatval($apiProduct['sellPrice'] ?? 0),
                'category' => $apiProduct['categoryName'] ?? 'Non catégorisé',
                'stock' => intval($apiProduct['inventory'] ?? 0)
            ];
            
            // Si aucune image n'a été trouvée mais qu'il y a une image principale
            if (empty($product['images']) && !empty($product['image'])) {
                $product['images'] = [$product['image']];
            }
            
            // Sauvegarde du produit
            $result = saveProduct($conn, $product);
            
            if ($result) {
                // Vérifier si c'est une insertion ou une mise à jour
                if ($conn->affected_rows > 0) {
                    $stats['added']++;
                } else {
                    $stats['updated']++;
                }
            } else {
                $stats['failed']++;
            }
        }
        
        // Si allPages est activé et qu'il y a d'autres pages, continuer la synchronisation
        if ($allPages && $page < $totalPages) {
            $nextPageStats = syncProducts($page + 1, $limit, true);
            
            // Fusion des statistiques
            $stats['added'] += $nextPageStats['added'];
            $stats['updated'] += $nextPageStats['updated'];
            $stats['failed'] += $nextPageStats['failed'];
            
            if ($nextPageStats['status'] === 'error') {
                $stats['status'] = 'partial_error';
                $stats['error_message'] = 'Erreurs sur les pages suivantes: ' . $nextPageStats['error_message'];
            }
        }
        
    } catch (Exception $e) {
        $stats['status'] = 'error';
        $stats['error_message'] = $e->getMessage();
        error_log("Sync Error: " . $e->getMessage());
    }
    
    // Enregistrement du journal de synchronisation
    logSync($conn, $stats);
    
    // Fermeture de la connexion
    $conn->close();
    
    return $stats;
}

// Si le script est exécuté directement (et non inclus)
if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    // Vérifier si une synchronisation complète est demandée
    $allPages = isset($_GET['all']) && $_GET['all'] === '1';
    
    // Exécuter la synchronisation
    $stats = syncProducts(1, 50, $allPages);
    
    // Afficher les résultats si en mode CLI ou si demandé
    if (php_sapi_name() === 'cli' || isset($_GET['debug'])) {
        echo "=== Résultats de la synchronisation ===\n";
        echo "Produits ajoutés: {$stats['added']}\n";
        echo "Produits mis à jour: {$stats['updated']}\n";
        echo "Produits en échec: {$stats['failed']}\n";
        echo "Statut: {$stats['status']}\n";
        
        if (!empty($stats['error_message'])) {
            echo "Message d'erreur: {$stats['error_message']}\n";
        }
    } else {
        // Retourner les résultats en JSON pour les appels AJAX
        header('Content-Type: application/json');
        echo json_encode($stats);
    }
}
?>