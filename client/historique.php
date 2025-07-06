<?php
// Récupérer toutes les opérations du client
$stmt = $pdo->prepare("SELECT o.*, 
                      (SELECT numeroCompte FROM compte WHERE idCompte = o.idCompteDebiteur) as compteDebiteur,
                      (SELECT numeroCompte FROM compte WHERE idCompte = o.idCompteCrediteur) as compteCrediteur
                      FROM operation o
                      JOIN compte c ON (o.idCompteDebiteur = c.idCompte OR o.idCompteCrediteur = c.idCompte)
                      WHERE c.idClient = ?
                      ORDER BY o.dateOperation DESC");
$stmt->execute([$_SESSION['user']['id']]);
$operations = $stmt->fetchAll();
?>
<div>
    <div class="card">
        <div class="card-header">
            <h2>Toutes vos transactions</h2>
            <div class="filters">
                <select id="filter-type" class="form-control">
                    <option value="all">Tous types</option>
                    <option value="Virement">Virements</option>
                    <option value="Depot">Dépôts</option>
                    <option value="Retrait">Retraits</option>
                </select>
                <input type="date" id="filter-date" class="form-control">
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="transactions-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Compte concerné</th>
                            <th>Montant</th>
                            <th>Détails</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($operations as $op): ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($op['dateOperation'])) ?></td>
                            <td><?= htmlspecialchars($op['typeOperation']) ?></td>
                            <td>
                                <?php if ($op['typeOperation'] == 'Virement'): ?>
                                    <?= $op['compteDebiteur'] == $_SESSION['user']['numeroCompte'] ? 
                                        'Vers: ' . $op['compteCrediteur'] : 
                                        'De: ' . $op['compteDebiteur'] ?>
                                <?php else: ?>
                                    <?= $_SESSION['user']['numeroCompte'] ?>
                                <?php endif; ?>
                            </td>
                            <td class="<?= $op['typeOperation'] == 'Depot' || ($op['typeOperation'] == 'Virement' && $op['compteCrediteur'] == $_SESSION['user']['numeroCompte']) ? 'credit' : 'debit' ?>">
                                <?= ($op['typeOperation'] == 'Depot' || ($op['typeOperation'] == 'Virement' && $op['compteCrediteur'] == $_SESSION['user']['numeroCompte']) ? '+' : '-') ?>
                                <?= number_format($op['montant'], 2, ',', ' ') ?> FBU
                            </td>
                            <td><?= !empty($op['motif']) ? htmlspecialchars($op['motif']) : '-' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .transactions-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .transactions-table th, 
    .transactions-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }
    
    .transactions-table th {
        background-color: #f5f7fa;
        font-weight: 500;
    }
    
    .transactions-table tr:hover {
        background-color: #f9f9f9;
    }
    
    .filters {
        display: flex;
        gap: 10px;
    }
    
    .filters .form-control {
        width: auto;
    }
</style>

<script>
    // Filtrage côté client
    document.addEventListener('DOMContentLoaded', function() {
        const filterType = document.getElementById('filter-type');
        const filterDate = document.getElementById('filter-date');
        const rows = document.querySelectorAll('.transactions-table tbody tr');
        
        function applyFilters() {
            const typeValue = filterType.value;
            const dateValue = filterDate.value;
            
            rows.forEach(row => {
                const rowType = row.querySelector('td:nth-child(2)').textContent;
                const rowDate = row.querySelector('td:nth-child(1)').textContent;
                
                const typeMatch = typeValue === 'all' || rowType === typeValue;
                const dateMatch = !dateValue || rowDate.includes(dateValue.split('-').reverse().join('/'));
                
                row.style.display = typeMatch && dateMatch ? '' : 'none';
            });
        }
        
        filterType.addEventListener('change', applyFilters);
        filterDate.addEventListener('change', applyFilters);
    });
</script>