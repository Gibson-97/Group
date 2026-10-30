<?php
// includes/functions.php

/**
 * Corrected stock level check
 * Returns "REORDER" when current quantity <= reorder level
 */
function checkStockLevel($currentQuantity, $reorderLevel) {
    if ($currentQuantity <= $reorderLevel) {
        return "REORDER";
    }
    return "OK";
}

/**
 * Check if a drug is expired or expiring within 60 days
 */
function checkExpiryStatus($expiryDate) {
    $today = new DateTime();
    $expiry = new DateTime($expiryDate);
    $interval = $today->diff($expiry);
    $daysLeft = (int)$interval->format('%r%a'); // can be negative

    if ($daysLeft < 0) {
        return ['status' => 'EXPIRED', 'class' => 'danger', 'text' => 'EXPIRED'];
    } elseif ($daysLeft <= 60) {
        return ['status' => 'WARNING', 'class' => 'warning', 'text' => "Expires in $daysLeft days"];
    } else {
        return ['status' => 'OK', 'class' => 'success', 'text' => 'OK'];
    }
}

/**
 * Simple function to make output safe
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}
?>