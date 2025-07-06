<?php
include 'db/db.php';

$query = $pdo->query("SELECT * FROM Compte JOIN Client ON Compte.idClient = Client.idClient");
$comptes = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Comptes</title>
    <link rel="stylesheet" href="css/styles.css">

</head>
<body>
    <header>
        <h1>Liste des Comptes</h1>
    </header>

    <main>
        <input type="text" id="recherhe" class="recherche" placeholder="entre votre recherche ici">
        <button class ="rech"id = "rech">recherche</button>
        <table>
            <thead>
                <tr>
                    <th>ID Compte</th>
                    <th>Numéro de Compte</th>
                    <th>Type de Compte</th>
                    <th>Solde</th>
                    <th>Date de Création</th>
                    <th>Client</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($comptes) > 0): ?>
                    <?php foreach ($comptes as $compte): ?>
                        <tr>
                            <td><?= htmlspecialchars($compte['idCompte']) ?></td>
                            <td><?= htmlspecialchars($compte['numeroCompte']) ?></td>
                            <td><?= htmlspecialchars($compte['typeCompte']) ?></td>
                            <td><?= htmlspecialchars($compte['solde']) ?></td>
                            <td><?= htmlspecialchars($compte['dateCreation']) ?></td>
                            <td><?= htmlspecialchars($compte['nom']) . ' ' . htmlspecialchars($compte['prenom']) ?></td>
                            <td>
                                <a style="color: #007bff;background-color:rgb(32, 240, 4);" href="index.php?page=comptupdate&id=<?= $compte['idCompte'] ?>">Modifier</a> |
                                <a style="color: #007bff;background-color: #f05304;" href="traitement/compte_delete.php?id=<?= $compte['idCompte'] ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce compte ?');">Supprimer</a>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">Aucun compte trouvé.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
