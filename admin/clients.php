<?php
include '../db/db.php';

// Gestion de la suppression
if (isset($_POST['delete_id'])) {
    $id = $_POST['delete_id'];
    try {
        // Suppression des fichiers images d'abord
        $stmt = $pdo->prepare("SELECT photoPasseport, copieCarteIdentite FROM client WHERE idClient = ?");
        $stmt->execute([$id]);
        $client = $stmt->fetch();
        
        if ($client) {
            if (!empty($client['photoPasseport']) && file_exists("../photos/".$client['photoPasseport'])) {
                unlink("../photos/".$client['photoPasseport']);
            }
            if (!empty($client['copieCarteIdentite']) && file_exists("../photos/".$client['copieCarteIdentite'])) {
                unlink("../photos/".$client['copieCarteIdentite']);
            }
            
            // Suppression du client
            $stmt = $pdo->prepare("DELETE FROM client WHERE idClient = ?");
            $stmt->execute([$id]);
            
            $_SESSION['success_message'] = "Client supprimé avec succès";
        } else {
            $_SESSION['error_message'] = "Client non trouvé";
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Erreur lors de la suppression: " . $e->getMessage();
    }
}

// Gestion de la recherche
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if (!empty($search)) {
    $query = $pdo->prepare("SELECT * FROM client WHERE 
                          nom LIKE :search OR 
                          prenom LIKE :search OR 
                          email LIKE :search OR 
                          telephone LIKE :search");
    $query->execute([':search' => "%$search%"]);
} else {
    $query = $pdo->query("SELECT * FROM client");
}
$clients = $query->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="card-body">
    <form method="GET" action="" class="search-form">
        <input type="hidden" name="page" value="client">
        <input type="text" name="search" class="recherche" placeholder="Entrez votre recherche ici" value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="rech">Rechercher</button>
    </form>

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
                            <td>
                                <?php if (!empty($client['photoPasseport'])): ?>
                                    <img src="<?= htmlspecialchars($client['photoPasseport']) ?>" alt="Photo Passeport" class="client-image">
                                <?php else: ?>
                                    <span>Aucune photo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($client['copieCarteIdentite'])): ?>
                                    <img src="<?= htmlspecialchars($client['copieCarteIdentite']) ?>" alt="Carte Identité" class="client-image">
                                <?php else: ?>
                                    <span>Aucune carte</span>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <button class="btn-modifier" onclick="openEditModal(<?= $client['idClient'] ?>)">Modifier</button>
                                <form method="POST" action="" style="display:inline;">
                                    <input type="hidden" name="delete_id" value="<?= $client['idClient'] ?>">
                                    <button type="submit" class="btn-supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce client ?');">Supprimer</button>
                                </form>
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

    <!-- Modale de modification -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Modifier le client</h2>
            <div id="editFormContainer">
            </div>
        </div>
    </div>
</div>

<script>
// Fonctions pour la modale
function openEditModal(clientId) {
    // Charger le formulaire de modification via AJAX
    fetch(`actions/get_client_form.php?id=${clientId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('editFormContainer').innerHTML = html;
            document.getElementById('editModal').style.display = 'block';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors du chargement du formulaire');
        });
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

// Fermer la modale quand on clique en dehors
window.onclick = function(event) {
    const modal = document.getElementById('editModal');
    if (event.target == modal) {
        closeEditModal();
    }
}
</script>