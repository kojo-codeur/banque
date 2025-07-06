<?php
// Traitement du formulaire de paramètres
if (isset($_POST['update_settings'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $telephone = preg_replace('/[^0-9]/', '', $_POST['telephone']);
    
    try {
        $stmt = $pdo->prepare("UPDATE client SET email = ?, telephone = ? WHERE idClient = ?");
        $stmt->execute([$email, $telephone, $_SESSION['user']['id']]);
        
        // Mettre à jour la session
        $_SESSION['user']['email'] = $email;
        $_SESSION['user']['telephone'] = $telephone;
        
        $success = "Paramètres mis à jour avec succès";
    } catch (PDOException $e) {
        $error = "Erreur lors de la mise à jour: " . $e->getMessage();
    }
}
?>


<div >
    <div class="card">
        <div class="card-header">
            <h2>Informations personnelles</h2>
        </div>
        <div class="card-body">
            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <i class='bx bxs-check-circle'></i> <?= $success ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class='bx bxs-error-circle'></i> <?= $error ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="email">Adresse email</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           value="<?= htmlspecialchars($_SESSION['user']['email']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="telephone">Numéro de téléphone</label>
                    <input type="tel" id="telephone" name="telephone" class="form-control" 
                           value="<?= htmlspecialchars($_SESSION['user']['telephone']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-container">
                        <input type="checkbox" name="notification" <?= $_SESSION['user']['telephone'] ? 'checked' : '' ?>>
                        <span class="checkmark"></span>
                        Recevoir les notifications par numero de telephone
                    </label>
                </div>
                
                <button type="submit" name="update_settings" class="btn btn-primary">
                    <i class='bx bx-save'></i> Enregistrer les modifications
                </button>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2>Sécurité</h2>
        </div>
        <div class="card-body">
            <div class="security-actions">
                <a href="?page=change_password" class="btn btn-secondary">
                    <i class='bx bx-lock'></i> Changer le mot de passe
                </a>
            </div>
        </div>
    </div>
</div>
