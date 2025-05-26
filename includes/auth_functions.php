<?php
// Fonctions d'authentification

/**
 * Vérifie les identifiants de connexion
 * @param string $email Email de l'utilisateur
 * @param string $password Mot de passe en clair
 * @return array|bool Données utilisateur ou false si échec
 */
function loginUser($email, $password) {
    $conn = connectDB();
    if (!$conn) {
        return false;
    }
    
    // Préparer la requête pour éviter les injections SQL
    $stmt = $conn->prepare("SELECT id, email, password, firstname, lastname, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Vérifier le mot de passe
        if (password_verify($password, $user['password'])) {
            // Mettre à jour la date de dernière connexion
            $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $updateStmt->bind_param("i", $user['id']);
            $updateStmt->execute();
            $updateStmt->close();
            
            // Supprimer le mot de passe avant de retourner les données
            unset($user['password']);
            $conn->close();
            return $user;
        }
    }
    
    $stmt->close();
    $conn->close();
    return false;
}

/**
 * Enregistre un nouvel utilisateur
 * @param string $email Email de l'utilisateur
 * @param string $password Mot de passe en clair
 * @param string $firstname Prénom
 * @param string $lastname Nom
 * @param string $role Rôle (user par défaut)
 * @return bool Succès ou échec
 */
function registerUser($email, $password, $firstname, $lastname, $role = 'user') {
    $conn = connectDB();
    if (!$conn) {
        return false;
    }
    
    // Vérifier si l'email existe déjà
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        $checkStmt->close();
        $conn->close();
        return false; // Email déjà utilisé
    }
    $checkStmt->close();
    
    // Hacher le mot de passe
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insérer le nouvel utilisateur
    $stmt = $conn->prepare("INSERT INTO users (email, password, firstname, lastname, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $email, $hashedPassword, $firstname, $lastname, $role);
    $success = $stmt->execute();
    
    $stmt->close();
    $conn->close();
    return $success;
}

/**
 * Vérifie si l'utilisateur est connecté
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Vérifie si l'utilisateur est un administrateur
 * @return bool
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Fonction pour récupérer les informations d'un utilisateur par son ID
function getUserById($id) {
    $conn = connectDB();
    $user = [];
    
    if ($conn) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
        }
        
        $stmt->close();
        $conn->close();
    }
    
    return $user;
}

/**
 * Déconnecte l'utilisateur
 */
function logoutUser() {
    // Détruire toutes les variables de session
    $_SESSION = array();
    
    // Détruire la session
    session_destroy();
    
    // Rediriger vers la page d'accueil
    header("Location: " . SITE_URL);
    exit;
}
?>