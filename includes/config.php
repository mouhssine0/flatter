<?php
// Fichier de configuration pour l'API CJdropshipping

// Informations d'authentification pour l'API
define('CJ_API_KEY', 'a24663ec67204321ad4c0001a067e0cc'); // Remplacez par votre clé API CJdropshipping
define('CJ_EMAIL', 'anastifen@gmail.com'); // Remplacez par votre email CJdropshipping

// Configuration de base de données (si nécessaire)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'flatter_db');

// Configuration du site
define('SITE_URL', 'http://localhost/flatter');
define('SITE_NAME', 'Flatter - Dropshipping');

// Fuseau horaire
date_default_timezone_set('Europe/Paris');

// Activation du mode débogage (à désactiver en production)
define('DEBUG_MODE', true);

// Affichage des erreurs en mode débogage
if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}
?>