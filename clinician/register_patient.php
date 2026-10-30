<?php
require_once '../includes/auth.php';
require_once '../config.php';
require_once '../includes/functions.php';

requireRole(['Clinician', 'InCharge']);
$pageTitle = "Register Patient";
$headerColor = "#2e7d32";
$sidebarFile = "../includes/sidebar_clinician.php";

$success = $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $dob = $_POST['dob'] ?: null;
    $sex = $_POST['sex'] ?? null;
    $contact = trim($_POST['contact'] ?? '') ?: null;
    $address = trim($_POST['address'] ?? '') ?: null;

    if (empty($full_name)) {
        $error = "Full name is required.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO patients (full_name, date_of_birth, sex, contact, address) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$full_name, $dob, $sex, $contact, $address]);
            $patient_id = $pdo->lastInsertId();
            $success = "Patient registered successfully! Patient ID: <strong>$patient_id</strong>";
        } catch (Exception $e) {
            $error = "Error saving patient: " . $e->getMessage();
        }
    }
}
include '../includes/layout_start.php';
?>

<div class="card">
    <h2>Patient Registration Form</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Full Name *</label>
            <input type="text" name="full_name" required value="<?= e($_POST['full_name'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Date of Birth</label>
            <input type="date" name="dob" value="<?= e($_POST['dob'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Sex</label>
            <select name="sex">
                <option value="">-- Select --</option>
                <option value="M" <?= (($_POST['sex'] ?? '') === 'M') ? 'selected' : '' ?>>Male</option>
                <option value="F" <?= (($_POST['sex'] ?? '') === 'F') ? 'selected' : '' ?>>Female</option>
            </select>
        </div>
        <div class="form-group">
            <label>Contact Number</label>
            <input type="text" name="contact" value="<?= e($_POST['contact'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Address / Village</label>
            <input type="text" name="address" value="<?= e($_POST['address'] ?? '') ?>">
        </div>
        <button type="submit" class="btn"><i class="fas fa-user-plus"></i> Register Patient</button>
    </form>
</div>

<?php include '../includes/layout_end.php'; ?>