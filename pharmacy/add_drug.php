<?php
require_once '../includes/auth.php';
require_once '../config.php';
require_once '../includes/functions.php';

requireRole(['PharmacyAssistant', 'InCharge']);

$pageTitle   = "Add / Update Drug";
$headerColor = "#ef6c00";
$sidebarFile = "../includes/sidebar_pharmacy.php";

$success = $error = "";
$drug = [
    'drug_code' => '',
    'drug_name' => '',
    'current_quantity' => '',
    'reorder_level' => '',
    'expiry_date' => ''
];

// If editing existing drug
if (isset($_GET['code'])) {
    $stmt = $pdo->prepare("SELECT * FROM drugs WHERE drug_code = ?");
    $stmt->execute([$_GET['code']]);
    $existing = $stmt->fetch();
    if ($existing) {
        $drug = $existing;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $drug_code        = strtoupper(trim($_POST['drug_code'] ?? ''));
    $drug_name        = trim($_POST['drug_name'] ?? '');
    $current_quantity = (int)($_POST['current_quantity'] ?? 0);
    $reorder_level    = (int)($_POST['reorder_level'] ?? 0);
    $expiry_date      = $_POST['expiry_date'] ?? '';

    if (empty($drug_code) || empty($drug_name) || empty($expiry_date)) {
        $error = "Drug Code, Name and Expiry Date are required.";
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO drugs (drug_code, drug_name, current_quantity, reorder_level, expiry_date)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    drug_name = VALUES(drug_name),
                    current_quantity = VALUES(current_quantity),
                    reorder_level = VALUES(reorder_level),
                    expiry_date = VALUES(expiry_date)
            ");
            $stmt->execute([$drug_code, $drug_name, $current_quantity, $reorder_level, $expiry_date]);

            $success = "Drug saved successfully!";
            
            // Refresh data
            $drug = [
                'drug_code' => $drug_code,
                'drug_name' => $drug_name,
                'current_quantity' => $current_quantity,
                'reorder_level' => $reorder_level,
                'expiry_date' => $expiry_date
            ];
        } catch (Exception $e) {
            $error = "Error saving drug: " . $e->getMessage();
        }
    }
}

include '../includes/layout_start.php';
?>

<div class="card">
    <h2><?= isset($_GET['code']) ? 'Edit Drug' : 'Add New Drug' ?></h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" style="margin-top: 20px;">
        <div class="form-group">
            <label>Drug Code *</label>
            <input type="text" name="drug_code" required 
                   value="<?= e($drug['drug_code']) ?>" 
                   <?= isset($_GET['code']) ? 'readonly' : '' ?>
                   placeholder="e.g. AMX500">
        </div>

        <div class="form-group">
            <label>Drug Name *</label>
            <input type="text" name="drug_name" required value="<?= e($drug['drug_name']) ?>">
        </div>

        <div class="form-group">
            <label>Current Quantity *</label>
            <input type="number" name="current_quantity" required min="0" 
                   value="<?= e($drug['current_quantity']) ?>">
        </div>

        <div class="form-group">
            <label>Reorder Level *</label>
            <input type="number" name="reorder_level" required min="0" 
                   value="<?= e($drug['reorder_level']) ?>">
            <small style="color:#666;">System will show REORDER when stock ≤ this number</small>
        </div>

        <div class="form-group">
            <label>Expiry Date *</label>
            <input type="date" name="expiry_date" required value="<?= e($drug['expiry_date']) ?>">
        </div>

        <button type="submit" class="btn">Save Drug</button>
        <a href="stock.php" class="btn" style="background:#666;">Back to Stock</a>
    </form>
</div>

<?php include '../includes/layout_end.php'; ?>