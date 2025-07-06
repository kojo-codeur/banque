<?php

// Vérification du rôle client
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'client') {
    header("Location: ../index.php");
    exit();
}

// Récupérer les comptes du client pour les virements
$stmt = $pdo->prepare("SELECT idCompte, numeroCompte, solde FROM compte WHERE idClient = ?");
$stmt->execute([$_SESSION['user']['id']]);
$comptes = $stmt->fetchAll();

// Récupérer les virements récents du client
$stmt = $pdo->prepare("SELECT o.*, 
                      (SELECT numeroCompte FROM compte WHERE idCompte = o.idCompteDebiteur) as compteDebiteur,
                      (SELECT numeroCompte FROM compte WHERE idCompte = o.idCompteCrediteur) as compteCrediteur
                      FROM operation o
                      JOIN compte c ON (o.idCompteDebiteur = c.idCompte OR o.idCompteCrediteur = c.idCompte)
                      WHERE c.idClient = ? AND o.typeOperation = 'Virement'
                      ORDER BY o.dateOperation DESC
                      LIMIT 5");
$stmt->execute([$_SESSION['user']['id']]);
$virements = $stmt->fetchAll();

// Traitement du formulaire de virement
if (isset($_POST['effectuer_virement'])) {
    $compte_source = $_POST['compte_source'];
    $compte_dest = trim($_POST['compte_dest']);
    $montant = floatval($_POST['montant']);
    $motif = trim($_POST['motif']);
    
    // Validation
    if ($montant <= 0) {
        $_SESSION['error'] = "Le montant doit être positif";
        header("Location: http://localhost/joelbanque/client/dashboard.php?page=virements");
        exit();
    }
    
    // Vérifier que le compte source appartient bien au client
    $compte_valide = false;
    $compte_source_id = null;
    foreach ($comptes as $compte) {
        if ($compte['numeroCompte'] == $compte_source) {
            $compte_valide = true;
            $compte_source_id = $compte['idCompte'];
            if ($compte['solde'] < $montant) {
                $_SESSION['error'] = "Solde insuffisant";
                header("Location: http://localhost/joelbanque/client/dashboard.php?page=virements");
                exit();
            }
            break;
        }
    }
    
    if (!$compte_valide) {
        $_SESSION['error'] = "Compte source invalide";
        header("Location: http://localhost/joelbanque/client/dashboard.php?page=virements");
        exit();
    }
    
    try {
        // Vérifier que le compte destinataire existe et est différent du compte source
        $stmt = $pdo->prepare("SELECT idCompte FROM compte WHERE numeroCompte = ? AND idCompte != ?");
        $stmt->execute([$compte_dest, $compte_source_id]);
        $compte_dest_data = $stmt->fetch();
        
        if (!$compte_dest_data) {
            $_SESSION['error'] = "Compte destinataire introuvable ou identique au compte source";
            header("Location: http://localhost/joelbanque/client/dashboard.php?page=virements");
            exit();
        }
        
        // Effectuer le virement
        $pdo->beginTransaction();
        
        // Débiter le compte source
        $pdo->prepare("UPDATE compte SET solde = solde - ? WHERE idCompte = ?")
           ->execute([$montant, $compte_source_id]);
        
        // Créditer le compte destinataire
        $pdo->prepare("UPDATE compte SET solde = solde + ? WHERE idCompte = ?")
           ->execute([$montant, $compte_dest_data['idCompte']]);
        
        // Enregistrer l'opération
        $pdo->prepare("INSERT INTO operation (typeOperation, montant, dateOperation, idCompteDebiteur, idCompteCrediteur, motif) 
                      VALUES ('Virement', ?, NOW(), ?, ?, ?)")
           ->execute([$montant, $compte_source_id, $compte_dest_data['idCompte'], $motif]);
        
        $pdo->commit();
        
        $_SESSION['success'] = "Virement effectué avec succès";
        header("Location: http://localhost/joelbanque/client/dashboard.php?page=virements");
        exit();
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Erreur lors du virement: " . $e->getMessage();
        header("Location: http://localhost/joelbanque/client/dashboard.php?page=virements");
        exit();
    }
}
?>


<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error" style="margin-bottom: 20px;">
        <i class='bx bxs-error-circle'></i>
        <span><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success" style="margin-bottom: 20px;">
        <i class='bx bxs-check-circle'></i>
        <span><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></span>
    </div>
<?php endif; ?>

<div class="virement-container">
    <!-- Formulaire de virement -->
    <div class="card">
        <div class="card-header">
            <h2>Nouveau virement</h2>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="form-group">
                    <label for="compte_source">Compte source</label>
                    <select id="compte_source" name="compte_source" class="form-control" required>
                        <?php foreach ($comptes as $compte): ?>
                            <option value="<?= $compte['numeroCompte'] ?>">
                                <?= htmlspecialchars($compte['numeroCompte']) ?> - 
                                <?= number_format($compte['solde'], 2, ',', ' ') ?> FBU
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="compte_dest">Compte destinataire</label>
                    <input type="text" id="compte_dest" name="compte_dest" 
                            class="form-control" placeholder="Numéro de compte" required>
                </div>
                
                <div class="form-group">
                    <label for="montant">Montant (FBU)</label>
                    <input type="number" id="montant" name="montant" 
                            class="form-control" min="0.01" step="0.01" required
                            placeholder="0,00">
                </div>
                
                <div class="form-group">
                    <label for="motif">Motif (optionnel)</label>
                    <textarea id="motif" name="motif" class="form-control" rows="3"
                                placeholder="Description du virement"></textarea>
                </div>
                
                <button type="submit" name="effectuer_virement" class="btn btn-primary">
                    <i class='bx bx-transfer'></i> Effectuer le virement
                </button>
            </form>
        </div>
    </div>
    
    <!-- Historique des virements récents -->
<div class="card">
    <div class="card-header">
        <h2>Virements récents</h2>
        <a href="?page=historique" class="see-all">Voir tout</a>
    </div>
    <div class="card-body">
        <?php if (empty($virements)): ?>
            <div class="empty-state">
                <i class='bx bx-transfer-alt'></i>
                <p>Aucun virement effectué récemment</p>
            </div>
        <?php else: 
            // Calculer la moitié des virements
            $half_count = ceil(count($virements) / 2);
            $visible_virements = array_slice($virements, 0, $half_count);
            $hidden_virements = array_slice($virements, $half_count);
        ?>
            <ul class="virement-list">
                <?php foreach ($visible_virements as $virement): 
                    $is_credit = $virement['compteCrediteur'] == $_SESSION['user']['numeroCompte'];
                ?>
                <li class="virement-item">
                    <div class="virement-icon">
                        <i class='bx bx-transfer-alt'></i>
                    </div>
                    <div class="virement-details">
                        <h3>Virement bancaire</h3>
                        <p><?= date('d/m/Y à H:i', strtotime($virement['dateOperation'])) ?></p>
                        <p><?= $is_credit ? 'De: ' . htmlspecialchars($virement['compteDebiteur']) 
                                            : 'À: ' . htmlspecialchars($virement['compteCrediteur']) ?></p>
                        <?php if (!empty($virement['motif'])): ?>
                            <p><em><?= htmlspecialchars($virement['motif']) ?></em></p>
                        <?php endif; ?>
                    </div>
                    <div class="virement-amount <?= $is_credit ? 'virement-credit' : 'virement-debit' ?>">
                        <?= $is_credit ? '+' : '-' ?>
                        <?= number_format($virement['montant'], 2, ',', ' ') ?> FBU
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
            
            <!-- Virements cachés -->
            <ul class="virement-list hidden-virements" style="display: none;">
                <?php foreach ($hidden_virements as $virement): 
                    $is_credit = $virement['compteCrediteur'] == $_SESSION['user']['numeroCompte'];
                ?>
                <li class="virement-item">
                    <div class="virement-icon">
                        <i class='bx bx-transfer-alt'></i>
                    </div>
                    <div class="virement-details">
                        <h3>Virement bancaire</h3>
                        <p><?= date('d/m/Y à H:i', strtotime($virement['dateOperation'])) ?></p>
                        <p><?= $is_credit ? 'De: ' . htmlspecialchars($virement['compteDebiteur']) 
                                            : 'À: ' . htmlspecialchars($virement['compteCrediteur']) ?></p>
                        <?php if (!empty($virement['motif'])): ?>
                            <p><em><?= htmlspecialchars($virement['motif']) ?></em></p>
                        <?php endif; ?>
                    </div>
                    <div class="virement-amount <?= $is_credit ? 'virement-credit' : 'virement-debit' ?>">
                        <?= $is_credit ? '+' : '-' ?>
                        <?= number_format($virement['montant'], 2, ',', ' ') ?> FBU
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
            
            <?php if (count($virements) > $half_count): ?>
                <div class="see-more-container">
                    <button class="see-more-btn">Voir plus <i class='bx bx-chevron-down'></i></button>
                    <button class="see-less-btn" style="display: none;">Voir moins <i class='bx bx-chevron-up'></i></button>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const seeMoreBtn = document.querySelector('.see-more-btn');
    const seeLessBtn = document.querySelector('.see-less-btn');
    const hiddenVirements = document.querySelector('.hidden-virements');
    
    if (seeMoreBtn) {
        seeMoreBtn.addEventListener('click', function() {
            hiddenVirements.style.display = 'block';
            seeMoreBtn.style.display = 'none';
            seeLessBtn.style.display = 'block';
        });
    }
    
    if (seeLessBtn) {
        seeLessBtn.addEventListener('click', function() {
            hiddenVirements.style.display = 'none';
            seeMoreBtn.style.display = 'block';
            seeLessBtn.style.display = 'none';
        });
    }
});
</script>