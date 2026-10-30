<?php
require_once '../includes/auth.php';
require_once '../config.php';
require_once '../includes/functions.php';

requireRole(['PharmacyAssistant', 'InCharge']);

$pageTitle   = "Dispense Drug";
$headerColor = "#ef6c00";
$sidebarFile = "../includes/sidebar_pharmacy.php";

$success = $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $drug_code = strtoupper(trim($_POST['drug_code'] ?? ''));
    $quantity  = (int)($_POST['quantity'] ?? 0);
    $notes     = trim($_POST['notes'] ?? '');

    if (empty($drug_code) || $quantity <= 0) {
        $error = "Please select a drug and enter a valid quantity.";
    } else {
        try {
            $pdo->beginTransaction();

            // Get current stock
            $stmt = $pdo->prepare("SELECT drug_name, current_quantity, reorder_level FROM drugs WHERE drug_code = ?");
            $stmt->execute([$drug_code]);
            $drug = $stmt->fetch();

            if (!$drug) {
                throw new Exception("Drug not found.");
            }

            if ($drug['current_quantity'] < $quantity) {
                throw new Exception("Insufficient stock. Available: " . $drug['current_quantity']);
            }

            $new_qty = $drug['current_quantity'] - $quantity;

            // Update stock
            $stmt = $pdo->prepare("UPDATE drugs SET current_quantity = ? WHERE drug_code = ?");
            $stmt->execute([$new_qty, $drug_code]);

            $status = checkStockLevel($new_qty, $drug['reorder_level']);

            $pdo->commit();

            $success = "Successfully dispensed <strong>{$quantity}</strong> of <strong>{$drug['drug_name']}</strong>.<br>
                        New stock level: <strong>{$new_qty}</strong> → <span class='badge " . 
                        ($status === 'REORDER' ? 'badge-danger' : 'badge-success') . "'>{$status}</span>";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}

// Get all drugs for dropdown
$drugs = $pdo->query("SELECT drug_code, drug_name, current_quantity FROM drugs ORDER BY drug_name")->fetchAll();

include '../includes/layout_start.php';
?>

<div class="card">
    <h2>Dispense Drug</h2>
    <p style="color:#666; margin-bottom:20px;">Use this form to manually dispense drugs and automatically update stock.</p>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Select Drug *</label>
            <select name="drug_code" required>
                <option value="">-- Select Drug --</option>
                <?php foreach ($drugs as $d): ?>
                    <option value="<?= e($d['drug_code']) ?>">
                        <?= e($d['drug_code']) ?> - <?= e($d['drug_name']) ?> (Stock: <?= $d['current_quantity'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Quantity to Dispense *</label>
            <input type="number" name="quantity" required min="1" value="1">
        </div>

        <div class="form-group">
            <label>Notes (optional)</label>
            <textarea name="notes" rows="2" placeholder="e.g. Dispensed to Patient ID 12"></textarea>
        </div>

        <button type="submit" class="btn">Dispense Drug</button>
        <a href="stock.php" class="btn" style="background:#666;">Back to Stock</a>
    </form>
</div>

<?php include '../includes/layout_end.php'; ?>