<?php
require_once '../includes/auth.php';
require_once '../config.php';
require_once '../includes/functions.php';

requireRole(['Clinician', 'InCharge']);
$pageTitle = "Record Consultation";
$headerColor = "#2e7d32";
$sidebarFile = "../includes/sidebar_clinician.php";

$user = currentUser();
$success = $error = "";
$requestedPatientId = $_GET['patient_id'] ?? $_GET['id'] ?? '';
$selectedPatientId = filter_var($requestedPatientId, FILTER_VALIDATE_INT);
if ($selectedPatientId === false || $selectedPatientId < 1) {
    $selectedPatientId = '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = (int)($_POST['patient_id'] ?? 0);
    $diagnosis = trim($_POST['diagnosis'] ?? '');
    $drug_code = strtoupper(trim($_POST['drug_code'] ?? ''));
    $quantity = (int)($_POST['quantity'] ?? 0);

    if ($patient_id <= 0 || empty($drug_code) || $quantity <= 0) {
        $error = "Please fill all required fields correctly.";
    } else {
        try {
            $pdo->beginTransaction();

            // Check patient exists
            $stmt = $pdo->prepare("SELECT patient_id FROM patients WHERE patient_id = ?");
            $stmt->execute([$patient_id]);
            if (!$stmt->fetch()) {
                throw new Exception("Patient ID not found.");
            }

            // Check drug and stock
            $stmt = $pdo->prepare("SELECT current_quantity, reorder_level, drug_name FROM drugs WHERE drug_code = ?");
            $stmt->execute([$drug_code]);
            $drug = $stmt->fetch();

            if (!$drug) {
                throw new Exception("Drug code not found.");
            }
            if ($drug['current_quantity'] < $quantity) {
                throw new Exception("Insufficient stock. Available: " . $drug['current_quantity']);
            }

            // Insert consultation
            $stmt = $pdo->prepare("INSERT INTO consultations (patient_id, recorded_by, diagnosis) VALUES (?, ?, ?)");
            $stmt->execute([$patient_id, $user['user_id'], $diagnosis]);
            $consultation_id = $pdo->lastInsertId();

            // Insert prescription
            $stmt = $pdo->prepare("INSERT INTO prescriptions (consultation_id, drug_code, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$consultation_id, $drug_code, $quantity]);

            // Update stock
            $new_qty = $drug['current_quantity'] - $quantity;
            $stmt = $pdo->prepare("UPDATE drugs SET current_quantity = ? WHERE drug_code = ?");
            $stmt->execute([$new_qty, $drug_code]);

            $status = checkStockLevel($new_qty, $drug['reorder_level']);
            $pdo->commit();

            $success = "Consultation saved successfully!<br>
                        Drug: <strong>{$drug['drug_name']}</strong><br>
                        Quantity dispensed: $quantity<br>
                        New stock level: $new_qty → <strong>$status</strong>";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}
include '../includes/layout_start.php';
?>

<div class="card">
    <h2>New Consultation</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Patient ID *</label>
            <input type="number" name="patient_id" required min="1" value="<?= e($_POST['patient_id'] ?? $selectedPatientId) ?>">
        </div>
        <div class="form-group">
            <label>Diagnosis</label>
            <textarea name="diagnosis" rows="3"><?= e($_POST['diagnosis'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label>Drug Code *</label>
            <input type="text" name="drug_code" required placeholder="e.g. AMX500" value="<?= e($_POST['drug_code'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Quantity *</label>
            <input type="number" name="quantity" required min="1" value="<?= e($_POST['quantity'] ?? '') ?>">
        </div>
        <button type="submit" class="btn"><i class="fas fa-save"></i> Save Consultation & Update Stock</button>
    </form>
</div>

<?php include '../includes/layout_end.php'; ?>