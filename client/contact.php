<?php
require_once '../db/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'client') {
    header("Location: ../index.php");
    exit();
}

$success = false;
$error = null;

// Traitement du formulaire de contact
if (isset($_POST['envoyer_message'])) {
    $sujet = htmlspecialchars(trim($_POST['sujet']));
    $message = htmlspecialchars(trim($_POST['message']));
    $client_id = $_SESSION['user']['id'];
    $client_email = $_SESSION['user']['email'];
    $client_nom = $_SESSION['user']['nom'] . ' ' . $_SESSION['user']['prenom'];

    // Validation
    if (empty($sujet) || empty($message)) {
        $error = "Tous les champs sont obligatoires";
    } elseif (strlen($message) < 20) {
        $error = "Votre message doit contenir au moins 20 caractères";
    } else {
        try {
        
            $success = true;
            
        } catch (PDOException $e) {
            $error = "Erreur lors de l'envoi du message: " . $e->getMessage();
        }
    }
}
?>

<div class="dashboard-layout">
    <main class="main-content">
        
        <?php if ($success): ?>
            <div class="success-message">
                <i class='bx bxs-check-circle'></i>
                <div>
                    <h3>Message envoyé avec succès</h3>
                    <p>Nous avons bien reçu votre message et y répondrons dans les plus brefs délais.</p>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error" style="margin-bottom: 20px;">
                <i class='bx bxs-error-circle'></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>
        
        <div class="contact-container">
            <!-- Formulaire de contact -->
            <div class="card">
                <div class="card-header">
                    <h2>Envoyer un message</h2>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-group">
                            <label for="sujet">Sujet</label>
                            <select id="sujet" name="sujet" class="form-control" required>
                                <option value="" disabled selected>Sélectionnez un sujet</option>
                                <option value="question" <?= isset($_POST['sujet']) && $_POST['sujet'] == 'question' ? 'selected' : '' ?>>Question générale</option>
                                <option value="probleme" <?= isset($_POST['sujet']) && $_POST['sujet'] == 'probleme' ? 'selected' : '' ?>>Problème technique</option>
                                <option value="reclamation" <?= isset($_POST['sujet']) && $_POST['sujet'] == 'reclamation' ? 'selected' : '' ?>>Réclamation</option>
                                <option value="suggestion" <?= isset($_POST['sujet']) && $_POST['sujet'] == 'suggestion' ? 'selected' : '' ?>>Suggestion</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Votre message</label>
                            <textarea id="message" name="message" class="form-control" rows="5" required
                                        placeholder="Décrivez votre demande en détail..."><?= isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '' ?></textarea>
                        </div>
                        
                        <button type="submit" name="envoyer_message" class="btn btn-primary">
                            <i class='bx bx-send'></i> Envoyer le message
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Coordonnées de l'agence -->
            <div class="card">
                <div class="card-header">
                    <h2>Coordonnées de votre agence</h2>
                </div>
                <div class="card-body">
                    <div class="contact-info">
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class='bx bx-phone'></i>
                            </div>
                            <div class="contact-text">
                                <h3>Téléphone</h3>
                                <p>+257 12 345 678</p>
                                <p>Service client disponible du lundi au vendredi, 8h-17h</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class='bx bx-envelope'></i>
                            </div>
                            <div class="contact-text">
                                <h3>Email</h3>
                                <p>contact@banqueafricaine.bi</p>
                                <p>Réponse garantie sous 48 heures</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class='bx bx-map'></i>
                            </div>
                            <div class="contact-text">
                                <h3>Adresse</h3>
                                <p>Avenue de l'Indépendance, Bujumbura</p>
                                <p>Ouvert du lundi au vendredi, 8h30-16h30</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="map-container">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3987.634365425769!2d29.35931461475791!3d-3.376847997522852!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zM8KwMjInMzYuNiJTIDI5wrAyMSc0Mi4xIkU!5e0!3m2!1sen!2sbi!4v1620000000000!5m2!1sen!2sbi" 
                                allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>