<?php
include '../db/db.php';

// Déterminer l'action à effectuer
$action = $_GET['action'] ?? 'list';
$filter = $_GET['filter'] ?? 'en_attente';

// Traitement des formulaires
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        // Validation des données
        $errors = [];
        $data = [
            'matricule' => htmlspecialchars(trim($_POST['matricule'] ?? '')),
            'nom' => htmlspecialchars(trim($_POST['nom'] ?? '')),
            'prenom' => htmlspecialchars(trim($_POST['prenom'] ?? '')),
            'email' => filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL),
            'telephone' => htmlspecialchars(trim($_POST['telephone'] ?? '')),
            'role' => htmlspecialchars(trim($_POST['role'] ?? '')),
            'actif' => isset($_POST['actif']) ? 1 : 0
        ];

        // Vérification des champs obligatoires
        foreach (['matricule', 'nom', 'prenom', 'email', 'role'] as $field) {
            if (empty($data[$field])) {
                $errors[] = "Le champ " . ucfirst($field) . " est requis";
            }
        }

        // Traitement du mot de passe
        if (empty($_POST['password'])) {
            $errors[] = "Le mot de passe est requis";
        } else {
            $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }

        // Traitement de la photo et enregistrement das le dossier photos
        if (!empty($_FILES['photo']['name'])) {
            $uploadDir = '../phots/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = uniqid() . '_' . basename($_FILES['photo']['name']);
            $targetFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
                $data['photo'] = $fileName;
            } else {
                $errors[] = "Erreur lors du téléchargement de la photo";
            }
        }

        // Si pas d'erreurs, insertion en base
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO employe 
                    (matricule, nom, prenom, email, telephone, role, password, actif, photo, date_creation) 
                    VALUES (:matricule, :nom, :prenom, :email, :telephone, :role, :password, :actif, :photo, NOW())");
                
                $stmt->execute($data);
                
                $_SESSION['message'] = [
                    'type' => 'success',
                    'text' => 'Employé ajouté avec succès!'
                ];
                
            } catch (PDOException $e) {
                $errors[] = "Erreur lors de l'ajout: " . $e->getMessage();
            }
        }
    } 
    elseif ($action === 'edit') {
        $id = filter_var($_POST['id_employe'] ?? 0, FILTER_VALIDATE_INT);
        $errors = [];
        $data = [
            'id_employe' => $id,
            'matricule' => htmlspecialchars(trim($_POST['matricule'] ?? '')),
            'nom' => htmlspecialchars(trim($_POST['nom'] ?? '')),
            'prenom' => htmlspecialchars(trim($_POST['prenom'] ?? '')),
            'email' => filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL),
            'telephone' => htmlspecialchars(trim($_POST['telephone'] ?? '')),
            'role' => htmlspecialchars(trim($_POST['role'] ?? '')),
            'actif' => isset($_POST['actif']) ? 1 : 0
        ];

        // Vérification des champs obligatoires
        foreach (['matricule', 'nom', 'prenom', 'email', 'role'] as $field) {
            if (empty($data[$field])) {
                $errors[] = "Le champ " . ucfirst($field) . " est requis";
            }
        }

        // Traitement du mot de passe (optionnel en modification)
        if (!empty($_POST['password'])) {
            $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $passwordUpdate = ", password = :password";
        } else {
            $passwordUpdate = "";
        }

        // Traitement de la photo
        if (!empty($_FILES['photo']['name'])) {
            $uploadDir = '../photos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = uniqid() . '_' . basename($_FILES['photo']['name']);
            $targetFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
                $data['photo'] = $fileName;
                $photoUpdate = ", photo = :photo";
                
                // Supprimer l'ancienne photo si elle existe
                if (!empty($_POST['old_photo'])) {
                    @unlink($uploadDir . $_POST['old_photo']);
                }
            } else {
                $errors[] = "Erreur lors du téléchargement de la photo";
            }
        } else {
            $photoUpdate = "";
        }

        // Si pas d'erreurs, mise à jour en base
        if (empty($errors)) {
            try {
                $query = "UPDATE employe SET 
                    matricule = :matricule, 
                    nom = :nom, 
                    prenom = :prenom, 
                    email = :email, 
                    telephone = :telephone, 
                    role = :role, 
                    actif = :actif
                    {$passwordUpdate}
                    {$photoUpdate}
                    WHERE id_employe = :id_employe";
                
                $stmt = $pdo->prepare($query);
                $stmt->execute($data);
                
                $_SESSION['message'] = [
                    'type' => 'success',
                    'text' => 'Employé modifié avec succès!'
                ];

            } catch (PDOException $e) {
                $errors[] = "Erreur lors de la modification: " . $e->getMessage();
            }
        }
    }
}

// Switch principal pour les différentes vues de l'administrateur pour la filtration
switch ($action) {
    case 'add':
        displayAddForm($errors ?? []);
        break;
        
    case 'edit':
        $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        if ($id) {
            $stmt = $pdo->prepare("SELECT * FROM employe WHERE id_employe = ?");
            $stmt->execute([$id]);
            $employe = $stmt->fetch();
            
            if ($employe) {
                displayEditForm($employe, $errors ?? []);
            } else {
                header('Location: ?page=employes');// en cas d'erreur on retour sur la page employe
                exit;
            }
        }
        break;
        
    case 'view':
        $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        if ($id) {
            $stmt = $pdo->prepare("SELECT * FROM employe WHERE id_employe = ?"); //selection de l'employer en filtra
            $stmt->execute([$id]);
            $employe = $stmt->fetch();
            
            if ($employe) {
                displayEmployeModal($employe);
                displayEmployesList();
            }
        }
        break;
        
    default:
        displayEmployesList();
}

function displayEmployesList() {
    global $pdo, $filter;
    
    // Requête pour compter les employés en attente
    $pendingQuery = "SELECT COUNT(*) as count FROM employe WHERE actif = 0";
    $pendingResult = $pdo->query($pendingQuery)->fetch();
    $pendingEmployes = $pendingResult['count'];
    
    // Requête principale pour filtre l'employe des le lancement  
    $query = "SELECT * FROM employe";
    if ($filter === 'en_attente') {
        $query .= " WHERE actif = 0";
    } elseif ($filter === 'actifs') {
        $query .= " WHERE actif = 1";
    }
    $query .= " ORDER BY date_creation DESC";
    $employes = $pdo->query($query)->fetchAll();
    ?>
    
    <div class="card">
        <div class="card-header">
            <h2>Gestion des employés</h2>
            <div class="filter-options d-flex align-items-center">
                <div class="btn-group" role="group">
                    <a href="?page=employes&filter=en_attente" class="btn btn-sm btn-outline-primary <?= $filter === 'en_attente' ? 'active' : '' ?>">
                        En attente
                        <?php if ($pendingEmployes > 0): ?>
                            <span class="badge bg-danger ms-1"><?= $pendingEmployes ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="?page=employes&filter=actifs" class="btn btn-sm btn-outline-primary <?= $filter === 'actifs' ? 'active' : '' ?>">Actifs</a>
                </div>
                <a href="?page=employes&action=add" class="btn btn-sm btn-primary ms-3">
                    <i class="bi bi-plus-lg"></i> Ajouter
                </a>
            </div>
        </div>
        
        <div class="card-body">
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?= $_SESSION['message']['type'] ?> alert-dismissible fade show">
                    <?= $_SESSION['message']['text'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>
            
            <!-- affichage des element filtre sur le tableau -->

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Matricule</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Contact</th>
                            <th>Rôle</th>
                            <th>Date création</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employes as $employe): 
                            $statut = $employe['actif'] ? 'actif' : 'en_attente';
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($employe['matricule']) ?></td>
                            <td><?= htmlspecialchars($employe['nom']) ?></td>
                            <td><?= htmlspecialchars($employe['prenom']) ?></td>
                            <td>
                                <div><?= htmlspecialchars($employe['email']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($employe['telephone']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($employe['role']) ?></td>
                            <td><?= date('d/m/Y', strtotime($employe['date_creation'])) ?></td>
                            <td>
                                <span class="badge bg-<?= $statut === 'actif' ? 'success' : 'warning' ?>">
                                    <?= ucfirst($statut) ?>
                                </span>
                            </td>
                            <td class="text-nowrap">
                                <?php if (!$employe['actif']): ?>
                                    <button class="btn btn-sm btn-success" onclick="activateEmploye(<?= $employe['id_employe'] ?>)">
                                        <i class="bi bi-check-lg"></i> Activer
                                    </button>
                                <?php else: ?>
                                    <a href="?page=employes&action=edit&id=<?= $employe['id_employe'] ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i> Modifier
                                    </a>
                                    <button class="btn btn-sm btn-warning" onclick="deactivateEmploye(<?= $employe['id_employe'] ?>)">
                                        <i class="bi bi-x-lg"></i> Désactiver
                                    </button>
                                <?php endif; ?>
                                
                                <!-- Bouton Détails - désactivé pour les employés en attente -->
                                <?php if ($employe['actif']): ?>
                                    <a href="?page=employes&action=view&id=<?= $employe['id_employe'] ?>" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i> Détails
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-info" disabled title="Non disponible pour les employés en attente">
                                        <i class="bi bi-eye"></i> Détails
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
}

function displayAddForm($errors) {
    ?>
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">Ajouter un employé</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="?page=employes&action=add" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="matricule" class="form-label">Matricule *</label>
                            <input type="text" class="form-control" id="matricule" name="matricule" 
                                   value="<?= htmlspecialchars($_POST['matricule'] ?? '') ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom *</label>
                            <input type="text" class="form-control" id="nom" name="nom" 
                                   value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="prenom" class="form-label">Prénom *</label>
                            <input type="text" class="form-control" id="prenom" name="prenom" 
                                   value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="telephone" class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" id="telephone" name="telephone" 
                                   value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="role" class="form-label">Rôle *</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="">Sélectionner un rôle</option>
                                <option value="admin" <?= ($_POST['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrateur</option>
                                <option value="agent" <?= ($_POST['role'] ?? '') === 'agent' ? 'selected' : '' ?>>Gestionnaire</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe *</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="photo" class="form-label">Photo</label>
                            <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="actif" name="actif" <?= isset($_POST['actif']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="actif">Activer le compte immédiatement</label>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="?page=employes" class="btn btn-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php
}

function displayEditForm($employe, $errors) {
    ?>
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">Modifier l'employé</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="?page=employes&action=edit" enctype="multipart/form-data">
                <input type="hidden" name="id_employe" value="<?= $employe['id_employe'] ?>">
                <?php if (!empty($employe['photo'])): ?>
                    <input type="hidden" name="old_photo" value="<?= htmlspecialchars($employe['photo']) ?>">
                <?php endif; ?>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="matricule" class="form-label">Matricule *</label>
                            <input type="text" class="form-control" id="matricule" name="matricule" 
                                   value="<?= htmlspecialchars($_POST['matricule'] ?? $employe['matricule']) ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom *</label>
                            <input type="text" class="form-control" id="nom" name="nom" 
                                   value="<?= htmlspecialchars($_POST['nom'] ?? $employe['nom']) ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="prenom" class="form-label">Prénom *</label>
                            <input type="text" class="form-control" id="prenom" name="prenom" 
                                   value="<?= htmlspecialchars($_POST['prenom'] ?? $employe['prenom']) ?>" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($_POST['email'] ?? $employe['email']) ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="telephone" class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" id="telephone" name="telephone" 
                                   value="<?= htmlspecialchars($_POST['telephone'] ?? $employe['telephone']) ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="role" class="form-label">Rôle *</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="admin" <?= ($_POST['role'] ?? $employe['role']) === 'admin' ? 'selected' : '' ?>>Administrateur</option>
                                <option value="gestionnaire" <?= ($_POST['role'] ?? $employe['role']) === 'gestionnaire' ? 'selected' : '' ?>>Gestionnaire</option>
                                <option value="caissier" <?= ($_POST['role'] ?? $employe['role']) === 'caissier' ? 'selected' : '' ?>>Caissier</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="password" class="form-label">Nouveau mot de passe</label>
                            <input type="password" class="form-control" id="password" name="password">
                            <small class="text-muted">Laisser vide pour ne pas modifier</small>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="photo" class="form-label">Photo</label>
                            <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                            <?php if (!empty($employe['photo'])): ?>
                                <div class="mt-2">
                                    <img src="../uploads/employes/<?= htmlspecialchars($employe['photo']) ?>" alt="Photo employé" width="80" class="img-thumbnail">
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="actif" name="actif" 
                                   <?= ($_POST['actif'] ?? $employe['actif']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="actif">Compte actif</label>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="?page=employes" class="btn btn-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary">Mettre à jour</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php
}

function displayEmployeModal($employe) {
    ?>
    <!-- Modal pour les détails de l'employé -->
    <div class="modal fade" id="employeModal" tabindex="-1" aria-labelledby="employeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="employeModalLabel">Détails de l'employé</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <div class="mb-3">
                                <img src="<?= !empty($employe['photo']) ? '../uploads/employes/' . htmlspecialchars($employe['photo']) : 'assets/img/default-user.png' ?>" 
                                     class="img-thumbnail rounded-circle" width="150" alt="Photo employé">
                            </div>
                            <h4><?= htmlspecialchars($employe['prenom'] . ' ' . htmlspecialchars($employe['nom'])) ?></h4>
                            <span class="badge bg-<?= $employe['actif'] ? 'success' : 'warning' ?>">
                                <?= $employe['actif'] ? 'Actif' : 'En attente' ?>
                            </span>
                        </div>
                        <div class="col-md-8">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <h6>Matricule</h6>
                                    <p><?= htmlspecialchars($employe['matricule']) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Rôle</h6>
                                    <p><?= htmlspecialchars($employe['role']) ?></p>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <h6>Email</h6>
                                    <p><?= htmlspecialchars($employe['email']) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Téléphone</h6>
                                    <p><?= htmlspecialchars($employe['telephone']) ?></p>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <h6>Date de création</h6>
                                    <p><?= date('d/m/Y H:i', strtotime($employe['date_creation'])) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Dernière connexion</h6>
                                    <p><?= $employe['dernier_connexion'] ? date('d/m/Y H:i', strtotime($employe['dernier_connexion'])) : 'Jamais connecté' ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <a href="?page=employes&action=edit&id=<?= $employe['id_employe'] ?>" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> Modifier
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Afficher automatiquement la modal quand elle est chargée
    document.addEventListener('DOMContentLoaded', function() {
        var modal = new bootstrap.Modal(document.getElementById('employeModal'));
        modal.show();
    });
    </script>
    <?php
}
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function activateEmploye(id) {
    if (confirm('Voulez-vous vraiment activer cet employé ?')) {
        window.location.href = 'actions/activate_employe.php?id=' + id;
    }
}

function deactivateEmploye(id) {
    if (confirm('Voulez-vous vraiment désactiver cet employé ?')) {
        window.location.href = 'actions/deactivate_employe.php?id=' + id;
    }
}
</script>