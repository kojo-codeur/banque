<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include '../db/db.php';
    
    $nom = $_POST['nom'] ?? null;
    $prenom = $_POST['prenom'] ?? null;
    $adresse = $_POST['adresse'] ?? null;
    $telephone = $_POST['telephone'] ?? null;
    $email = $_POST['email'] ?? null;

    $photoPasseport = $_FILES['photoPasseport'] ?? null;
    $copieCarteIdentite = $_FILES['copieCarteIdentite'] ?? null;

    if ($photoPasseport && $copieCarteIdentite) {
        $uploadDir = '../photos/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $photoPath = $uploadDir . basename($photoPasseport['name']);
        $idCardPath = $uploadDir . basename($copieCarteIdentite['name']);

        if (move_uploaded_file($photoPasseport['tmp_name'], $photoPath) &&
            move_uploaded_file($copieCarteIdentite['tmp_name'], $idCardPath)) {
            
            $sql = "INSERT INTO Client (nom, prenom, adresse, telephone, email, photoPasseport, copieCarteIdentite)
                    VALUES (:nom, :prenom, :adresse, :telephone, :email, :photoPasseport, :copieCarteIdentite)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':adresse' => $adresse,
                ':telephone' => $telephone,
                ':email' => $email,
                ':photoPasseport' => $photoPath,
                ':copieCarteIdentite' => $idCardPath
            ]);

            $_SESSION['message'] = "Client enregistré avec succès !";
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = "Erreur lors de l'upload des fichiers.";
            $_SESSION['message_type'] = 'error';
        }
    } else {
        $_SESSION['message'] = "Veuillez fournir tous les fichiers nécessaires.";
        $_SESSION['message_type'] = 'error';
    }
    
    exit();
}

// Récupération des messages pour affichage
$message = $_SESSION['message'] ?? null;
$message_type = $_SESSION['message_type'] ?? null;
unset($_SESSION['message']);
unset($_SESSION['message_type']);
?>





<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2 class="h4 mb-0"><i class="fas fa-user-plus me-2"></i>Ajouter un Client</h2>
                </div>
                
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-<?= $message_type === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($message) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data" id="clientForm">
                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nom" name="nom" required>
                        </div>

                        <div class="mb-3">
                            <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="prenom" name="prenom" required>
                        </div>

                        <div class="mb-3">
                            <label for="adresse" class="form-label">Adresse <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="adresse" name="adresse" rows="3" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="telephone" class="form-label">Téléphone <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="telephone" name="telephone" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="date_naissance" class="form-label">Date de naissance</label>
                            <input type="date" class="form-control" id="date_naissance" name="date_naissance">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Sexe</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="sexe" id="sexe_m" value="M">
                                <label class="form-check-label" for="sexe_m">Masculin</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="sexe" id="sexe_f" value="F">
                                <label class="form-check-label" for="sexe_f">Féminin</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="photoPasseport" class="form-label">Photo passeport <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="photoPasseport" name="photoPasseport" accept="image/*" required>
                            <small class="text-muted">Formats acceptés: JPG, PNG (Max 2MB)</small>
                            <div class="mt-2" id="passeportPreview"></div>
                        </div>

                        <div class="mb-3">
                            <label for="copieCarteIdentite" class="form-label">Copie Carte d'Identité <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="copieCarteIdentite" name="copieCarteIdentite" accept=".pdf,.jpg,.png" required>
                            <small class="text-muted">Formats acceptés: PDF, JPG, PNG (Max 5MB)</small>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="conditions" name="conditions" required>
                            <label class="form-check-label" for="conditions">Je certifie que les informations fournies sont exactes <span class="text-danger">*</span></label>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Enregistrer
                            </button>
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="fas fa-eraser me-2"></i>Effacer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Prévisualisation de la photo passeport
    document.getElementById('photoPasseport').addEventListener('change', function(e) {
        const preview = document.getElementById('passeportPreview');
        preview.innerHTML = '';
        
        if (this.files && this.files[0]) {
            const file = this.files[0];
            const fileType = file.type;
            const validImageTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            
            if (!validImageTypes.includes(fileType)) {
                alert('Veuillez sélectionner une image valide (JPG, PNG)');
                this.value = '';
                return;
            }
            
            if (file.size > 2 * 1024 * 1024) {
                alert('La taille du fichier ne doit pas dépasser 2MB');
                this.value = '';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.maxWidth = '200px';
                img.style.maxHeight = '200px';
                img.className = 'img-thumbnail mt-2';
                preview.appendChild(img);
            }
            reader.readAsDataURL(file);
        }
    });

    // Validation du formulaire
    document.getElementById('clientForm').addEventListener('submit', function(e) {        
        const email = document.getElementById('email').value;
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            alert('Veuillez entrer une adresse email valide');
            e.preventDefault();
            return false;
        }
        
        return true;
    });
</script>


