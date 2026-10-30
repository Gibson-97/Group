<?php
require_once '../includes/auth.php';
require_once '../config.php';
require_once '../includes/functions.php';

requireRole(['Clinician', 'InCharge']);

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM patients WHERE patient_id = ?");
$stmt->execute([$id]);
$patient = $stmt->fetch();

if (!$patient) {
    die("Patient not found.");
}

// Get consultation history
$stmt = $pdo->prepare("
    SELECT c.consultation_id, c.consultation_date, c.diagnosis, u.full_name AS recorded_by
    FROM consultations c
    JOIN users u ON c.recorded_by = u.user_id
    WHERE c.patient_id = ?
    ORDER BY c.consultation_date DESC
");
$stmt->execute([$id]);
$consultations = $stmt->fetchAll();

$pageTitle   = "View Patient";
$headerColor = "#2e7d32";
$sidebarFile = "../includes/sidebar_clinician.php";

include '../includes/layout_start.php';
?>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h2>Patient Details</h2>
        <div>
            <a href="edit_patient.php?id=<?= $patient['patient_id'] ?>" class="btn">Edit</a>
            <a href="patients.php" class="btn" style="background:#666;">Back to List</a>
        </div>
    </div>

    <table style="margin-top:20px; max-width:700px;">
        <tr>
            <th width="200">Patient ID</th>
            <td><strong><?= $patient['patient_id'] ?></strong></td>
        </tr>
        <tr>
            <th>Full Name</th>
            <td><?= e($patient['full_name']) ?></td>
        </tr>
        <tr>
            <th>Date of Birth</th>
            <td><?= e($patient['date_of_birth'] ?? '-') ?></td>
        </tr>
        <tr>
            <th>Sex</th>
            <td><?= e($patient['sex'] ?? '-') ?></td>
        </tr>
        <tr>
            <th>Contact</th>
            <td><?= e($patient['contact'] ?? '-') ?></td>
        </tr>
        <tr>
            <th>Address / Village</th>
            <td><?= e($patient['address'] ?? '-') ?></td>
        </tr>
        <tr>
            <th>Registered On</th>
            <td><?= date('d M Y H:i', strtotime($patient['registered_at'])) ?></td>
        </tr>
    </table>
</div>

<div class="card">
    <h3>Consultation History (<?= count($consultations) ?>)</h3>

    <?php if (count($consultations) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Diagnosis</th>
                    <th>Recorded By</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($consultations as $c): ?>
                    <tr>
                        <td><?= date('d M Y H:i', strtotime($c['consultation_date'])) ?></td>
                        <td><?= e($c['diagnosis'] ?: '-') ?></td>
                        <td><?= e($c['recorded_by']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="color:#666; margin-top:15px;">No consultations recorded yet.</p>
    <?php endif; ?>
</div>

<?php include '../includes/layout_end.php'; ?>