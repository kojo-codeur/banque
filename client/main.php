<div class="content-grid">
    <!-- Solde -->
    <div class="card balance-card">
        <div class="card-header">
            <h2>Solde actuel</h2>
            <i class='bx bx-wallet'></i>
        </div>
        <div class="card-body">
            <p class="balance-amount"><?= number_format($_SESSION['user']['solde'], 2, ',', ' ') ?> FBU</p>
            <p class="account-type">Compte <?= htmlspecialchars($_SESSION['user']['typeCompte']) ?></p>
        </div>
    </div>
    
    <!-- Dernières transactions -->
    <div class="card transactions-card">
        <div class="card-header">
            <h2>Dernières transactions</h2>
            <a href="?page=historique" class="see-all">Voir tout</a>
        </div>
        <div class="card-body">
            <?php if (empty($operations)): ?>
                <div class="empty-state">
                    <p>Aucune opération récente</p>
                </div>
            <?php else: 
                // Calculer la moitié des transactions
                $half_count = ceil(count($operations) / 2);
                $visible_operations = array_slice($operations, 0, $half_count);
                $hidden_operations = array_slice($operations, $half_count);
            ?>
                <ul class="transactions-list">
                    <?php foreach ($visible_operations as $op): ?>
                    <li class="transaction-item">
                        <div class="transaction-icon">
                            <?php if ($op['typeOperation'] == 'Virement'): ?>
                                <i class='bx bx-transfer'></i>
                            <?php elseif ($op['typeOperation'] == 'Depot'): ?>
                                <i class='bx bx-plus-circle'></i>
                            <?php else: ?>
                                <i class='bx bx-minus-circle'></i>
                            <?php endif; ?>
                        </div>
                        <div class="transaction-details">
                            <h3><?= htmlspecialchars($op['typeOperation']) ?></h3>
                            <p><?= date('d/m/Y H:i', strtotime($op['dateOperation'])) ?></p>
                            <?php if ($op['typeOperation'] == 'Virement'): ?>
                                <p class="transaction-info">
                                    <?= $op['compteDebiteur'] == $_SESSION['user']['numeroCompte'] ? 'À: ' . $op['compteCrediteur'] : 'De: ' . $op['compteDebiteur'] ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <div class="transaction-amount <?= $op['typeOperation'] == 'Depot' || ($op['typeOperation'] == 'Virement' && $op['compteCrediteur'] == $_SESSION['user']['numeroCompte']) ? 'credit' : 'debit' ?>">
                            <?= ($op['typeOperation'] == 'Depot' || ($op['typeOperation'] == 'Virement' && $op['compteCrediteur'] == $_SESSION['user']['numeroCompte'])) ? '+' : '-' ?>
                            <?= number_format($op['montant'], 2, ',', ' ') ?> FBU
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                
                <!-- Transactions cachées -->
                <ul class="transactions-list hidden-transactions" style="display: none;">
                    <?php foreach ($hidden_operations as $op): ?>
                    <li class="transaction-item">
                        <div class="transaction-icon">
                            <?php if ($op['typeOperation'] == 'Virement'): ?>
                                <i class='bx bx-transfer'></i>
                            <?php elseif ($op['typeOperation'] == 'Depot'): ?>
                                <i class='bx bx-plus-circle'></i>
                            <?php else: ?>
                                <i class='bx bx-minus-circle'></i>
                            <?php endif; ?>
                        </div>
                        <div class="transaction-details">
                            <h3><?= htmlspecialchars($op['typeOperation']) ?></h3>
                            <p><?= date('d/m/Y H:i', strtotime($op['dateOperation'])) ?></p>
                            <?php if ($op['typeOperation'] == 'Virement'): ?>
                                <p class="transaction-info">
                                    <?= $op['compteDebiteur'] == $_SESSION['user']['numeroCompte'] ? 'À: ' . $op['compteCrediteur'] : 'De: ' . $op['compteDebiteur'] ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <div class="transaction-amount <?= $op['typeOperation'] == 'Depot' || ($op['typeOperation'] == 'Virement' && $op['compteCrediteur'] == $_SESSION['user']['numeroCompte']) ? 'credit' : 'debit' ?>">
                            <?= ($op['typeOperation'] == 'Depot' || ($op['typeOperation'] == 'Virement' && $op['compteCrediteur'] == $_SESSION['user']['numeroCompte'])) ? '+' : '-' ?>
                            <?= number_format($op['montant'], 2, ',', ' ') ?> FBU
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                
                <?php if (count($operations) > $half_count): ?>
                    <div class="see-more-container">
                        <button class="see-more-btn">Voir plus <i class='bx bx-chevron-down'></i></button>
                        <button class="see-less-btn" style="display: none;">Voir moins <i class='bx bx-chevron-up'></i></button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Actions rapides -->
    <div class="card quick-actions-card">
        <div class="card-header">
            <h2>Actions rapides</h2>
        </div>
        <div class="card-body">
            <div class="quick-actions-grid">
                <a href="?page=virements" class="quick-action">
                    <div class="action-icon">
                        <i class='bx bx-transfer'></i>
                    </div>
                    <span>Virement</span>
                </a>
                <a href="?page=contact" class="quick-action">
                    <div class="action-icon">
                        <i class='bx bx-headphone'></i>
                    </div>
                    <span>Contact</span>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const seeMoreBtn = document.querySelector('.see-more-btn');
    const seeLessBtn = document.querySelector('.see-less-btn');
    const hiddenTransactions = document.querySelector('.hidden-transactions');
    
    if (seeMoreBtn) {
        seeMoreBtn.addEventListener('click', function() {
            hiddenTransactions.style.display = 'block';
            seeMoreBtn.style.display = 'none';
            seeLessBtn.style.display = 'block';
        });
    }
    
    if (seeLessBtn) {
        seeLessBtn.addEventListener('click', function() {
            hiddenTransactions.style.display = 'none';
            seeMoreBtn.style.display = 'block';
            seeLessBtn.style.display = 'none';
        });
    }
});
</script>