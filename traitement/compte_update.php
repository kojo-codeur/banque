<?php
include 'db/db.php';


if (isset($_GET['id'])) {
    $idCompte = $_GET['id'];

    
    $stmt = $pdo->prepare("SELECT * FROM Compte WHERE idCompte = ?");
    $stmt->execute([$idCompte]);
    $compte = $stmt->fetch(PDO::FETCH_ASSOC);

    
    if (!$compte) {
        echo "<p>Compte introuvable.<p>";
        exit();
    }
} else {
    echo "<p>ID de compte non spécifié.<p>";
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $numeroCompte = $_POST['numeroCompte'];
    $typeCompte = $_POST['typeCompte'];
    $solde = $_POST['solde'];
    $idCompte = $_POST['idCompte'];

    
    $stmt = $pdo->prepare("UPDATE Compte SET numeroCompte = ?, typeCompte = ?, solde = ? WHERE idCompte = ?");
    $stmt->execute([$numeroCompte, $typeCompte, $solde, $idCompte]);

    
    header("Location: index.php?page=listecompte"); 
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Compte</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <h1>Modifier le Compte</h1>

    
    <form method="POST">
        <input type="hidden" name="idCompte" value="<?= $compte['idCompte'] ?>">

        <label for="numeroCompte">Numéro de Compte :</label>
        <input type="text" name="numeroCompte" value="<?= $compte['numeroCompte'] ?>" required>

        <label for="typeCompte">Type de Compte :</label>
        <select name="typeCompte" required>
            <option value="Courant" <?= $compte['typeCompte'] == 'Courant' ? 'selected' : '' ?>>Courant</option>
            <option value="Épargne" <?= $compte['typeCompte'] == 'Épargne' ? 'selected' : '' ?>>Épargne</option>
        </select>

        <label for="solde">Solde :</label>
        <input type="number" name="solde" value="<?= $compte['solde'] ?>" step="0.01" required>

        <button type="submit">Mettre à jour</button>
    </form>

</body>
</html>
