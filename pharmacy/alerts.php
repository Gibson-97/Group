<?php
require_once '../includes/auth.php';
require_once '../config.php';
require_once '../includes/functions.php';

requireRole(['PharmacyAssistant', 'InCharge']);

$pageTitle   = "Stock Alerts";
$headerColor = "#ef6c00";
$sidebarFile = "../includes/sidebar_pharmacy.php";

include '../includes/layout_start.php';

// Get drugs that need reorder (using correct <= rule)
$reorderDrugs = $pdo->query("
    SELECT * FROM drugs 
    WHERE current_quantity <= reorder_level 
    ORDER BY current_quantity ASC
")->fetchAll();

// Get expired and soon-to-expire drugs
$expiredDrugs = $pdo->query("
    SELECT * FROM drugs 
    WHERE expiry_date < CURDATE()
    ORDER BY expiry_date ASC
")->fetchAll();

$expiringDrugs = $pdo->query("
    SELECT * FROM drugs 
    WHERE expiry_date >= CURDATE() 
      AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 60 DAY)
    ORDER BY expiry_date ASC
")->fetchAll();
?>

<!-- ===================== REORDER ALERTS ===================== -->
<div class="card" style="border-left: 6px solid #c62828;">
    <h2 style="color:#c62828; margin-bottom:10px;">
        ⚠️ Drugs That Need Reordering (<?= count($reorderDrugs) ?>)
    </h2>
    <p style="color:#666; margin-bottom:20px;">
        These drugs have reached or gone below their reorder level.
    </p>

    <?php if (count($reorderDrugs) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Drug Name</th>
                    <th>Current Qty</th>
                    <th>Reorder Level</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reorderDrugs as $drug): ?>
                    <tr>
                        <td><strong><?= e($drug['drug_code']) ?></strong></td>
                        <td><?= e($drug['drug_name']) ?></td>
                        <td style="color:#c62828; font-weight:bold;"><?= $drug['current_quantity'] ?></td>
                        <td><?= $drug['reorder_level'] ?></td>
                        <td><span class="badge badge-danger">REORDER</span></td>
                        <td>
                            <a href="add_drug.php?code=<?= urlencode($drug['drug_code']) ?>" 
                               class="btn" style="padding:5px 10px; font-size:13px;">
                                Update Stock
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div style="padding:15px; background:#e8f5e9; border-radius:6px; color:#2e7d32;">
            ✅ No drugs currently need reordering.
        </div>
    <?php endif; ?>
</div>

<!-- ===================== EXPIRED DRUGS ===================== -->
<div class="card" style="border-left: 6px solid #b71c1c;">
    <h2 style="color:#b71c1c; margin-bottom:10px;">
        🛑 Expired Drugs (<?= count($expiredDrugs) ?>)
    </h2>

    <?php if (count($expiredDrugs) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Drug Name</th>
                    <th>Expiry Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($expiredDrugs as $drug): ?>
                    <tr>
                        <td><strong><?= e($drug['drug_code']) ?></strong></td>
                        <td><?= e($drug['drug_name']) ?></td>
                        <td><?= e($drug['expiry_date']) ?></td>
                        <td><span class="badge badge-danger">EXPIRED</span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div style="padding:15px; background:#e8f5e9; border-radius:6px; color:#2e7d32;">
            ✅ No expired drugs.
        </div>
    <?php endif; ?>
</div>

<!-- ===================== EXPIRING SOON ===================== -->
<div class="card" style="border-left: 6px solid #ef6c00;">
    <h2 style="color:#e65100; margin-bottom:10px;">
        📅 Expiring Within 60 Days (<?= count($expiringDrugs) ?>)
    </h2>

    <?php if (count($expiringDrugs) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Drug Name</th>
                    <th>Expiry Date</th>
                    <th>Days Left</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($expiringDrugs as $drug): 
                    $expiry = checkExpiryStatus($drug['expiry_date']);
                    $daysLeft = (new DateTime($drug['expiry_date']))->diff(new DateTime())->days;
                ?>
                    <tr>
                        <td><strong><?= e($drug['drug_code']) ?></strong></td>
                        <td><?= e($drug['drug_name']) ?></td>
                        <td><?= e($drug['expiry_date']) ?></td>
                        <td><?= $daysLeft ?> days</td>
                        <td><span class="badge badge-warning">EXPIRES SOON</span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div style="padding:15px; background:#e8f5e9; border-radius:6px; color:#2e7d32;">
            ✅ No drugs expiring in the next 60 days.
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/layout_end.php'; ?>