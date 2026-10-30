<?php
require_once '../includes/auth.php';
require_once '../config.php';
require_once '../includes/functions.php';

requireRole(['PharmacyAssistant', 'InCharge']);

$pageTitle   = "Pharmacy Stock";
$headerColor = "#ef6c00";
$sidebarFile = "../includes/sidebar_pharmacy.php";
$searchTerm = trim($_GET['q'] ?? '');

if ($searchTerm !== '') {
    $stmt = $pdo->prepare("SELECT * FROM drugs WHERE drug_name LIKE ? OR drug_code LIKE ? ORDER BY drug_name ASC");
    $stmt->execute(["%$searchTerm%", "%$searchTerm%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM drugs ORDER BY drug_name ASC");
}
$drugs = $stmt->fetchAll();

include '../includes/layout_start.php';
?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h2>Pharmacy Stock & Alerts</h2>
        <a href="add_drug.php" class="btn">+ Add / Update Drug</a>
    </div>
    <p style="margin-top: 8px; color: #666;">
        Reorder alert triggers when stock quantity is <strong>≤ reorder level</strong>.
    </p>
</div>

<div class="card">
    <form method="GET" style="margin-bottom: 20px;">
        <input type="search" name="q" placeholder="Search by drug name or code" value="<?= e($searchTerm) ?>">
        <button type="submit" class="btn"><i class="fas fa-search" aria-hidden="true"></i> Search</button>
        <?php if ($searchTerm !== ''): ?>
            <a href="stock.php" class="btn" style="background:#666;">Clear</a>
        <?php endif; ?>
    </form>
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
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($drugs as $drug) {
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
                    <td>
                        <a href="add_drug.php?code=<?= urlencode($drug['drug_code']) ?>" 
                           class="btn" style="padding:6px 10px; font-size:13px; background:#0288d1;">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="dispense.php?code=<?= urlencode($drug['drug_code']) ?>" 
                           class="btn" style="padding:6px 10px; font-size:13px; background:#0288d1;">
                            <i class="fas fa-pills"></i> Dispense
                        </a>
                    </td>
                </tr>
            <?php } ?>
            <?php if (count($drugs) === 0): ?>
                <tr>
                    <td colspan="8">No drugs found<?= $searchTerm !== '' ? ' matching "' . e($searchTerm) . '"' : '' ?>.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/layout_end.php'; ?>