<?php
session_start();
include 'db/db.php';

// $rechercheop = $_POST['recherhe'];

// $recherche = $pdo->query("SELECT * FROM operation WHERE idOperation = ?");

$query = $pdo->query("SELECT * FROM operation");
$clients = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Operations</title>
    <link rel="stylesheet" href="css/styles.css">
    
</head>
<body>
    <main>
    
    <input type="text" id="recherhe" class="recherche" placeholder="entre votre recherche ici">
    <button class ="rech"id = "rech">recherche</button>

        <section id="clientsList">
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="message success">
                    <?= $_SESSION['success_message']; ?>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php elseif (isset($_SESSION['error_message'])): ?>
                <div class="message error">
                    <?= $_SESSION['error_message']; ?>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Type operation</th>
                        <th>Montant</th>
                        <th>Date operation</th>
                        <th>compte debite</th>
                        <th>compte credite</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($clients) > 0): ?>
                        <?php foreach ($clients as $client): ?>
                            <tr>
                                <td><?= htmlspecialchars($client['idOperation']) ?></td>
                                <td><?= htmlspecialchars($client['typeOperation']) ?></td>
                                <td><?= htmlspecialchars($client['montant']) ?></td>
                                <td><?= htmlspecialchars($client['dateOperation']) ?></td>
                                <td><?= htmlspecialchars($client['idCompteDebiteur']) ?></td>
                                <td><?= htmlspecialchars($client['idCompteCrediteur']) ?></td>
                                <td>
                                    <a style="color: #007bff;background-color:rgb(32, 240, 4);" href="index.php?page=operationtupdate&id=<?= $client['idOperation'] ?>">Modifier</a>| 
                                    <a style="color: #007bff;background-color: #f05304;" href="traitement/operation_delete.php?id=<?= $client['idOperation'] ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet operation ?');">Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9">Aucun Operation trouvé.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
