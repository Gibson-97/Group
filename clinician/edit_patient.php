<?php
require_once '../includes/auth.php';
require_once '../config.php';
require_once '../includes/functions.php';

requireRole(['Clinician', 'InCharge']);

$id = (int)($_GET['id'] ?? 0);
$success = $error = "";

// Fetch patient
$stmt = $pdo->prepare("SELECT * FROM patients WHERE patient_id = ?");
$stmt->execute([$id]);
$patient = $stmt->fetch();

if (!$patient) {
    die("Patient not found.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $dob       = $_POST['dob'] ?: null;
    $sex       = $_POST['sex'] ?? null;
    $contact   = trim($_POST['contact'] ?? '') ?: null;
    $address   = trim($_POST['address'] ?? '') ?: null;

    if (empty($full_name)) {
        $error = "Full name is required.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE patients SET 
                full_name = ?, date_of_birth = ?, sex = ?, contact = ?, address = ?
                WHERE patient_id = ?");
            $stmt->execute([$full_name, $dob, $sex, $contact, $address, $id]);
            
            $success = "Patient updated successfully!";
            
            // Refresh data
            $stmt = $pdo->prepare("SELECT * FROM patients WHERE patient_id = ?");
            $stmt->execute([$id]);
            $patient = $stmt->fetch();
        } catch (Exception $e) {
            $error = "Error updating patient: " . $e->getMessage();
        }
    }
}

$pageTitle   = "Edit Patient";
$headerColor = "#2e7d32";
$sidebarFile = "../includes/sidebar_clinician.php";

include '../includes/layout_start.php';
?>

<div class="card">
    <h2>Edit Patient #<?= $patient['patient_id'] ?></h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" style="margin-top:20px;">
        <div class="form-group">
            <label>Full Name *</label>
            <input type="text" name="full_name" required value="<?= e($patient['full_name']) ?>">
        </div>

        <div class="form-group">
            <label>Date of Birth</label>
            <input type="date" name="dob" value="<?= e($patient['date_of_birth']) ?>">
        </div>

        <div class="form-group">
            <label>Sex</label>
            <select name="sex">
                <option value="">-- Select --</option>
                <option value="M" <?= $patient['sex'] === 'M' ? 'selected' : '' ?>>Male</option>
                <option value="F" <?= $patient['sex'] === 'F' ? 'selected' : '' ?>>Female</option>
            </select>
        </div>

        <div class="form-group">
            <label>Contact</label>
            <input type="text" name="contact" value="<?= e($patient['contact']) ?>">
        </div>

        <div class="form-group">
            <label>Address / Village</label>
            <input type="text" name="address" value="<?= e($patient['address']) ?>">
        </div>

        <button type="submit" class="btn">Update Patient</button>
        <a href="view_patient.php?id=<?= $patient['patient_id'] ?>" class="btn" style="background:#666;">Cancel</a>
    </form>
</div>

<?php include '../includes/layout_end.php'; ?>