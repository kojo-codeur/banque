<?php
include '../db/db.php';

// Fonction pour générer un numéro de compte unique
function genererNumeroCompte($pdo) {
       
    do {
        $suffixe = str_pad(mt_rand(0, 9999), 6, '0', STR_PAD_LEFT); // 4 chiffres aléatoires
        $numeroCompte = $suffixe;
        
        // Vérifier si le numéro existe déjà
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Compte WHERE numeroCompte = ?");
        $stmt->execute([$numeroCompte]);
        $exists = $stmt->fetchColumn();
    } while ($exists > 0);
    
    return $numeroCompte;
}

// Vérification de la méthode POST et traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Génération du numéro de compte
    $numeroCompte = genererNumeroCompte($pdo);
    
    // Nettoyage et validation des données
    $typeCompte = htmlspecialchars(trim($_POST['typeCompte'] ?? ''));
    $solde = filter_var($_POST['solde'] ?? 0, FILTER_VALIDATE_FLOAT);
    $dateCreation = htmlspecialchars(trim($_POST['dateCreation'] ?? ''));
    $idClient = filter_var($_POST['idClient'] ?? 0, FILTER_VALIDATE_INT);

    // Validation des données
    $errors = [];
    
    if (!in_array($typeCompte, ['Courant', 'Épargne'])) {
        $errors[] = "Type de compte invalide";
    }
    
    if ($solde === false) {
        $errors[] = "Le solde doit être un nombre valide";
    }
    
    if (empty($dateCreation) || !strtotime($dateCreation)) {
        $errors[] = "Date de création invalide";
    }
    
    if ($idClient <= 0) {
        $errors[] = "Client invalide";
    }

    // Si pas d'erreurs, insertion en base
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("INSERT INTO Compte (numeroCompte, typeCompte, solde, dateCreation, idClient) 
                                  VALUES (:numero, :type, :solde, :date_creation, :client)");
            $stmt->execute([
                ':numero' => $numeroCompte,
                ':type' => $typeCompte,
                ':solde' => $solde,
                ':date_creation' => $dateCreation,
                ':client' => $idClient
            ]);
            
            $pdo->commit();
            
            $_SESSION['success_message'] = "Compte ajouté avec succès! Numéro de compte: " . $numeroCompte;
           
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = "Erreur d'ajout du compte: " . $e->getMessage();
        }
    }
}

// Récupération des clients pour le select
try {
    $query = $pdo->query("SELECT idClient, nom, prenom FROM client ORDER BY nom, prenom");
    $clients = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errors[] = "Erreur de récupération des clients: " . $e->getMessage();
    $clients = [];
}
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <header class="mb-4">
                <h1 class="text-center">Ajouter un Compte</h1>
            </header>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($_SESSION['success_message']) ?>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <form method="POST" class="needs-validation" novalidate>
                <?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($errors)): ?>
                    <div class="generated-account mb-3">
                        <strong>Numéro de compte généré:</strong> <?= htmlspecialchars($numeroCompte) ?>
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label for="typeCompte" class="form-label">Type de Compte :</label>
                    <select class="form-select" id="typeCompte" name="typeCompte" required>
                        <option value="">Sélectionnez un type</option>
                        <option value="Courant" <?= ($_POST['typeCompte'] ?? '') === 'Courant' ? 'selected' : '' ?>>Courant</option>
                        <option value="Épargne" <?= ($_POST['typeCompte'] ?? '') === 'Épargne' ? 'selected' : '' ?>>Épargne</option>
                    </select>
                    <div class="invalid-feedback">
                        Veuillez sélectionner un type de compte.
                    </div>
                </div>

                <div class="mb-3">
                    <label for="solde" class="form-label">Solde Initial :</label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="solde" name="solde" 
                               step="0.01" min="0" value="<?= htmlspecialchars($_POST['solde'] ?? '0.00') ?>" required>
                        <span class="input-group-text">FBU</span>
                        <div class="invalid-feedback">
                            Veuillez saisir un solde valide.
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="dateCreation" class="form-label">Date de Création :</label>
                    <input type="date" class="form-control" id="dateCreation" name="dateCreation" 
                           value="<?= htmlspecialchars($_POST['dateCreation'] ?? date('Y-m-d')) ?>" required>
                    <div class="invalid-feedback">
                        Veuillez saisir une date valide.
                    </div>
                </div>

                <div class="mb-4">
                    <label for="idClient" class="form-label">Client :</label>
                    <select class="form-select" id="idClient" name="idClient" required>
                        <option value="">Sélectionnez un client</option>
                        <?php foreach ($clients as $client): ?>
                            <option value="<?= htmlspecialchars($client['idClient']) ?>"
                                <?= ($_POST['idClient'] ?? '') == $client['idClient'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($client['idClient']) ?> - 
                                <?= htmlspecialchars($client['nom']) ?> 
                                <?= htmlspecialchars($client['prenom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">
                        Veuillez sélectionner un client.
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">Créer le Compte</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Validation côté client
(() => {
    'use strict'

    const forms = document.querySelectorAll('.needs-validation')

    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }

            form.classList.add('was-validated')
        }, false)
    })
})()
</script>
