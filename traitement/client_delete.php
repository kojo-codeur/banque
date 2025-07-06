<?php
include '../db/db.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $client_id = $_GET['id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM client WHERE idClient = ?");
        $stmt->execute([$client_id]);

        header("Location: http://localhost/examen/?page=listeclient");
        exit();
    } catch (PDOException $e) {
        echo "<p>Erreur de suppression : <p>" . $e->getMessage();
    }
} else {
    echo "<p>Aucun ID de client valide fourni.<p>";
}
?>
