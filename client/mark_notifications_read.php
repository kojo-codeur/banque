<?php
session_start();
require_once '../db/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'client') {
    header('HTTP/1.1 401 Unauthorized');
    exit();
}

try {
    // Marquer toutes les notifications comme lues
    $stmt = $pdo->prepare("UPDATE operation 
                          SET is_read = 1 
                          WHERE 
                              idCompteCrediteur IN (SELECT idCompte FROM compte WHERE idClient = ?)
                          AND is_read = 0");
    $stmt->execute([$_SESSION['user']['id']]);
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}