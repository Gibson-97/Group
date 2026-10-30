<?php
require_once '../includes/auth.php';
require_once '../config.php';
require_once '../includes/functions.php';

requireRole(['InCharge']);

$pageTitle   = "Admin Dashboard";
$headerColor = "#1565c0";
$sidebarFile = "../includes/sidebar_admin.php";

include '../includes/layout_start.php';
?>

<div class="card">
    <h2>Welcome, <?= e(currentUser()['full_name']) ?>!</h2>
    <p>You have full access to the NCE Clinic system.</p>
</div>

<div class="stats-grid">
    <?php
    $totalPatients = $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
    $totalConsults = $pdo->query("SELECT COUNT(*) FROM consultations")->fetchColumn();
    $lowStock      = $pdo->query("SELECT COUNT(*) FROM drugs WHERE current_quantity <= reorder_level")->fetchColumn();
    $expiring      = $pdo->query("SELECT COUNT(*) FROM drugs WHERE expiry_date <= DATE_ADD(CURDATE(), INTERVAL 60 DAY)")->fetchColumn();
    ?>
    <div class="stat-box">
        <h3><?= $totalPatients ?></h3>
        <p>Total Patients</p>
    </div>
    <div class="stat-box">
        <h3><?= $totalConsults ?></h3>
        <p>Consultations</p>
    </div>
    <div class="stat-box">
        <h3><?= $lowStock ?></h3>
        <p>Drugs Needing Reorder</p>
    </div>
    <div class="stat-box">
        <h3><?= $expiring ?></h3>
        <p>Expiring Soon / Expired</p>
    </div>
</div>

<div class="card">
    <h3>Drugs Needing Attention</h3>
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Name</th>
                <th>Qty</th>
                <th>Reorder Level</th>
                <th>Stock Status</th>
                <th>Expiry Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $pdo->query("SELECT * FROM drugs ORDER BY current_quantity ASC");
            while ($drug = $stmt->fetch()) {
                $status = checkStockLevel($drug['current_quantity'], $drug['reorder_level']);
                $expiry = checkExpiryStatus($drug['expiry_date']);

                if ($status === 'REORDER' || $expiry['status'] !== 'OK') {
                    echo "<tr>
                        <td>{$drug['drug_code']}</td>
                        <td>" . e($drug['drug_name']) . "</td>
                        <td>{$drug['current_quantity']}</td>
                        <td>{$drug['reorder_level']}</td>
                        <td><span class='badge " . ($status === 'REORDER' ? 'badge-danger' : 'badge-success') . "'>$status</span></td>
                        <td><span class='badge badge-{$expiry['class']}'>{$expiry['text']}</span></td>
                    </tr>";
                }
            }
            ?>
        </tbody>
    </table>
</div>

<?php include '../includes/layout_end.php'; ?>