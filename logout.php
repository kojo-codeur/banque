<?php
session_start();
include('db/db.php');
// $pdo->prepare("UPDATE utilisateurs SET connecter = 0 WHERE utilisateur_id = ?")
//     ->execute([$user['id']]);

session_destroy();
header("location:index.php")
?>