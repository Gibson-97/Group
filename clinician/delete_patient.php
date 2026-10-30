<?php
require_once '../includes/auth.php';
require_once '../config.php';
require_once '../includes/functions.php';

requireRole(['Clinician', 'InCharge']);

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    try {
        // Optional: Check if patient has consultations
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM consultations WHERE patient_id = ?");
        $stmt->execute([$id]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            // Soft approach: You can choose to prevent deletion or still allow it
            // For now we allow deletion (CASCADE will remove related consultations if set)
        }

        $stmt = $pdo->prepare("DELETE FROM patients WHERE patient_id = ?");
        $stmt->execute([$id]);

        header("Location: patients.php?msg=deleted");
        exit;
    } catch (Exception $e) {
        die("Error deleting patient: " . $e->getMessage());
    }
} else {
    header("Location: patients.php");
    exit;
}