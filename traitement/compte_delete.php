<?php
include '../db/db.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $idCompte = $_GET['id'];

    try {
        
        $stmt = $pdo->prepare("DELETE FROM Compte WHERE idCompte = ?");
        $stmt->execute([$idCompte]);

        header("Location: http://localhost/examen/?page=listecompte");
        exit();
    } catch (PDOException $e) {
        echo "<p>Erreur de suppression : <p>" . $e->getMessage();
    }
} else {
    echo "<p>Aucun ID de compte valide fourni.<p>";
}
?>
