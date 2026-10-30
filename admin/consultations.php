<?php
require_once '../includes/auth.php';
require_once '../config.php';
require_once '../includes/functions.php';

requireRole(['InCharge']);

$pageTitle   = "Consultations";
$headerColor = "#1565c0";
$sidebarFile = "../includes/sidebar_admin.php";

$stmt = $pdo->query("
    SELECT c.consultation_id, c.consultation_date, c.diagnosis,
           p.full_name AS patient_name, p.patient_id,
           u.full_name AS recorded_by
    FROM consultations c
    JOIN patients p ON c.patient_id = p.patient_id
    JOIN users u ON c.recorded_by = u.user_id
    ORDER BY c.consultation_date DESC
");
$consultations = $stmt->fetchAll();

include '../includes/layout_start.php';
?>

<div class="card">
    <h2>All Consultations (<?= count($consultations) ?>)</h2>

    <?php if (count($consultations) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Patient</th>
                    <th>Diagnosis</th>
                    <th>Recorded By</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($consultations as $c): ?>
                    <tr>
                        <td><?= date('d M Y H:i', strtotime($c['consultation_date'])) ?></td>
                        <td><?= e($c['patient_name']) ?> (ID: <?= $c['patient_id'] ?>)</td>
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