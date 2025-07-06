<?php
include '../../db/db.php';
session_start();

if (!isset($_GET['id'])) {
    die("ID client manquant");
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM client WHERE idClient = ?");
$stmt->execute([$id]);
$client = $stmt->fetch();

if (!$client) {
    die("Client non trouvé");
}
?>

<form method="POST" action="traitement/client_update.php">
    <input type="hidden" name="idClient" value="<?= $client['idClient'] ?>">
    
    <div class="form-group">
        <label>Nom:</label>
        <input type="text" name="nom" value="<?= htmlspecialchars($client['nom']) ?>" required>
    </div>
    
    <div class="form-group">
        <label>Prénom:</label>
        <input type="text" name="prenom" value="<?= htmlspecialchars($client['prenom']) ?>" required>
    </div>
    
    <div class="form-group">
        <label>Adresse:</label>
        <input type="text" name="adresse" value="<?= htmlspecialchars($client['adresse']) ?>">
    </div>
    
    <div class="form-group">
        <label>Téléphone:</label>
        <input type="text" name="telephone" value="<?= htmlspecialchars($client['telephone']) ?>">
    </div>
    
    <div class="form-group">
        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($client['email']) ?>">
    </div>
    
    <div class="form-group">
        <label>Photo Passeport:</label>
        <input type="file" name="photoPasseport">
        <?php if (!empty($client['photoPasseport'])): ?>
            <p>Fichier actuel: <?= htmlspecialchars($client['photoPasseport']) ?></p>
        <?php endif; ?>
    </div>
    
    <div class="form-group">
        <label>Copie Carte Identité:</label>
        <input type="file" name="copieCarteIdentite">
        <?php if (!empty($client['copieCarteIdentite'])): ?>
            <p>Fichier actuel: <?= htmlspecialchars($client['copieCarteIdentite']) ?></p>
        <?php endif; ?>
    </div>
    
    <button type="submit" class="btn-save">Enregistrer</button>
</form>