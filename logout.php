<?php
// Page de déconnexion
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth_functions.php';

// Démarrer la session
session_start();

// Déconnecter l'utilisateur
logoutUser();
?>