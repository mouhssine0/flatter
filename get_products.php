<?php
header('Content-Type: application/json');

require_once 'includes/config.php';
require_once 'includes/api.php';
require_once 'includes/functions.php';

// Récupération des paramètres de pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 12;

// Validation des paramètres
$page = max(1, $page); // Page minimum = 1
$limit = max(1, min(200, $limit)); // Limite entre 1 et 200

// Récupération des produits
$result = getProducts($page, $limit);

// Préparation de la réponse
$response = [
    'success' => !isset($result['error']),
    'data' => [
        'products' => $result['products'],
        'pagination' => [
            'total' => $result['total'],
            'currentPage' => $result['currentPage'],
            'totalPages' => $result['totalPages'],
            'limit' => $limit
        ]
    ]
];

// Ajout du message d'erreur si présent
if (isset($result['error'])) {
    $response['error'] = $result['error'];
}

// Envoi de la réponse
echo json_encode($response);