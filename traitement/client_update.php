<?php
session_start();
include 'db/db.php';

if (!isset($_GET['id'])) {
    
    header("Location: listeclient.php");
    exit();
}

$idClient = $_GET['id'];

$query = $pdo->prepare("SELECT * FROM client WHERE idClient = ?");
$query->execute([$idClient]);
$client = $query->fetch(PDO::FETCH_ASSOC);

if (!$client) {
    echo "Client introuvable.";
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $adresse = $_POST['adresse'];
    $telephone = $_POST['telephone'];
    $email = $_POST['email'];

    $updateQuery = $pdo->prepare("UPDATE client SET nom = ?, prenom = ?, adresse = ?, telephone = ?, email = ? WHERE idClient = ?");
    $updateQuery->execute([$nom, $prenom, $adresse, $telephone, $email, $idClient]);

    $_SESSION['message'] = "Client mis à jour avec succès.";
    header("Location: http://localhost/examen/?page=listeclient");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Client</title>
    <link rel="stylesheet" href="../css/formulaire.css">
</head>
<body>
    <header>
        <h1>Modifier un Client</h1>
    </header>

    <main>
        <section id="clientForm">
            <form action="" method="POST">
                <h2>Formulaire de modification</h2>

                <label for="nom">Nom :</label>
                <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($client['nom']) ?>" required>

                <label for="prenom">Prénom :</label>
                <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($client['prenom']) ?>" required>

                <label for="adresse">Adresse :</label>
                <textarea id="adresse" name="adresse" required><?= htmlspecialchars($client['adresse']) ?></textarea>

                <label for="telephone">Téléphone :</label>
                <input type="tel" id="telephone" name="telephone" value="<?= htmlspecialchars($client['telephone']) ?>" required>

                <label for="email">Email :</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($client['email']) ?>" required>

                <button type="submit">Mettre à jour</button>
            </form>

            <?php
            if (isset($_SESSION['message'])) {
                echo "<p style='color: green;'>" . htmlspecialchars($_SESSION['message']) . "</p>";
                unset($_SESSION['message']);
            }
            ?>
        </section>
    </main>
</body>
</html>
