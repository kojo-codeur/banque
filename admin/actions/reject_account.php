<?php
include '../../db/db.php';
$id = $_GET['id'] ?? 0;
$motif = $_GET['motif'] ?? '';
$pdo->prepare("UPDATE compte SET statut = 'rejeté', motif_rejet = ? WHERE idCompte = ?")->execute([$motif, $id]);
header('Location: ../?page=comptes');
?>