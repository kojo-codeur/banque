<?php
session_start();
require_once '../db/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'client') {
    header("Location: ../index.php");
    exit();
}

// Récupérer les opérations du client
$stmt = $pdo->prepare("SELECT o.*, 
                      (SELECT numeroCompte FROM compte WHERE idCompte = o.idCompteDebiteur) as compteDebiteur,
                      (SELECT numeroCompte FROM compte WHERE idCompte = o.idCompteCrediteur) as compteCrediteur
                      FROM operation o
                      JOIN compte c ON (o.idCompteDebiteur = c.idCompte OR o.idCompteCrediteur = c.idCompte)
                      WHERE c.idClient = ?
                      ORDER BY o.dateOperation DESC
                      LIMIT 5");
$stmt->execute([$_SESSION['user']['id']]);
$operations = $stmt->fetchAll();

// Récupérer les notifications (virements reçus uniquement)
$stmt = $pdo->prepare("SELECT 
    o.idOperation, o.montant, o.dateOperation, o.motif,
    (SELECT numeroCompte FROM compte WHERE idCompte = o.idCompteDebiteur) as compte_emetteur,
    (SELECT CONCAT(prenom, ' ', nom) FROM client WHERE idClient = 
        (SELECT idClient FROM compte WHERE idCompte = o.idCompteDebiteur)) as nom_emetteur,
    o.is_read
FROM operation o
WHERE 
    o.idCompteCrediteur IN (SELECT idCompte FROM compte WHERE idClient = ?)
AND o.typeOperation = 'Virement'
AND o.dateOperation > DATE_SUB(NOW(), INTERVAL 7 DAY)
ORDER BY o.dateOperation DESC
LIMIT 10");
$stmt->execute([$_SESSION['user']['id']]);
$notifications = $stmt->fetchAll();

// Compter les notifications non lues
$unread_count = 0;
foreach ($notifications as $notif) {
    if (!$notif['is_read']) {
        $unread_count++;
    }
}

// Initialiser les variables si null
if (!$operations) $operations = [];
if (!$notifications) $notifications = [];

// Déterminer quelle page afficher
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

function loadContent($page, $operations = []) {
    // Rendre les variables disponibles pour les vues incluses
    $user = $_SESSION['user'];
    $pdo = $GLOBALS['pdo'];
    
    switch($page) {
        case 'virements':
            include 'virements.php';
            break;
        case 'contact':
            include 'contact.php';
            break;
        case 'historique':
            include 'historique.php';
            break;
        case 'parametres':
            include 'parametres.php';
            break;
        case 'compte':
            include 'compte.php';
            break;
        default:
            include 'main.php';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Client - <?= htmlspecialchars(ucfirst($page)) ?></title>
    <link rel="stylesheet" href="../css/dashboard.css">
     <link rel="stylesheet" href="../css/style.css">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <!-- SweetAlert pour les jolies alertes -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="<?= htmlspecialchars($_SESSION['user']['photo']) ?>" 
                     alt="Photo profil" class="profile-img">
                <div class="profile-info">
                    <h3><?= htmlspecialchars($_SESSION['user']['prenom'] . ' ' . $_SESSION['user']['nom']) ?></h3>
                    <p>Client</p>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li class="<?= $page == 'dashboard' ? 'active' : '' ?>">
                        <a href="?page=dashboard">
                            <i class='bx bxs-dashboard'></i>
                            <span>Tableau de bord</span>
                        </a>
                    </li>
                    <li class="<?= $page == 'compte' ? 'active' : '' ?>">
                        <a href="?page=compte">
                            <i class='bx bx-wallet'></i>
                            <span>Mon compte</span>
                        </a>
                    </li>
                    <li class="<?= $page == 'virements' ? 'active' : '' ?>">
                        <a href="?page=virements">
                            <i class='bx bx-transfer'></i>
                            <span>Virements</span>
                        </a>
                    </li>
                    <li class="<?= $page == 'historique' ? 'active' : '' ?>">
                        <a href="?page=historique">
                            <i class='bx bx-history'></i>
                            <span>Historique</span>
                        </a>
                    </li>
                    <li class="<?= $page == 'parametres' ? 'active' : '' ?>">
                        <a href="?page=parametres">
                            <i class='bx bx-cog'></i>
                            <span>Paramètres</span>
                        </a>
                    </li>
                    <li>
                        <a href="../logout.php" id="logout-link" class="logout">
                            <i class='bx bx-log-out'></i>
                            <span>Déconnexion</span>
                        </a> 
                    </li>
                </ul>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Header avec notifications -->
            <header class="main-header">
                <div class="header-left">
                    <h1><?= htmlspecialchars(ucfirst($page)) ?></h1>
                    <p>Bienvenue sur votre espace client</p>
                </div>
                <div class="header-right">
                    <div class="notification-bell" id="notificationBell">
                        <i class='bx bx-bell'></i>
                        <?php if ($unread_count > 0): ?>
                            <span class="notification-count"><?= $unread_count ?></span>
                        <?php endif; ?>
                        <div class="notification-dropdown" id="notificationDropdown">
                            <?php if (!empty($notifications)): ?>
                                <?php foreach ($notifications as $notif): ?>
                                    <div class="notification-item <?= !$notif['is_read'] ? 'unread' : '' ?>">
                                        <div class="notification-icon">
                                            <i class='bx bx-transfer-alt'></i>
                                        </div>
                                        <div class="notification-content">
                                            <p class="notification-title">Nouveau virement reçu</p>
                                            <p class="notification-text">
                                                <?= number_format($notif['montant'], 2, ',', ' ') ?> FBU 
                                                de <?= htmlspecialchars($notif['compte_emetteur']) ?>
                                                (<?= htmlspecialchars($notif['nom_emetteur']) ?>)
                                            </p>
                                            <?php if (!empty($notif['motif'])): ?>
                                                <p class="notification-motive">Motif: <?= htmlspecialchars($notif['motif']) ?></p>
                                            <?php endif; ?>
                                            <p class="notification-time">
                                                <?= date('d/m/Y H:i', strtotime($notif['dateOperation'])) ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="notification-empty">
                                    <p>Aucune notification</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </header>

            <?php loadContent($page, $operations); ?>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const logoutLink = document.getElementById('logout-link');
            
            if (logoutLink) {
                logoutLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    Swal.fire({
                        title: 'Déconnexion',
                        text: 'Êtes-vous sûr de vouloir vous déconnecter ?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Oui, déconnecter',
                        cancelButtonText: 'Annuler'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = this.href;
                        }
                    });
                });
            }

            // Gestion des notifications
            const bell = document.getElementById('notificationBell');
            const dropdown = document.getElementById('notificationDropdown');
            
            // Toggle dropdown
            if (bell && dropdown) {
                bell.addEventListener('click', function(e) {
                    e.stopPropagation();
                    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
                    
                    // Marquer comme lues si il y a des notifications non lues
                    if (document.querySelector('.notification-count')) {
                        fetch('mark_notifications_read.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Retirer le badge
                                const badge = document.querySelector('.notification-count');
                                if (badge) badge.remove();
                                
                                // Retirer la classe unread
                                document.querySelectorAll('.notification-item.unread').forEach(item => {
                                    item.classList.remove('unread');
                                });
                            }
                        });
                    }
                });
                
                // Fermer le dropdown quand on clique ailleurs
                document.addEventListener('click', function() {
                    dropdown.style.display = 'none';
                });
            }
        });
    </script>
</body>
</html>