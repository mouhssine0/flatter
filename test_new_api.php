<?php
// Script de test pour le fichier API existant
require_once 'includes/api.php';

// Tester l'obtention du jeton d'accès
echo "<h2>Test d'obtention du jeton d'accès</h2>";
$token = getAccessToken();
echo "Jeton d'accès: " . ($token ? $token : 'Échec de l\'obtention du jeton') . "<br><br>";

// Tester la récupération des produits
echo "<h2>Test de récupération des produits</h2>";
$products = getProductsFromAPI(1, 5);
echo "Nombre de produits récupérés: " . count($products) . "<br><br>";

if (!empty($products)) {
    echo "<h3>Premier produit:</h3>";
    echo "<pre>";
    print_r($products[0]);
    echo "</pre>";
}

// Tester la recherche de produits
echo "<h2>Test de recherche de produits</h2>";
$searchResults = searchProducts('shirt', 1, 5);
echo "Nombre de résultats de recherche: " . count($searchResults) . "<br><br>";

if (!empty($searchResults)) {
    echo "<h3>Premier résultat:</h3>";
    echo "<pre>";
    print_r($searchResults[0]);
    echo "</pre>";
}
?>