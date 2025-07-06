<?php
session_start();
require_once '../db/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'agent') {
    header("Location: ../index.php");
    exit();
}

$employeId = $_SESSION['user']['id'];

// Récupérer les infos de l'agent
$stmt = $pdo->prepare("SELECT * FROM employe WHERE id_employe = ?");
$stmt->execute([$employeId]);
$agentInfo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$agentInfo) {
    session_destroy();
    header("Location: ../index.php");
    exit();
}

$_SESSION['user'] = array_merge($_SESSION['user'], $agentInfo);

// Gestion des pages
$page = $_GET['page'] ?? 'dashboard';
$allowedPages = [
    'dashboard' => 'main.php',
    'operations' => 'operations.php',
    'virement' => 'virement.php',
    'depot' => 'depot.php',
    'retrait' => 'retrait.php',
    'historique' => 'historique.php'
];

$contentFile = $allowedPages[$page] ?? 'pages/404.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Agent</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/stye.css">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="<?= htmlspecialchars($_SESSION['user']['photo']) ?>" alt="Photo profil" class="profile-img">
                <div class="profile-info">
                    <h3><?= htmlspecialchars($_SESSION['user']['prenom'] . ' ' . htmlspecialchars($_SESSION['user']['nom'])) ?></h3>
                    <p>Agent bancaire</p>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li class="<?= $page === 'dashboard' ? 'active' : '' ?>">
                        <a href="?page=dashboard">
                            <i class='bx bxs-dashboard'></i>
                            <span>Tableau de bord</span>
                        </a>
                    </li>
                    <li class="<?= $page === 'operations' ? 'active' : '' ?>">
                        <a href="?page=operations">
                            <i class='bx bx-transfer'></i>
                            <span>Opérations</span>
                        </a>
                    </li>
                    <!-- <li class="<?= $page === 'virement' ? 'active' : '' ?>">
                        <a href="?page=virement">
                            <i class='bx bx-transfer-alt'></i>
                            <span>Virement</span>
                        </a>
                    </li>
                    <li class="<?= $page === 'depot' ? 'active' : '' ?>">
                        <a href="?page=depot">
                            <i class='bx bx-money'></i>
                            <span>Dépôt</span>
                        </a>
                    </li>
                    <li class="<?= $page === 'retrait' ? 'active' : '' ?>">
                        <a href="?page=retrait">
                            <i class='bx bx-credit-card'></i>
                            <span>Retrait</span>
                        </a>
                    </li> -->
                    <li class="<?= $page === 'historique' ? 'active' : '' ?>">
                        <a href="?page=historique">
                            <i class='bx bx-history'></i>
                            <span>Historique</span>
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
        
        <!-- Main Content -->
        <main class="main-content">
            <?php include $contentFile; ?>
        </main>
    </div>
</body>
<script>
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
</script>
</html>