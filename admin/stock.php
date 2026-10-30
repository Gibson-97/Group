<?php
require_once '../includes/auth.php';
require_once '../config.php';
require_once '../includes/functions.php';

requireRole(['InCharge']);

$pageTitle   = "Pharmacy Stock";
$headerColor = "#1565c0";
$sidebarFile = "../includes/sidebar_admin.php";

include '../includes/layout_start.php';
?>

<div class="card">
    <h2>Pharmacy Stock Overview</h2>
    <p style="color:#666; margin-bottom:20px;">
        Stock status uses the rule: <strong>REORDER when quantity ≤ reorder level</strong>
    </p>

    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Drug Name</th>
                <th>Current Qty</th>
                <th>Reorder Level</th>
                <th>Stock Status</th>
                <th>Expiry Date</th>
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
                    <td><strong><?= e($drug['drug_code']) ?></strong></td>
                    <td><?= e($drug['drug_name']) ?></td>
                    <td><?= $drug['current_quantity'] ?></td>
                    <td><?= $drug['reorder_level'] ?></td>
                    <td><span class="badge <?= $stockClass ?>"><?= $stockStatus ?></span></td>
                    <td><?= e($drug['expiry_date']) ?></td>
                    <td><span class="badge badge-<?= $expiry['class'] ?>"><?= $expiry['text'] ?></span></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<?php include '../includes/layout_end.php'; ?>