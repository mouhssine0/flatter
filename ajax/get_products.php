<?php
// Fichier pour récupérer les produits via AJAX
header('Content-Type: application/json');

// Inclusion des fichiers nécessaires
require_once '../includes/config.php';
require_once '../includes/api.php';
require_once '../includes/functions.php';

// Récupération des paramètres
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 12;

// Récupération des produits
$products = getProducts($page, $limit);

// Envoi de la réponse
echo json_encode([
    'success' => true,
    'products' => $products,
    'page' => $page,
    'limit' => $limit
]);
?>