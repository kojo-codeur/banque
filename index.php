<?php
session_start();
require_once 'db/db.php';

if (isset($_POST['login'])) {
    $identifiant = trim($_POST['identifiant']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? 1 : 0;

    try {
        $stmt = $pdo->prepare("SELECT c.idClient, c.nom, c.prenom, c.email, c.password, c.photoPasseport, c.telephone,
                      co.idCompte, co.numeroCompte, co.typeCompte, co.solde 
                      FROM client c 
                      JOIN compte co ON c.idClient = co.idClient 
                      WHERE co.numeroCompte = ?");
        $stmt->execute([$identifiant]);
        $user = $stmt->fetch();

        if ($user) {
            // Vérifier le mot de passe
            if (password_verify($password, $user['password'])) {
                $_SESSION['user'] = [
                    'id' => $user['idClient'],
                    'numeroCompte' => $user['numeroCompte'],
                    'nom' => $user['nom'],
                    'prenom' => $user['prenom'],
                    'email' => $user['email'],
                    'role' => 'client',
                    'photo' => $user['photoPasseport'],
                    'telephone' => $user['telephone'],
                    'solde' => $user['solde'],
                    'typeCompte' => $user['typeCompte']
                ];
                
                header("Location: client/dashboard.php");
                exit();
            } else {
                $_SESSION['error'] = "Mot de passe incorrect";
                header("Location: index.php");
                exit();
            }
        } else {
            // 2. Si ce n'est pas un client, vérifier si c'est un employé
            $stmt = $pdo->prepare("SELECT * FROM employe WHERE matricule = ? OR email = ?");
            $stmt->execute([$identifiant, $identifiant]);
            $staff = $stmt->fetch();

            if ($staff) {
                if (password_verify($password, $staff['password'])) {
                    if (!$staff['actif']) {
                        $_SESSION['error'] = "Votre compte est désactivé";
                        header("Location: index.php");
                        exit();
                    }

                    $_SESSION['user'] = [
                        'id' => $staff['id_employe'],
                        'matricule' => $staff['matricule'],
                        'nom' => $staff['nom'],
                        'prenom' => $staff['prenom'],
                        'email' => $staff['email'],
                        'role' => $staff['role'],
                        'photo' => $staff['photo'],
                        'telephone' => $staff['telephone']
                    ];

                    // Redirection selon le rôle
                    if ($staff['role'] == 'admin') {
                        header("Location: admin/dashboard.php");
                    } else {
                        header("Location: agent/dashboard.php");
                    }
                    exit();
                } else {
                    $_SESSION['error'] = "Mot de passe incorrect";
                    header("Location: index.php");
                    exit();
                }
            } else {
                $_SESSION['error'] = "Identifiant incorrect";
                header("Location: index.php");
                exit();
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de connexion: " . $e->getMessage();
        header("Location: index.php");
        exit();
    }
}

if (isset($_POST['reset_password'])) {
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if (strlen($new_password) < 8) {
        $_SESSION['error'] = "Le mot de passe doit contenir au moins 8 caractères";
        header("Location: index.php?form=forgot");
        exit();
    }

    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = "Les mots de passe ne correspondent pas";
        header("Location: index.php?form=forgot");
        exit();
    }

    try {
        // 1. D'abord vérifier dans la table client
        $stmt = $pdo->prepare("SELECT idClient FROM client WHERE email = ? AND telephone = ?");
        $stmt->execute([$email, $telephone]);
        $client = $stmt->fetch();

        if ($client) {
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $pdo->prepare("UPDATE client SET password = ? WHERE idClient = ?")
               ->execute([$hashed_password, $client['idClient']]);
            
            $_SESSION['success'] = "Mot de passe client réinitialisé avec succès";
            header("Location: index.php");
            exit();
        }

        // 2. Si pas trouvé dans client, vérifier dans employe
        $stmt = $pdo->prepare("SELECT id_employe FROM employe WHERE email = ? AND telephone = ?");
        $stmt->execute([$email, $telephone]);
        $employe = $stmt->fetch();

        if ($employe) {
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $pdo->prepare("UPDATE employe SET password = ? WHERE id_employe = ?")
               ->execute([$hashed_password, $employe['id_employe']]);
            
            $_SESSION['success'] = "Mot de passe employé réinitialisé avec succès";
            header("Location: index.php");
            exit();
        }

        // Si aucun compte trouvé
        $_SESSION['error'] = "Aucun compte trouvé avec ces informations";
        header("Location: index.php?form=forgot");
        exit();

    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la réinitialisation: " . $e->getMessage();
        header("Location: index.php?form=forgot");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Banque Africaine</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="shortcut icon" href="photos/logo.jpg" type="image/x-icon">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <img src="photos/logo.jpg" alt="Logo Banque" class="logo">
            <h1>Banque Africaine</h1>
            <p>Système de gestion bancaire</p>
        </div>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <i class='bx bxs-error-circle'></i>
                <span><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class='bx bxs-check-circle'></i>
                <span><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></span>
            </div>
        <?php endif; ?>

        <form class="login-form" method="POST">
            <div class="form-group">
                <label for="identifiant">Identifiant</label>
                <div class="input-with-icon">
                    <i class='bx bx-user'></i>
                    <input type="text" id="identifiant" name="identifiant" 
                           placeholder="Numéro de compte (client) ou matricule (employé)" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <div class="input-with-icon">
                    <i class='bx bx-lock'></i>
                    <input type="password" id="password" name="password" 
                           placeholder="Votre mot de passe" required>
                </div>
            </div>
            
            <div class="form-options">
                <label class="checkbox-container">
                    <input type="checkbox" name="remember">
                    <span class="checkmark"></span>
                    Se souvenir de moi
                </label>
                <a href="#" class="forgot-password" id="forgotLink">Mot de passe oublié ?</a>
            </div>
            
            <button type="submit" name="login" class="login-button">
                <i class='bx bx-log-in'></i> Se connecter
            </button>
        </form>
        
        <form action="" method="POST" class="auth-form" id="forgotForm" style="display: none;">
            <h3>Réinitialisation du mot de passe</h3>
            <div class="form-group">
                <label for="reset_email">Email</label>
                <div class="input-with-icon">
                    <input type="email" id="reset_email" name="email" 
                       class="form-control" placeholder="Votre email" required>
                </div>
            
                <label for="reset_telephone">Téléphone</label>
                <div class="input-with-icon">
                    <input type="tel" id="reset_telephone" name="telephone" 
                       class="form-control" placeholder="Votre téléphone" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="reset_new_password">Nouveau mot de passe</label>
                <div class="input-with-icon">
                    <input type="password" id="reset_new_password" name="new_password" 
                       class="form-control" placeholder="Minimum 8 caractères" required minlength="8">
                </div>
            
                <label for="reset_confirm_password">Confirmer le mot de passe</label>
                <div class="input-with-icon">
                    <input type="password" id="reset_confirm_password" name="confirm_password" 
                       class="form-control" placeholder="Retapez votre mot de passe" required minlength="8">
                </div>
            </div>
            
            <button type="submit" name="reset_password" class="btn btn-block">
                <i class='bx bx-refresh'></i> Réinitialiser
            </button>
            
            <p class="text-center">
                <a href="#" class="forgot-password" id="backToLogin">Retour à la connexion</a>
            </p>
        </form>
        
        <div class="login-footer">
            <p>© <?= date('Y');?> Banque Africaine. Tous droits réservés.</p>
            <p class="security-info">
                <i class='bx bx-shield'></i> Système sécurisé
            </p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const forgotLink = document.getElementById('forgotLink');
            const backToLogin = document.getElementById('backToLogin');
            const loginForm = document.querySelector('.login-form');
            const forgotForm = document.getElementById('forgotForm');
            
            // Afficher le formulaire de réinitialisation
            forgotLink.addEventListener('click', function(e) {
                e.preventDefault();
                loginForm.style.display = 'none';
                forgotForm.style.display = 'block';
            });
            
            // Revenir au formulaire de connexion
            backToLogin.addEventListener('click', function(e) {
                e.preventDefault();
                forgotForm.style.display = 'none';
                loginForm.style.display = 'block';
            });
            
            // Masquer les messages d'alerte après 5 secondes
            document.querySelectorAll('.alert').forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }, 5000);
            });
        });
    </script>
</body>
</html>