<?php
session_start();
require_once '../db/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

// Récupérer les demandes en attente
$pendingAccounts = $pdo->query("
    SELECT COUNT(*) FROM compte WHERE statut = 'en_attente'
")->fetchColumn();

// Récupérer les statistiques
$stats = [
    'totalClients' => $pdo->query("SELECT COUNT(*) FROM client")->fetchColumn(),
    'totalComptes' => $pdo->query("SELECT COUNT(*) FROM compte")->fetchColumn(),
    'totalEmployes' => $pdo->query("SELECT COUNT(*) FROM employe")->fetchColumn(),
    'operationsToday' => $pdo->query("SELECT COUNT(*) FROM operation WHERE DATE(dateOperation) = CURDATE()")->fetchColumn()
];

// Déterminer la page à charger
$page = $_GET['page'] ?? 'dashboard';

function loadAdminContent($page, $data = []) {
    // Rendre les variables disponibles pour les vues
    extract($data);
    
    // Mapping des pages admin
    $adminPages = [
        'dashboard' => 'main.php',
        'clients' => 'clients.php',
        'comptes' => 'compte.php',
        'employes' => 'employes.php',
        'rapports' => 'rapports.php',
        'nouveauclient' =>'nouveau_client.php',
        'nouveaucompte' =>'nouveau_compte.php',
        'nouveauemploye' =>'employes.php',
        'parametres' => 'parametres.php',
        'rapport'=>'rapports.php'
    ];
    
    // Vérifier si la page existe, sinon charger le dashboard
    $viewFile = $adminPages[$page] ?? 'main.php';
    
    // Inclure le fichier de vue
    if (file_exists($viewFile)) {
        include $viewFile;
    } else {
        include '../views/admin/dashboard.php';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Admin - <?= ucfirst($page) ?></title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/style.css">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="<?=  htmlspecialchars($_SESSION['user']['photo']) ?>" alt="Photo profil" class="profile-img">
                <div class="profile-info">
                    <h3><?= htmlspecialchars($_SESSION['user']['prenom'] . ' ' . htmlspecialchars($_SESSION['user']['nom'])) ?></h3>
                    <p>Administrateur</p>
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
                    <li class="<?= $page == 'clients' ? 'active' : '' ?>">
                        <a href="?page=clients">
                            <i class='bx bx-group'></i>
                            <span>Gestion Clients</span>
                        </a>
                    </li>
                    <li class="<?= $page == 'comptes' ? 'active' : '' ?>">
                        <a href="?page=comptes">
                            <i class='bx bx-wallet'></i>
                            <span>Gestion Comptes </span>
                            <?php if ($pendingAccounts > 0): ?>
                                <span class="notification-bubble"> (<?= $pendingAccounts ?>)</span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="<?= $page == 'employes' ? 'active' : '' ?>">
                        <a href="?page=employes">
                            <i class='bx bx-user'></i>
                            <span>Gestion Employés</span>
                        </a>
                    </li>
                    <li class="<?= $page == 'rapports' ? 'active' : '' ?>">
                        <a href="?page=rapports">
                            <i class='bx bx-bar-chart'></i>
                            <span>Rapports</span>
                        </a>
                    </li>
                    <li class="<?= $page == 'parametres' ? 'active' : '' ?>">
                        <a href="?page=parametres">
                            <i class='bx bx-cog'></i>
                            <span>Paramètres</span>
                        </a>
                    </li>
                    <li>
                        <a href="../logout.php" id="logout-link">
                            <i class='bx bx-log-out'></i>
                            <span>Déconnexion</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>
        
        <!-- Contenu principal -->
        <main class="main-content">
            <header class="main-header">
                <div class="header-left">
                    <h1><?= htmlspecialchars(ucfirst($page)) ?></h1>
                    <p><?= htmlspecialchars(ucfirst($page)) ?></p>
                </div>
                <div class="header-right">
                    <div class="notification-bell" id="notificationBell">
                        <i class='bx bx-bell'></i>
                        <?php if ($pendingAccounts > 0): ?>
                            <span class="notification-count"><?= $pendingAccounts ?></span>
                        <?php endif; ?>
                        <div class="notification-dropdown" id="notificationDropdown">
                            <div class="notification-header">
                                <h4>Notifications</h4>
                            </div>
                            <div class="notification-list">
                                <?php if ($pendingAccounts > 0): ?>
                                    <a href="?page=comptes&filter=en_attente" class="notification-item">
                                        <i class='bx bx-wallet'></i>
                                        <div>
                                            <p><?= $pendingAccounts ?> compte(s) en attente de validation</p>
                                            <small>Cliquez pour voir</small>
                                        </div>
                                    </a>
                                <?php else: ?>
                                    <div class="notification-empty">
                                        <p>Aucune nouvelle notification</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Chargement du contenu dynamique -->
            <div class="content-wrapper">
                <?php loadAdminContent($page, $stats); ?>
            </div>
        </main>
    </div>

    <script>
    // Gestion des notifications
    document.getElementById('notificationBell').addEventListener('click', function(e) {
        e.stopPropagation();
        document.getElementById('notificationDropdown').classList.toggle('show');
    });

    // Fermer le dropdown quand on clique ailleurs
    window.addEventListener('click', function() {
        document.getElementById('notificationDropdown').classList.remove('show');
    });

    // Confirmation de déconnexion
    document.getElementById('logout-link').addEventListener('click', function(e) {
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

    // Validation des comptes (exemple pour comptes.php)
    function validateAccount(accountId) {
        Swal.fire({
            title: 'Valider ce compte ?',
            text: "Cette action ne peut pas être annulée !",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oui, valider'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('actions/validate_account.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ accountId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire(
                            'Validé !',
                            'Le compte a été activé avec succès.',
                            'success'
                        ).then(() => location.reload());
                    } else {
                        Swal.fire(
                            'Erreur !',
                            data.message || 'Une erreur est survenue',
                            'error'
                        );
                    }
                });
            }
        });
    }

    // Rejet des comptes (exemple pour comptes.php)
    function rejectAccount(accountId) {
        Swal.fire({
            title: 'Raison du rejet',
            input: 'text',
            inputLabel: 'Veuillez indiquer la raison du rejet',
            inputPlaceholder: 'Motif du rejet...',
            showCancelButton: true,
            confirmButtonText: 'Confirmer le rejet',
            cancelButtonText: 'Annuler',
            inputValidator: (value) => {
                if (!value) {
                    return 'Vous devez indiquer une raison !';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('actions/reject_account.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ 
                        accountId,
                        reason: result.value 
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire(
                            'Rejeté !',
                            'Le compte a été rejeté avec succès.',
                            'success'
                        ).then(() => location.reload());
                    } else {
                        Swal.fire(
                            'Erreur !',
                            data.message || 'Une erreur est survenue',
                            'error'
                        );
                    }
                });
            }
        });
    }
    </script>
</body>
</html>