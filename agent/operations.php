<?php
require_once '../db/db.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}

// Récupération de la liste des comptes
try {
    $stmt = $pdo->query("SELECT idCompte, numeroCompte, solde FROM Compte WHERE statut = 'actif' ORDER BY numeroCompte");
    $comptes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($comptes)) {
        throw new Exception("Aucun compte actif disponible pour les opérations.");
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header('Location: dashboard.php');
    exit();
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: dashboard.php');
    exit();
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validation des entrées
    $typeOperation = filter_input(INPUT_POST, 'typeOperation', FILTER_SANITIZE_STRING);
    $montant = filter_input(INPUT_POST, 'montant', FILTER_VALIDATE_FLOAT);
    $idCompteDebiteur = filter_input(INPUT_POST, 'idCompteDebiteur', FILTER_VALIDATE_INT);
    $idCompteCrediteur = filter_input(INPUT_POST, 'idCompteCrediteur', FILTER_VALIDATE_INT);
    $motif = filter_input(INPUT_POST, 'motif', FILTER_SANITIZE_STRING);

    // Vérification des données
    if (!$typeOperation || !in_array($typeOperation, ['Retrait', 'Depot', 'Virement'])) {
        $_SESSION['error'] = "Type d'opération invalide.";
    } elseif (!$montant || $montant <= 0) {
        $_SESSION['error'] = "Montant invalide. Doit être un nombre positif.";
    } elseif (!$idCompteDebiteur) {
        $_SESSION['error'] = "Compte débiteur invalide.";
    } elseif ($typeOperation == 'Virement' && !$idCompteCrediteur) {
        $_SESSION['error'] = "Compte créditeur requis pour un virement.";
    } elseif ($typeOperation == 'Virement' && $idCompteDebiteur == $idCompteCrediteur) {
        $_SESSION['error'] = "Les comptes débiteur et créditeur doivent être différents.";
    } elseif (empty($motif)) {
        $_SESSION['error'] = "Le motif de l'opération est obligatoire.";
    } else {
        try {
            $pdo->beginTransaction();

            // Vérification de l'existence des comptes
            $stmt = $pdo->prepare("SELECT idCompte, solde FROM Compte WHERE idCompte = ? AND statut = 'actif' FOR UPDATE");
            $stmt->execute([$idCompteDebiteur]);
            $compteDebiteur = $stmt->fetch();

            if (!$compteDebiteur) {
                throw new Exception("Compte débiteur introuvable ou inactif.");
            }

            if ($typeOperation == 'Virement') {
                $stmt->execute([$idCompteCrediteur]);
                $compteCrediteur = $stmt->fetch();

                if (!$compteCrediteur) {
                    throw new Exception("Compte créditeur introuvable ou inactif.");
                }
            }

            // Traitement selon le type d'opération
            switch ($typeOperation) {
                case 'Retrait':
                    if ($compteDebiteur['solde'] < $montant) {
                        throw new Exception("Solde insuffisant pour effectuer ce retrait.");
                    }

                    $stmt = $pdo->prepare("UPDATE Compte SET solde = solde - ? WHERE idCompte = ?");
                    $stmt->execute([$montant, $idCompteDebiteur]);
                    break;

                case 'Depot':
                    $stmt = $pdo->prepare("UPDATE Compte SET solde = solde + ? WHERE idCompte = ?");
                    $stmt->execute([$montant, $idCompteDebiteur]);
                    break;

                case 'Virement':
                    if ($compteDebiteur['solde'] < $montant) {
                        throw new Exception("Solde insuffisant pour effectuer ce virement.");
                    }

                    // Débit du compte débiteur
                    $stmt = $pdo->prepare("UPDATE Compte SET solde = solde - ? WHERE idCompte = ?");
                    $stmt->execute([$montant, $idCompteDebiteur]);

                    // Crédit du compte créditeur
                    $stmt = $pdo->prepare("UPDATE Compte SET solde = solde + ? WHERE idCompte = ?");
                    $stmt->execute([$montant, $idCompteCrediteur]);
                    break;
            }

            // Enregistrement de l'opération
            $query = "INSERT INTO Operation (
                typeOperation, 
                montant, 
                idCompteDebiteur, 
                idCompteCrediteur, 
                dateOperation,
                motif,
                is_read,
                id_employe
            ) VALUES (?, ?, ?, ?, NOW(), ?, 0, ?)";
            
            $stmt = $pdo->prepare($query);
            $params = [
                $typeOperation, 
                $montant, 
                $idCompteDebiteur, 
                $idCompteCrediteur,
                $motif,
                $_SESSION['id'] ?? null // Si vous avez un id_employe dans la session
            ];
            
            $stmt->execute($params);

            $pdo->commit();
            $_SESSION['success'] = "Opération effectuée avec succès.";
            header('Location: operations.php');
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Erreur lors de l'opération : " . $e->getMessage();
        }
    }
}
?>

<div class="container">
    <header class="main-header">
        <div class="header-left">
            <h1>Effectuer une opération</h1>
            <p>Gestion sécurisée de vos transactions financières</p>
        </div>
    </header>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <i class='bx bx-check-circle'></i>
            <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <i class='bx bx-error-circle'></i>
            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <h2 class="card-title"><i class='bx bx-edit-alt'></i> Formulaire d'opération</h2>
        
        <form method="POST" id="operationForm">
            <div class="form-grid">
                <div class="form-group">
                    <label for="typeOperation">Type d'Opération</label>
                    <select name="typeOperation" id="typeOperation" required class="form-control">
                        <option value="">-- Sélectionnez --</option>
                        <option value="Retrait" <?= isset($_POST['typeOperation']) && $_POST['typeOperation'] == 'Retrait' ? 'selected' : '' ?>>Retrait</option>
                        <option value="Depot" <?= isset($_POST['typeOperation']) && $_POST['typeOperation'] == 'Depot' ? 'selected' : '' ?>>Dépôt</option>
                        <option value="Virement" <?= isset($_POST['typeOperation']) && $_POST['typeOperation'] == 'Virement' ? 'selected' : '' ?>>Virement</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="montant">Montant (FBU)</label>
                    <input type="number" name="montant" id="montant" required step="0.01" min="0.01" 
                            value="<?= isset($_POST['montant']) ? htmlspecialchars($_POST['montant']) : '' ?>" 
                            class="form-control" placeholder="0.00">
                </div>

                <div class="form-group">
                    <label for="idCompteDebiteur">Compte débiteur</label>
                    <select name="idCompteDebiteur" id="idCompteDebiteur" required class="form-control">
                        <option value="">-- Sélectionnez un compte --</option>
                        <?php foreach ($comptes as $compte): ?>
                            <option value="<?= htmlspecialchars($compte['idCompte']) ?>" 
                                <?= isset($_POST['idCompteDebiteur']) && $_POST['idCompteDebiteur'] == $compte['idCompte'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($compte['numeroCompte']) ?> (Solde: <?= number_format($compte['solde'], 2) ?> FBU)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" id="compteCrediteurContainer" style="display:none;">
                    <label for="idCompteCrediteur">Compte créditeur</label>
                    <select name="idCompteCrediteur" id="idCompteCrediteur" class="form-control">
                        <option value="">-- Sélectionnez un compte --</option>
                        <?php foreach ($comptes as $compte): ?>
                            <option value="<?= htmlspecialchars($compte['idCompte']) ?>" 
                                <?= isset($_POST['idCompteCrediteur']) && $_POST['idCompteCrediteur'] == $compte['idCompte'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($compte['numeroCompte']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="grid-column: span 2;">
                    <label for="motif">Motif de l'opération</label>
                    <textarea name="motif" id="motif" class="form-control" required
                                placeholder="Décrivez la raison de cette opération..."><?= isset($_POST['motif']) ? htmlspecialchars($_POST['motif']) : '' ?></textarea>
                </div>
            </div>

            <div class="form-group" style="margin-top: 30px; display: flex; gap: 15px;">
                <button type="submit" class="btn btn-primary">
                    <i class='bx bx-check'></i> Confirmer l'opération
                                        </button>
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class='bx bx-arrow-back'></i> Annuler
                </a>
            </div>
        </form>
    </div>

    <!-- Section Historique des Opérations -->
    <div class="card">
        <h2 class="card-title"><i class='bx bx-history'></i> Historique des opérations récentes</h2>
        
        <div class="table-responsive">
            <table class="operations-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Montant</th>
                        <th>Compte Débiteur</th>
                        <th>Compte Créditeur</th>
                        <th>Motif</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Récupération des 5 dernières opérations
                    
                        $stmt = $pdo->prepare("
                            SELECT o.*, 
                                    d.numeroCompte AS debiteur_num,
                                    c.numeroCompte AS crediteur_num
                            FROM Operation o
                            LEFT JOIN Compte d ON o.idCompteDebiteur = d.idCompte
                            LEFT JOIN Compte c ON o.idCompteCrediteur = c.idCompte
                            ORDER BY o.dateOperation DESC
                            LIMIT 5
                        ");
                        $stmt->execute();
                        $operations = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        foreach ($operations as $operation):
                    ?>
                    <tr>
                        <td><?= date('d/m/Y H:i', strtotime($operation['dateOperation'])) ?></td>
                        <td>
                            <span class="operation-badge <?= strtolower($operation['typeOperation']) ?>">
                                <?= $operation['typeOperation'] ?>
                            </span>
                        </td>
                        <td class="<?= $operation['typeOperation'] === 'Depot' ? 'text-success' : 'text-danger' ?>">
                            <?= number_format($operation['montant'], 2) ?> FBU
                        </td>
                        <td><?= htmlspecialchars($operation['debiteur_num']) ?></td>
                        <td><?= $operation['crediteur_num'] ? htmlspecialchars($operation['crediteur_num']) : 'N/A' ?></td>
                        <td><?= htmlspecialchars($operation['motif']) ?></td>
                        <td>
                            <span class="status-badge <?= $operation['is_read'] ? 'read' : 'unread' ?>">
                                <?= $operation['is_read'] ? 'Traité' : 'En attente' ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="text-center" style="margin-top: 20px;">
            <a href="?page=historique" class="btn btn-primary">
                <i class='bx bx-list-ul'></i> Voir tout l'historique
            </a>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeOperationSelect = document.getElementById('typeOperation');
        const compteCrediteurContainer = document.getElementById('compteCrediteurContainer');
        
        function toggleCrediteurField() {
            if (typeOperationSelect.value === 'Virement') {
                compteCrediteurContainer.style.display = 'block';
                document.getElementById('idCompteCrediteur').setAttribute('required', '');
            } else {
                compteCrediteurContainer.style.display = 'none';
                document.getElementById('idCompteCrediteur').removeAttribute('required');
            }
        }
        
        typeOperationSelect.addEventListener('change', toggleCrediteurField);
        toggleCrediteurField(); // Initial call
        
        // Validation du formulaire
        document.getElementById('operationForm').addEventListener('submit', function(e) {
            const montant = parseFloat(document.getElementById('montant').value);
            if (montant <= 0 || isNaN(montant)) {
                alert('Le montant doit être un nombre positif.');
                e.preventDefault();
            }
            
            const motif = document.getElementById('motif').value.trim();
            if (motif === '') {
                alert('Veuillez saisir un motif pour cette opération.');
                e.preventDefault();
            }
            
            if (typeOperationSelect.value === 'Virement') {
                const debiteur = document.getElementById('idCompteDebiteur').value;
                const crediteur = document.getElementById('idCompteCrediteur').value;
                
                if (debiteur === crediteur) {
                    alert('Les comptes débiteur et créditeur doivent être différents.');
                    e.preventDefault();
                }
            }
        });
    });
</script>
