<?php
include '../db/db.php';

// Récupérer les comptes selon le filtre
$filter = $_GET['filter'] ?? 'en_attente';

// Vérifier si la colonne 'statut' existe
$columnCheck = $pdo->query("SHOW COLUMNS FROM compte LIKE 'statut'")->fetch();
$hasStatut = ($columnCheck !== false);

// Requête pour compter les comptes en attente
$pendingQuery = $hasStatut ? "SELECT COUNT(*) as count FROM compte WHERE statut = 'en_attente'" 
                          : "SELECT COUNT(*) as count FROM compte";
$pendingResult = $pdo->query($pendingQuery)->fetch();
$pendingAccounts = $pendingResult['count'];

// Vérifier si la colonne 'motif_rejet' existe
$columnCheck = $pdo->query("SHOW COLUMNS FROM compte LIKE 'motif_rejet'")->fetch();
$hasMotifRejet = ($columnCheck !== false);

$selectColumns = "
    c.idCompte, c.numeroCompte, c.typeCompte, c.solde, c.dateCreation,
    cl.prenom, cl.nom, cl.telephone, cl.email
";

if ($hasStatut) {
    $selectColumns .= ", c.statut";
}
if ($hasMotifRejet) {
    $selectColumns .= ", c.motif_rejet";
}

$query = "
    SELECT 
        $selectColumns
    FROM compte c
    JOIN client cl ON c.idClient = cl.idClient
";

if ($hasStatut) {
    if ($filter === 'en_attente') {
        $query .= " WHERE c.statut = 'en_attente'";
    } elseif ($filter === 'actifs') {
        $query .= " WHERE c.statut = 'actif'";
    } elseif ($filter === 'rejetes') {
        $query .= " WHERE c.statut = 'rejeté'";
    }
}

$query .= " ORDER BY c.dateCreation DESC";
$comptes = $pdo->query($query)->fetchAll();

// Fonctions JavaScript pour les boutons
$jsFunctions = "
<script>
function validateAccount(id) {
    if (confirm('Voulez-vous vraiment valider ce compte ?')) {
        window.location.href = 'actions/validate_account.php?id=' + id;
    }
}

function rejectAccount(id) {
    var motif = prompt('Veuillez entrer le motif du rejet :');
    if (motif !== null) {
        window.location.href = 'actions/reject_account.php?id=' + id + '&motif=' + encodeURIComponent(motif);
    }
}
</script>
";
?>

<div class="card">
    <div class="card-header">
        <h2>Gestion des comptes</h2>
        <div class="filter-options">
            <a href="?page=comptes&filter=en_attente" class="<?= $filter === 'en_attente' ? 'active' : '' ?>">
                En attente
                <?php if ($pendingAccounts > 0): ?>
                    <span class="filter-badge"><?= $pendingAccounts ?></span>
                <?php endif; ?>
            </a>
            <a href="?page=comptes&filter=actifs" class="<?= $filter === 'actifs' ? 'active' : '' ?>">Actifs</a>
            <a href="?page=comptes&filter=rejetes" class="<?= $filter === 'rejetes' ? 'active' : '' ?>">Rejetés</a>
        </div>
    </div>
    
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Numéro</th>
                        <th>Client</th>
                        <th>Type</th>
                        <th>Contact</th>
                        <th>Solde</th>
                        <th>Statut</th>
                        <th>Date création</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($comptes as $compte): 
                        $statut = $hasStatut ? ($compte['statut'] ?? 'inconnu') : 'inconnu';
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($compte['numeroCompte'] ?? '') ?></td>
                        <td><?= htmlspecialchars(($compte['prenom'] ?? '') . ' ' . ($compte['nom'] ?? '')) ?></td>
                        <td><?= htmlspecialchars($compte['typeCompte'] ?? '') ?></td>
                        <td>
                            <div><?= htmlspecialchars($compte['email'] ?? '') ?></div>
                            <small><?= htmlspecialchars($compte['telephone'] ?? '') ?></small>
                        </td>
                        <td><?= number_format($compte['solde'] ?? 0, 2, ',', ' ') ?> FBU</td>
                        <td>
                            <span class="status-badge <?= htmlspecialchars($statut) ?>">
                                <?= ucfirst(htmlspecialchars($statut)) ?>
                            </span>
                            <?php if ($hasMotifRejet && $statut === 'rejeté' && !empty($compte['motif_rejet'])): ?>
                                <div class="reject-reason">
                                    <small>Motif: <?= htmlspecialchars($compte['motif_rejet']) ?></small>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d/m/Y', strtotime($compte['dateCreation'] ?? 'now')) ?></td>
                        <td class="actions">
                            <?php if ($hasStatut && $statut === 'en_attente'): ?>
                                <button class="btn btn-validate" onclick="validateAccount(<?= $compte['idCompte'] ?>)">
                                    <i class='bx bx-check'></i> Valider
                                </button>
                                <button class="btn btn-reject" onclick="rejectAccount(<?= $compte['idCompte'] ?>)">
                                    <i class='bx bx-x'></i> Rejeter
                                </button>
                            <?php endif; ?>
                            <a href="?page=compte_details&id=<?= $compte['idCompte'] ?>" class="btn btn-view">
                                <i class='bx bx-show'></i> Détails
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.status-badge {
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.status-badge.en_attente {
    background-color: #FFF3CD;
    color: #856404;
}

.status-badge.actif {
    background-color: #D4EDDA;
    color: #155724;
}

.status-badge.rejeté {
    background-color: #F8D7DA;
    color: #721C24;
}

.status-badge.inconnu {
    background-color: #E2E3E5;
    color: #383D41;
}

.reject-reason {
    margin-top: 3px;
    color: #721C24;
    font-style: italic;
}

.filter-badge {
    background-color: #DC3545;
    color: white;
    border-radius: 10px;
    padding: 2px 6px;
    font-size: 12px;
    margin-left: 5px;
}

.btn {
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 14px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin: 2px;
}

.btn-validate {
    background-color: #28A745;
    color: white;
    border: none;
}

.btn-reject {
    background-color: #DC3545;
    color: white;
    border: none;
}

.btn-view {
    background-color: #17A2B8;
    color: white;
    text-decoration: none;
}
</style>

<?= $jsFunctions ?>