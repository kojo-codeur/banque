<?php
session_start();
require_once 'db/db.php';

if (!isset($_SESSION['first_login'])) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['change_password'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (strlen($new_password) < 8) {
        $_SESSION['error'] = "Le mot de passe doit contenir au moins 8 caractères";
    } elseif ($new_password !== $confirm_password) {
        $_SESSION['error'] = "Les mots de passe ne correspondent pas";
    } else {
        try {
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $pdo->prepare("UPDATE client SET password = ? WHERE idClient = ?")
               ->execute([$hashed_password, $_SESSION['first_login']]);
            
            $_SESSION['success'] = "Mot de passe mis à jour avec succès";
            unset($_SESSION['first_login']);
            header("Location: index.php");
            exit();
        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur lors de la mise à jour du mot de passe";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Première connexion</title>
    <link rel="stylesheet" href="css/login.css">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <img src="photos/logo.jpg" alt="Logo Banque" class="logo">
            <h1>Première connexion</h1>
            <p>Veuillez définir votre nouveau mot de passe</p>
        </div>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <i class='bx bxs-error-circle'></i>
                <span><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="login-form">
            <div class="form-group">
                <label for="new_password">Nouveau mot de passe</label>
                <div class="input-with-icon">
                    <i class='bx bx-lock-alt'></i>
                    <input type="password" id="new_password" name="new_password" placeholder="Minimum 8 caractères" required minlength="8">
                </div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirmer le mot de passe</label>
                <div class="input-with-icon">
                    <i class='bx bx-lock'></i>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Retapez votre mot de passe" required minlength="8">
                </div>
            </div>
            
            <button type="submit" name="change_password" class="login-button">
                <i class='bx bx-save'></i> Enregistrer
            </button>
        </form>
        
        <div class="login-footer">
            <p>© 2023 Banque Africaine. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>