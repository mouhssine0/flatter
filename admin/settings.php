<?php
// Page de paramètres du site
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth_functions.php';

// Démarrer la session
session_start();

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../login.php");
    exit;
}

// Initialisation des variables
$error = '';
$success = '';
$settings = [];

// Connexion à la base de données
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$conn) {
    $error = "Erreur de connexion à la base de données: " . mysqli_connect_error();
} else {
    // Vérifier si la table settings existe
    $tableExistsQuery = "SHOW TABLES LIKE 'settings'";
    $tableExistsResult = mysqli_query($conn, $tableExistsQuery);
    
    if (mysqli_num_rows($tableExistsResult) == 0) {
        // Créer la table settings si elle n'existe pas
        $createTableQuery = "CREATE TABLE settings (
            id INT(11) NOT NULL AUTO_INCREMENT,
            setting_key VARCHAR(255) NOT NULL,
            setting_value TEXT,
            setting_group VARCHAR(100) NOT NULL DEFAULT 'general',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY (setting_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        if (!mysqli_query($conn, $createTableQuery)) {
            $error = "Erreur lors de la création de la table settings: " . mysqli_error($conn);
        } else {
            // Insérer les paramètres par défaut
            $defaultSettings = [
                ['site_name', 'Flatter', 'general'],
                ['site_description', 'Boutique en ligne de vêtements', 'general'],
                ['contact_email', 'contact@flatter.com', 'general'],
                ['items_per_page', '12', 'general'],
                ['currency', 'EUR', 'general'],
                ['currency_symbol', '€', 'general'],
                ['enable_tax', '1', 'tax'],
                ['tax_rate', '20', 'tax'],
                ['shipping_flat_rate', '5.99', 'shipping'],
                ['free_shipping_threshold', '50', 'shipping'],
                ['enable_paypal', '1', 'payment'],
                ['paypal_email', 'business@flatter.com', 'payment'],
                ['paypal_sandbox', '1', 'payment'],
                ['enable_stripe', '0', 'payment'],
                ['stripe_public_key', '', 'payment'],
                ['stripe_secret_key', '', 'payment'],
                ['enable_order_emails', '1', 'notification'],
                ['admin_order_email', 'orders@flatter.com', 'notification']
            ];
            
            $insertQuery = "INSERT INTO settings (setting_key, setting_value, setting_group) VALUES (?, ?, ?)";
            $insertStmt = mysqli_prepare($conn, $insertQuery);
            
            foreach ($defaultSettings as $setting) {
                mysqli_stmt_bind_param($insertStmt, "sss", $setting[0], $setting[1], $setting[2]);
                mysqli_stmt_execute($insertStmt);
            }
        }
    }
    
    // Traitement de la mise à jour des paramètres
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
        // Récupérer tous les paramètres du formulaire
        $settingsToUpdate = [];
        
        // Paramètres généraux
        if (isset($_POST['general'])) {
            foreach ($_POST['general'] as $key => $value) {
                $settingsToUpdate[] = ['setting_key' => $key, 'setting_value' => $value, 'setting_group' => 'general'];
            }
        }
        
        // Paramètres de taxe
        if (isset($_POST['tax'])) {
            foreach ($_POST['tax'] as $key => $value) {
                $settingsToUpdate[] = ['setting_key' => $key, 'setting_value' => $value, 'setting_group' => 'tax'];
            }
        }
        
        // Paramètres d'expédition
        if (isset($_POST['shipping'])) {
            foreach ($_POST['shipping'] as $key => $value) {
                $settingsToUpdate[] = ['setting_key' => $key, 'setting_value' => $value, 'setting_group' => 'shipping'];
            }
        }
        
        // Paramètres de paiement
        if (isset($_POST['payment'])) {
            foreach ($_POST['payment'] as $key => $value) {
                $settingsToUpdate[] = ['setting_key' => $key, 'setting_value' => $value, 'setting_group' => 'payment'];
            }
        }
        
        // Paramètres de notification
        if (isset($_POST['notification'])) {
            foreach ($_POST['notification'] as $key => $value) {
                $settingsToUpdate[] = ['setting_key' => $key, 'setting_value' => $value, 'setting_group' => 'notification'];
            }
        }
        
        // Mettre à jour les paramètres dans la base de données
        $updateQuery = "INSERT INTO settings (setting_key, setting_value, setting_group) 
                       VALUES (?, ?, ?) 
                       ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
        $updateStmt = mysqli_prepare($conn, $updateQuery);
        
        $updateSuccess = true;
        foreach ($settingsToUpdate as $setting) {
            mysqli_stmt_bind_param($updateStmt, "sss", $setting['setting_key'], $setting['setting_value'], $setting['setting_group']);
            if (!mysqli_stmt_execute($updateStmt)) {
                $updateSuccess = false;
                $error = "Erreur lors de la mise à jour des paramètres: " . mysqli_error($conn);
                break;
            }
        }
        
        if ($updateSuccess) {
            $success = "Paramètres mis à jour avec succès.";
        }
    }
    
    // Récupérer tous les paramètres
    $query = "SELECT setting_key, setting_value, setting_group FROM settings ORDER BY setting_group, setting_key";
    $result = mysqli_query($conn, $query);
    
    // Organiser les paramètres par groupe
    $settings = [
        'general' => [],
        'tax' => [],
        'shipping' => [],
        'payment' => [],
        'notification' => []
    ];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $settings[$row['setting_group']][$row['setting_key']] = $row['setting_value'];
    }
    
    mysqli_close($conn);
}

// Inclusion de l'en-tête d'administration
require_once 'admin_header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-3">
            <!-- Menu latéral d'administration -->
            <div class="list-group">
                <a href="index.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-tachometer-alt me-2"></i>Tableau de bord
                </a>
                <a href="products.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-box me-2"></i>Gestion des produits
                </a>
                <a href="categories.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-tags me-2"></i>Gestion des catégories
                </a>
                <a href="users.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-users me-2"></i>Gestion des utilisateurs
                </a>
                <a href="register.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-user-plus me-2"></i>Ajouter un administrateur
                </a>
                <a href="orders.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-shopping-cart me-2"></i>Gestion des commandes
                </a>
                <a href="settings.php" class="list-group-item list-group-item-action active">
                    <i class="fas fa-cog me-2"></i>Paramètres
                </a>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-cog me-2"></i>Paramètres du site</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form action="" method="POST">
                        <!-- Onglets pour les différentes sections de paramètres -->
                        <ul class="nav nav-tabs mb-3" id="settingsTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab" aria-controls="general" aria-selected="true">
                                    <i class="fas fa-globe me-1"></i>Général
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tax-tab" data-bs-toggle="tab" data-bs-target="#tax" type="button" role="tab" aria-controls="tax" aria-selected="false">
                                    <i class="fas fa-percentage me-1"></i>Taxes
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="shipping-tab" data-bs-toggle="tab" data-bs-target="#shipping" type="button" role="tab" aria-controls="shipping" aria-selected="false">
                                    <i class="fas fa-truck me-1"></i>Expédition
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="payment-tab" data-bs-toggle="tab" data-bs-target="#payment" type="button" role="tab" aria-controls="payment" aria-selected="false">
                                    <i class="fas fa-credit-card me-1"></i>Paiement
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="notification-tab" data-bs-toggle="tab" data-bs-target="#notification" type="button" role="tab" aria-controls="notification" aria-selected="false">
                                    <i class="fas fa-bell me-1"></i>Notifications
                                </button>
                            </li>
                        </ul>
                        
                        <!-- Contenu des onglets -->
                        <div class="tab-content" id="settingsTabsContent">
                            <!-- Paramètres généraux -->
                            <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="site_name" class="form-label">Nom du site</label>
                                        <input type="text" class="form-control" id="site_name" name="general[site_name]" value="<?php echo htmlspecialchars($settings['general']['site_name'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="contact_email" class="form-label">Email de contact</label>
                                        <input type="email" class="form-control" id="contact_email" name="general[contact_email]" value="<?php echo htmlspecialchars($settings['general']['contact_email'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="site_description" class="form-label">Description du site</label>
                                    <textarea class="form-control" id="site_description" name="general[site_description]" rows="2"><?php echo htmlspecialchars($settings['general']['site_description'] ?? ''); ?></textarea>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="items_per_page" class="form-label">Produits par page</label>
                                        <input type="number" class="form-control" id="items_per_page" name="general[items_per_page]" value="<?php echo htmlspecialchars($settings['general']['items_per_page'] ?? '12'); ?>" min="1" max="100" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="currency" class="form-label">Devise</label>
                                        <select class="form-select" id="currency" name="general[currency]">
                                            <option value="EUR" <?php echo ($settings['general']['currency'] ?? '') === 'EUR' ? 'selected' : ''; ?>>Euro (EUR)</option>
                                            <option value="USD" <?php echo ($settings['general']['currency'] ?? '') === 'USD' ? 'selected' : ''; ?>>Dollar US (USD)</option>
                                            <option value="GBP" <?php echo ($settings['general']['currency'] ?? '') === 'GBP' ? 'selected' : ''; ?>>Livre Sterling (GBP)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="currency_symbol" class="form-label">Symbole monétaire</label>
                                        <input type="text" class="form-control" id="currency_symbol" name="general[currency_symbol]" value="<?php echo htmlspecialchars($settings['general']['currency_symbol'] ?? '€'); ?>" maxlength="5" required>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Paramètres de taxe -->
                            <div class="tab-pane fade" id="tax" role="tabpanel" aria-labelledby="tax-tab">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="enable_tax" name="tax[enable_tax]" value="1" <?php echo ($settings['tax']['enable_tax'] ?? '0') == '1' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="enable_tax">Activer les taxes</label>
                                </div>
                                <div class="mb-3">
                                    <label for="tax_rate" class="form-label">Taux de TVA (%)</label>
                                    <input type="number" class="form-control" id="tax_rate" name="tax[tax_rate]" value="<?php echo htmlspecialchars($settings['tax']['tax_rate'] ?? '20'); ?>" min="0" max="100" step="0.01">
                                    <div class="form-text">Entrez le taux de TVA applicable aux produits.</div>
                                </div>
                            </div>
                            
                            <!-- Paramètres d'expédition -->
                            <div class="tab-pane fade" id="shipping" role="tabpanel" aria-labelledby="shipping-tab">
                                <div class="mb-3">
                                    <label for="shipping_flat_rate" class="form-label">Frais d'expédition forfaitaires</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="shipping_flat_rate" name="shipping[shipping_flat_rate]" value="<?php echo htmlspecialchars($settings['shipping']['shipping_flat_rate'] ?? '5.99'); ?>" min="0" step="0.01">
                                        <span class="input-group-text"><?php echo htmlspecialchars($settings['general']['currency_symbol'] ?? '€'); ?></span>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="free_shipping_threshold" class="form-label">Seuil pour la livraison gratuite</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="free_shipping_threshold" name="shipping[free_shipping_threshold]" value="<?php echo htmlspecialchars($settings['shipping']['free_shipping_threshold'] ?? '50'); ?>" min="0" step="0.01">
                                        <span class="input-group-text"><?php echo htmlspecialchars($settings['general']['currency_symbol'] ?? '€'); ?></span>
                                    </div>
                                    <div class="form-text">La livraison sera gratuite pour les commandes dépassant ce montant. Mettre 0 pour désactiver.</div>
                                </div>
                            </div>
                            
                            <!-- Paramètres de paiement -->
                            <div class="tab-pane fade" id="payment" role="tabpanel" aria-labelledby="payment-tab">
                                <!-- PayPal -->
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="enable_paypal" name="payment[enable_paypal]" value="1" <?php echo ($settings['payment']['enable_paypal'] ?? '0') == '1' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="enable_paypal"><i class="fab fa-paypal me-2"></i>PayPal</label>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="paypal_email" class="form-label">Email PayPal</label>
                                            <input type="email" class="form-control" id="paypal_email" name="payment[paypal_email]" value="<?php echo htmlspecialchars($settings['payment']['paypal_email'] ?? ''); ?>">
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="paypal_sandbox" name="payment[paypal_sandbox]" value="1" <?php echo ($settings['payment']['paypal_sandbox'] ?? '0') == '1' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="paypal_sandbox">Mode Sandbox (test)</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Stripe -->
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="enable_stripe" name="payment[enable_stripe]" value="1" <?php echo ($settings['payment']['enable_stripe'] ?? '0') == '1' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="enable_stripe"><i class="fab fa-stripe me-2"></i>Stripe</label>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="stripe_public_key" class="form-label">Clé publique Stripe</label>
                                            <input type="text" class="form-control" id="stripe_public_key" name="payment[stripe_public_key]" value="<?php echo htmlspecialchars($settings['payment']['stripe_public_key'] ?? ''); ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label for="stripe_secret_key" class="form-label">Clé secrète Stripe</label>
                                            <input type="password" class="form-control" id="stripe_secret_key" name="payment[stripe_secret_key]" value="<?php echo htmlspecialchars($settings['payment']['stripe_secret_key'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Paramètres de notification -->
                            <div class="tab-pane fade" id="notification" role="tabpanel" aria-labelledby="notification-tab">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="enable_order_emails" name="notification[enable_order_emails]" value="1" <?php echo ($settings['notification']['enable_order_emails'] ?? '0') == '1' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="enable_order_emails">Envoyer des emails pour les nouvelles commandes</label>
                                </div>
                                <div class="mb-3">
                                    <label for="admin_order_email" class="form-label">Email de notification des commandes (admin)</label>
                                    <input type="email" class="form-control" id="admin_order_email" name="notification[admin_order_email]" value="<?php echo htmlspecialchars($settings['notification']['admin_order_email'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" name="update_settings" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Enregistrer les paramètres
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'admin_footer.php'; ?>