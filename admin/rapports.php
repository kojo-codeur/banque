<?php
include '../db/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

// Récupération des données pour les rapports
$transactions_last_month = $pdo->query("
    SELECT COUNT(*) as count, SUM(montant) as total FROM operation WHERE dateOperation >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
")->fetch();

$new_clients_last_month = $pdo->query("
    SELECT COUNT(*) as count FROM client
")->fetchColumn();

$active_clients = $pdo->query("
    SELECT COUNT(DISTINCT idcompteDebiteur) as count FROM operation WHERE dateOperation >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
")->fetchColumn();

// Préparation des données pour le graphique
$monthly_transactions = $pdo->query("
    SELECT 
        DATE_FORMAT(dateOperation, '%Y-%m') as month, COUNT(*) as count, SUM(montant) as amount FROM operation
    WHERE dateOperation >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) GROUP BY DATE_FORMAT(dateOperation, '%Y-%m') ORDER BY month
")->fetchAll();
?>
    <div class="header-right">
        <div class="date-filter">
            <select id="report-period" class="form-control">
                <option value="7">7 derniers jours</option>
                <option value="30" selected>30 derniers jours</option>
                <option value="90">3 derniers mois</option>
                <option value="365">1 an</option>
            </select>
        </div>
    </div>
</header>

<div class="card">
    <div class="card-header">
        <h2>Aperçu des performances</h2>
    </div>
    <div class="card-body">
        <div class="stats-grid">
            <div class="stat-item">
                <i class='bx bx-transfer'></i>
                <h3>Transactions (30j)</h3>
                <p class="stat-value"><?= number_format($transactions_last_month['count']) ?></p>
                <p class="stat-amount"><?= number_format($transactions_last_month['total'], 0, ',', ' ') ?> FBU</p>
            </div>
            <div class="stat-item">
                <i class='bx bx-user-plus'></i>
                <h3>Nouveaux clients (30j)</h3>
                <p class="stat-value"><?= number_format($new_clients_last_month) ?></p>
            </div>
            <div class="stat-item">
                <i class='bx bx-group'></i>
                <h3>Clients qui ont effectuer les operations (30j)</h3>
                <p class="stat-value"><?= number_format($active_clients) ?></p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Évolution des transactions</h2>
    </div>
    <div class="card-body">
        <canvas id="transactionsChart" height="300"></canvas>
    </div>
</div>


<script>
// Graphique des transactions
const ctx = document.getElementById('transactionsChart').getContext('2d');
const transactionsChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: [<?php foreach ($monthly_transactions as $mt) echo "'" . $mt['month'] . "', "; ?>],
        datasets: [{
            label: 'Nombre de transactions',
            data: [<?php foreach ($monthly_transactions as $mt) echo $mt['count'] . ", "; ?>],
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 2,
            tension: 0.1
        }, {
            label: 'Montant total (FBU)',
            data: [<?php foreach ($monthly_transactions as $mt) echo $mt['amount'] . ", "; ?>],
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 2,
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label.includes('Montant')) {
                            return label + ': ' + context.raw.toLocaleString() + ' FBU';
                        }
                        return label + ': ' + context.raw;
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>
