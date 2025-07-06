<?php
require_once '../db/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

// Traitement du formulaire de paramètres
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ici vous pouvez ajouter la logique pour mettre à jour les paramètres
    // Exemple: modification des limites de virement, taux d'intérêt, etc.
    $success_message = "Paramètres mis à jour avec succès";
}
?>


<div class="content-g">
    <div class="card">
        <div class="card-header">
            <h2>Paramètres généraux</h2>
        </div>
        <div class="card-body">
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success_message) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="settings-form">
                <div class="form-group">
                    <label for="taux_interet">Taux d'intérêt (%)</label>
                    <input type="number" step="0.01" id="taux_interet" name="taux_interet" value="2.5" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="limite_virement">Limite de virement quotidien (FBU)</label>
                    <input type="number" id="limite_virement" name="limite_virement" value="5000000" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="frais_compte">Frais de maintenance de compte (FBU/mois)</label>
                    <input type="number" id="frais_compte" name="frais_compte" value="1000" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="maintenance_mode">Mode maintenance</label>
                    <select id="maintenance_mode" name="maintenance_mode" class="form-control">
                        <option value="0">Désactivé</option>
                        <option value="1">Activé</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2>Sécurité</h2>
        </div>
        <div class="card-body">
            <form method="POST" class="settings-form">
                <div class="form-group">
                    <label for="session_timeout">Délai d'expiration de session (minutes)</label>
                    <input type="number" id="session_timeout" name="session_timeout" value="30" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="login_attempts">Nombre maximum de tentatives de connexion</label>
                    <input type="number" id="login_attempts" name="login_attempts" value="3" class="form-control">
                </div>
                
                <button type="submit" class="btn btn-primary">Mettre à jour la sécurité</button>
            </form>
        </div>
    </div>
</div>