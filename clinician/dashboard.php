<?php
require_once '../includes/auth.php';
require_once '../config.php';
require_once '../includes/functions.php';

requireRole(['Clinician', 'InCharge']);

$totalPatients = (int)$pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
$totalConsultations = (int)$pdo->query("SELECT COUNT(*) FROM consultations")->fetchColumn();
$consultationsToday = (int)$pdo->query("
    SELECT COUNT(*)
    FROM consultations
    WHERE DATE(consultation_date) = CURDATE()
")->fetchColumn();
$drugsNeedingReorder = (int)$pdo->query("
    SELECT COUNT(*)
    FROM drugs
    WHERE current_quantity <= reorder_level
")->fetchColumn();

$recentConsultations = $pdo->query("
    SELECT c.consultation_date, p.full_name, c.diagnosis
    FROM consultations c
    JOIN patients p ON c.patient_id = p.patient_id
    ORDER BY c.consultation_date DESC
    LIMIT 8
")->fetchAll();

$pageTitle   = "Clinician Dashboard";
$headerColor = "#2e7d32";
$sidebarFile = "../includes/sidebar_clinician.php";

include '../includes/layout_start.php';
?>

<div class="card">
    <h2>Welcome, <?= e(currentUser()['full_name']) ?>!</h2>
    <p>You can register patients, search records, and record consultations.</p>
</div>

<div class="stats-grid">
    <div class="stat-box">
        <h3><?= number_format($totalPatients) ?></h3>
        <p>Registered Patients</p>
    </div>
    <div class="stat-box">
        <h3><?= number_format($totalConsultations) ?></h3>
        <p>Total Consultations</p>
    </div>
    <div class="stat-box">
        <h3><?= number_format($consultationsToday) ?></h3>
        <p>Consultations Today</p>
    </div>
    <div class="stat-box">
        <h3><?= number_format($drugsNeedingReorder) ?></h3>
        <p>Drugs Needing Reorder</p>
    </div>
</div>

<div class="card">
    <h3>Recent Consultations</h3>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Patient</th>
                <th>Diagnosis</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($recentConsultations): ?>
                <?php foreach ($recentConsultations as $row): ?>
                    <tr>
                        <td><?= e(date('d M Y H:i', strtotime($row['consultation_date']))) ?></td>
                        <td><?= e($row['full_name']) ?></td>
                        <td><?= e($row['diagnosis']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">No consultations have been recorded yet.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/layout_end.php'; ?>