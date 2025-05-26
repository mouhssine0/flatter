<?php
header('Content-Type: application/json');

require_once '../includes/config.php';
require_once '../includes/api.php';
require_once '../includes/functions.php';

$productId = isset($_GET['id']) ? $_GET['id'] : null;

if (!$productId) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de produit manquant'
    ]);
    exit;
}

// Récupérer d'abord le produit actuel pour obtenir sa catégorie
$currentProduct = getProductById($productId);

if (!$currentProduct || !isset($currentProduct['category'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Produit non trouvé ou catégorie non disponible'
    ]);
    exit;
}

// Récupérer tous les produits de la même catégorie
$allProducts = getProducts(1, 8);
$similarProducts = [];

// Filtrer pour obtenir les produits de la même catégorie, en excluant le produit actuel
foreach ($allProducts['products'] as $product) {
    if ($product['category'] === $currentProduct['category'] && $product['id'] !== $productId) {
        $similarProducts[] = $product;
    }
}

// Limiter à 4 produits similaires maximum
$similarProducts = array_slice($similarProducts, 0, 4);

echo json_encode([
    'success' => true,
    'products' => $similarProducts
]);
?>