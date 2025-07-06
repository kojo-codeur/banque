<?php
include '../db/db.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $client_id = $_GET['id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM operation WHERE idOperation = ?");
        $stmt->execute([$client_id]);

        header("Location: http://localhost/examen/?page=listeoperation");
        exit();
    } catch (PDOException $e) {
        echo "<p>Erreur de suppression : <p>" . $e->getMessage();
    }
} else {
    echo "<p>Aucun ID de l'operation valide.<p>";
}
?>
