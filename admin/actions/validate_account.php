<?php
include '../../db/db.php';
$id = $_GET['id'] ?? 0;
$pdo->prepare("UPDATE compte SET statut = 'actif' WHERE idCompte = ?")->execute([$id]);
header('Location: ../?page=comptes');
?>