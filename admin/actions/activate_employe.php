<?php
include '../../db/db.php';
session_start();

$id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);

if ($id) {
    try {
        $stmt = $pdo->prepare("UPDATE employe SET actif = 1 WHERE id_employe = ?");
        $stmt->execute([$id]);
        
        $_SESSION['message'] = [
            'type' => 'success',
            'text' => 'Employé activé avec succès!'
        ];
    } catch (PDOException $e) {
        $_SESSION['message'] = [
            'type' => 'danger',
            'text' => 'Erreur lors de l\'activation: ' . $e->getMessage()
        ];
    }
}

header('Location: http://localhost/joelbanque/admin/dashboard.php?page=employes&filter=actifs');
exit;
