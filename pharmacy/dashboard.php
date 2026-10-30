<?php
require_once '../includes/auth.php';
require_once '../config.php';
require_once '../includes/functions.php';

requireRole(['PharmacyAssistant', 'InCharge']);

$pageTitle   = "Pharmacy Dashboard";
$headerColor = "#ef6c00";
$sidebarFile = "../includes/sidebar_pharmacy.php";

include '../includes/layout_start.php';
?>

<div class="card">
    <h2>Pharmacy Stock Overview</h2>
    <p>Monitor stock levels and expiry dates. Reorder alert uses the correct rule: <strong>≤ reorder level</strong>.</p>
</div>

<div class="card">
    <h3>Current Stock & Alerts</h3>
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Drug Name</th>
                <th>Quantity</th>
                <th>Reorder Level</th>
                <th>Stock Status</th>
                <th>Expiry Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $pdo->query("SELECT * FROM drugs ORDER BY drug_name");
            while ($drug = $stmt->fetch()) {
                $stockStatus = checkStockLevel($drug['current_quantity'], $drug['reorder_level']);
                $expiry = checkExpiryStatus($drug['expiry_date']);
                $stockClass = $stockStatus === 'REORDER' ? 'badge-danger' : 'badge-success';
                ?>
                <tr>
                    <td><?= e($drug['drug_code']) ?></td>
                    <td><?= e($drug['drug_name']) ?></td>
                    <td><?= $drug['current_quantity'] ?></td>
                    <td><?= $drug['reorder_level'] ?></td>
                    <td><span class="badge <?= $stockClass ?>"><?= $stockStatus ?></span></td>
                    <td><span class="badge badge-<?= $expiry['class'] ?>"><?= $expiry['text'] ?></span></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<?php include '../includes/layout_end.php'; ?>