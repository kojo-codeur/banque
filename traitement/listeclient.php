<?php
session_start();
include 'db/db.php';

$query = $pdo->query("SELECT * FROM client");
$clients = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Clients</title>
    <link rel="stylesheet" href="css/styles.css">
    
</head>
<body>
    <header>
        <h1>Liste des Clients</h1>
    </header>

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
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Adresse</th>
                        <th>Téléphone</th>
                        <th>Email</th>
                        <th>Photo Passeport</th>
                        <th>Carte Identité</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($clients) > 0): ?>
                        <?php foreach ($clients as $client): ?>
                            <tr>
                                <td><?= htmlspecialchars($client['idClient']) ?></td>
                                <td><?= htmlspecialchars($client['nom']) ?></td>
                                <td><?= htmlspecialchars($client['prenom']) ?></td>
                                <td><?= htmlspecialchars($client['adresse']) ?></td>
                                <td><?= htmlspecialchars($client['telephone']) ?></td>
                                <td><?= htmlspecialchars($client['email']) ?></td>
                                <td><img style="height: 50px;width: 50px; border-radius: 50px;" src="uploads/<?= htmlspecialchars($client['photoPasseport']) ?>" alt="Photo Passeport" width="50"></td>
                                <td><img style="height: 50px;width: 50px; border-radius: 50px;" src="uploads/<?= htmlspecialchars($client['copieCarteIdentite']) ?>" alt="Carte Identité" width="50"></td>
                                <td>
                                <a style="color: #007bff;background-color:rgb(32, 240, 4);" href="index.php?page=clientupdate&id=<?= $client['idClient'] ?>">Modifier</a>| 
                                <a style="color: #007bff;background-color: #f05304;" href="traitement/client_delete.php?id=<?= $client['idClient'] ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce client ?');">Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9">Aucun client trouvé.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
