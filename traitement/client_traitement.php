<?php
include '../db/db.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? null;
    $prenom = $_POST['prenom'] ?? null;
    $adresse = $_POST['adresse'] ?? null;
    $telephone = $_POST['telephone'] ?? null;
    $email = $_POST['email'] ?? null;

    $photoPasseport = $_FILES['photoPasseport'] ?? null;
    $copieCarteIdentite = $_FILES['copieCarteIdentite'] ?? null;

    if ($photoPasseport && $copieCarteIdentite) {
        $photoPath = '../photos/' . basename($photoPasseport['name']);
        $idCardPath = '../photos/' . basename($copieCarteIdentite['name']);

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

            $message = "Client enregistré avec succès !";
        } else {
            $message = "Erreur lors de l'upload des fichiers.";
        }
    } else {
        $message = "Veuillez fournir tous les fichiers nécessaires.";
    }
} else {
    $message = "Aucune donnée reçue.";
}

session_start();
$_SESSION['message'] = $message;
header('Location: http://localhost/examen/?page=client');
exit();
?>
