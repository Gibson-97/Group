<?php
// includes/auth.php

session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit;
}

/**
 * Check if the current user has the required role
 * Usage: requireRole(['InCharge', 'Clinician']);
 */
function requireRole($allowedRoles = []) {
    if (!in_array($_SESSION['user']['role'], $allowedRoles)) {
        // Redirect to their own dashboard
        switch ($_SESSION['user']['role']) {
            case 'InCharge':
                header("Location: ../admin/dashboard.php");
                break;
            case 'Clinician':
                header("Location: ../clinician/dashboard.php");
                break;
            case 'PharmacyAssistant':
                header("Location: ../pharmacy/dashboard.php");
                break;
            default:
                header("Location: ../index.php");
        }
        exit;
    }
}

/**
 * Get current logged in user
 */
function currentUser() {
    return $_SESSION['user'] ?? null;
}
?>