<?php
require_once '../db/db.php';

// Vérification de l'authentification
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}

// Paramètres de pagination
$perPage = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1; // Garantit que page >= 1
$offset = ($page - 1) * $perPage;

// Filtres
$typeFilter = isset($_GET['type']) ? $_GET['type'] : '';
$accountFilter = isset($_GET['account']) ? (int)$_GET['account'] : 0;
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Construction de la requête avec filtres
$query = "SELECT o.*, 
                 d.numeroCompte AS debiteur_num,
                 c.numeroCompte AS crediteur_num,
                 u.nom AS user_nom,
                 u.prenom AS user_prenom
          FROM Operation o
          LEFT JOIN Compte d ON o.idCompteDebiteur = d.idCompte
          LEFT JOIN Compte c ON o.idCompteCrediteur = c.idCompte
          LEFT JOIN employe u ON o.id_employe = u.id_employe
          WHERE 1=1";

$params = [];
$types = '';

// Filtre par type d'opération
if (!empty($typeFilter) && in_array($typeFilter, ['Retrait', 'Depot', 'Virement'])) {
    $query .= " AND o.typeOperation = ?";
    $params[] = $typeFilter;
    $types .= 's';
}

// Filtre par compte
if ($accountFilter > 0) {
    $query .= " AND (o.idCompteDebiteur = ? OR o.idCompteCrediteur = ?)";
    $params[] = $accountFilter;
    $params[] = $accountFilter;
    $types .= 'ii';
}

// Filtre par date
if (!empty($dateFrom)) {
    $query .= " AND o.dateOperation >= ?";
    $params[] = $dateFrom;
    $types .= 's';
}

if (!empty($dateTo)) {
    $query .= " AND o.dateOperation <= ?";
    $params[] = $dateTo . ' 23:59:59';
    $types .= 's';
}

// Requête pour le nombre total d'opérations
$countQuery = str_replace(
    'o.*, d.numeroCompte AS debiteur_num, c.numeroCompte AS crediteur_num, u.nom AS user_nom, u.prenom AS user_prenom',
    'COUNT(*) AS total',
    $query
);

// Exécution des requêtes
try {
    // Récupération des comptes pour le filtre
    $stmt = $pdo->query("SELECT idCompte, numeroCompte FROM Compte ORDER BY numeroCompte");
    $comptes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Requête paginée - modification ici
    $query .= " ORDER BY o.dateOperation DESC LIMIT ? OFFSET ?";
    $params[] = $perPage;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($query);
    
    // Liaison des paramètres avec type explicite
    foreach ($params as $key => $param) {
        $paramType = PDO::PARAM_STR; // Par défaut
        
        if (is_int($param)) {
            $paramType = PDO::PARAM_INT;
        }
        
        // Les deux derniers paramètres (LIMIT et OFFSET) sont forcément des INT
        if ($key === count($params) - 2 || $key === count($params) - 1) {
            $paramType = PDO::PARAM_INT;
        }
        
        $stmt->bindValue($key + 1, $param, $paramType);
    }
    
    $stmt->execute();
    $operations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Nombre total d'opérations
    $totalStmt = $pdo->prepare($countQuery);
    
    // On enlève les paramètres LIMIT et OFFSET pour la requête de comptage
    $countParams = array_slice($params, 0, -2);
    
    foreach ($countParams as $key => $param) {
        $paramType = PDO::PARAM_STR;
        if (is_int($param)) {
            $paramType = PDO::PARAM_INT;
        }
        $totalStmt->bindValue($key + 1, $param, $paramType);
    }
    
    $totalStmt->execute();
    $total = $totalStmt->fetchColumn();
    $totalPages = ceil($total / $perPage);

} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    
}
?>


<div class="container">
    <header class="main-header">
        <div class="header-left">
            <h1>Historique des opérations</h1>
            <p>Consultation de toutes les transactions effectuées</p>
        </div>
        <div class="header-right">
            <a href="?page=operations" class="btn btn-outline">
                <i class='bx bx-arrow-back'></i> Retour aux opérations
            </a>
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
        <h2 class="card-title"><i class='bx bx-filter-alt'></i> Filtres</h2>
        
        <div class="filter-section">
            <form method="GET" action="operations_history.php">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label for="type">Type d'opération</label>
                        <select name="type" id="type" class="filter-control">
                            <option value="">Tous les types</option>
                            <option value="Retrait" <?= $typeFilter === 'Retrait' ? 'selected' : '' ?>>Retrait</option>
                            <option value="Depot" <?= $typeFilter === 'Depot' ? 'selected' : '' ?>>Dépôt</option>
                            <option value="Virement" <?= $typeFilter === 'Virement' ? 'selected' : '' ?>>Virement</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="account">Compte</label>
                        <select name="account" id="account" class="filter-control">
                            <option value="0">Tous les comptes</option>
                            <?php foreach ($comptes as $compte): ?>
                                <option value="<?= $compte['idCompte'] ?>" <?= $accountFilter == $compte['idCompte'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($compte['numeroCompte']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="date_from">Date de début</label>
                        <input type="date" name="date_from" id="date_from" class="filter-control" value="<?= htmlspecialchars($dateFrom) ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label for="date_to">Date de fin</label>
                        <input type="date" name="date_to" id="date_to" class="filter-control" value="<?= htmlspecialchars($dateTo) ?>">
                    </div>
                    
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class='bx bx-filter'></i> Appliquer
                        </button>
                        <a href="operations_history.php" class="btn btn-secondary">
                            <i class='bx bx-reset'></i> Réinitialiser
                        </a>
                    </div>
                </div>
            </form>
        </div>
        
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
                        <th>Effectuée par</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($operations)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center;">Aucune opération trouvée</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($operations as $operation): ?>
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
                            <td><?= htmlspecialchars($operation['user_prenom'] . ' ' . $operation['user_nom']) ?></td>
                            <td>
                                <span class="status-badge <?= $operation['is_read'] ? 'read' : 'unread' ?>">
                                    <?= $operation['is_read'] ? 'Traité' : 'En attente' ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($total > $perPage): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>">
                    <i class='bx bx-first-page'></i>
                </a>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                    <i class='bx bx-chevron-left'></i>
                </a>
            <?php else: ?>
                <span class="disabled"><i class='bx bx-first-page'></i></span>
                <span class="disabled"><i class='bx bx-chevron-left'></i></span>
            <?php endif; ?>
            
            <?php
            $totalPages = ceil($total / $perPage);
            $start = max(1, $page - 2);
            $end = min($totalPages, $page + 2);
            
            if ($start > 1) echo '<span>...</span>';
            
            for ($i = $start; $i <= $end; $i++):
            ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" <?= $i == $page ? 'class="active"' : '' ?>>
                    <?= $i ?>
                </a>
            <?php endfor; ?>
            <?php
                if ($end < $totalPages) echo '<span>...</span>';
            ?>
            
            <?php if ($page < $totalPages): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                    <i class='bx bx-chevron-right'></i>
                </a>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $totalPages])) ?>">
                    <i class='bx bx-last-page'></i>
                </a>
            <?php else: ?>
                <span class="disabled"><i class='bx bx-chevron-right'></i></span>
                <span class="disabled"><i class='bx bx-last-page'></i></span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

