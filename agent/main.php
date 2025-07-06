<?php
include '../db/db.php';

$operationsToday = $pdo->prepare("SELECT COUNT(*) FROM operation 
                                WHERE id_employe = ? 
                                AND DATE(dateOperation) = CURDATE()");
$operationsToday->execute([$employeId]);
$operationsToday = $operationsToday->fetchColumn();

$clientsServed = $pdo->prepare("SELECT COUNT(DISTINCT c.idClient) 
                               FROM compte c 
                               JOIN operation o ON c.idCompte = o.idCompteDebiteur OR c.idCompte = o.idCompteCrediteur 
                               WHERE o.id_employe = ? 
                               AND DATE(o.dateOperation) = CURDATE()");
$clientsServed->execute([$employeId]);
$clientsServed = $clientsServed->fetchColumn();
?>

<header class="main-header">
    <div class="header-left">
        <h1>Tableau de bord</h1>
        <p>Bienvenue, <?= htmlspecialchars($_SESSION['user']['prenom']) ?></p>
    </div>
    <div class="header-right">
        <div class="notification-bell">
            <i class='bx bx-bell'></i>
            <span class="notification-count"><?= $operationsToday ?></span>
        </div>
    </div>
</header>

<div class="content-gri">
    <div class="card stats-card">
        <div class="card-header">
            <h2>Statistiques du jour</h2>
        </div>
        <div class="card-body">
            <div class="stats-grid">
                <div class="stat-item">
                    <i class='bx bx-transfer'></i>
                    <h3>Opérations</h3>
                    <p class="stat-value"><?= $operationsToday ?></p>
                </div>
                <div class="stat-item">
                    <i class='bx bx-group'></i>
                    <h3>Clients servis</h3>
                    <p class="stat-value"><?= $clientsServed ?></p>
                </div>
                <div class="stat-item">
                    <i class='bx bx-wallet'></i>
                    <h3>Total opérations</h3>
                    <p class="stat-value">
                        <?php
                        $stmt = $pdo->prepare("SELECT SUM(montant) FROM operation 
                                              WHERE id_employe = ? 
                                              AND DATE(dateOperation) = CURDATE()");
                        $stmt->execute([$employeId]);
                        echo number_format($stmt->fetchColumn() ?? 0, 2, ',', ' ') . ' FBU';
                        ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card quick-actions-card">
        <div class="card-header">
            <h2>Actions rapides</h2>
        </div>
        <div class="card-body">
            <div class="quick-actions-grid">
                <a href="?page=depot" class="quick-action">
                    <div class="action-icon">
                        <i class='bx bx-money'></i>
                    </div>
                    <span>Dépôt</span>
                </a>
                <a href="?page=retrait" class="quick-action">
                    <div class="action-icon">
                        <i class='bx bx-credit-card'></i>
                    </div>
                    <span>Retrait</span>
                </a>
                <a href="?page=virement" class="quick-action">
                    <div class="action-icon">
                        <i class='bx bx-transfer-alt'></i>
                    </div>
                    <span>Virement</span>
                </a>
            </div>
        </div>
    </div>
</div>