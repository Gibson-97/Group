<?php
require_once '../includes/auth.php';
require_once '../config.php';
require_once '../includes/functions.php';

requireRole(['Clinician', 'InCharge']);
$pageTitle = "Search Patient";
$headerColor = "#2e7d32";
$sidebarFile = "../includes/sidebar_clinician.php";

$results = [];
$searchTerm = trim($_GET['q'] ?? '');

if ($searchTerm !== '') {
    if (is_numeric($searchTerm)) {
        $stmt = $pdo->prepare("SELECT * FROM patients WHERE patient_id = ?");
        $stmt->execute([$searchTerm]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM patients WHERE full_name LIKE ? ORDER BY full_name");
        $stmt->execute(["%$searchTerm%"]);
    }
    $results = $stmt->fetchAll();
}
include '../includes/layout_start.php';
?>

<div class="card">
    <h2>Search Patient by Name or ID</h2>
    <form method="GET" style="margin: 20px 0;">
        <input type="text" name="q" placeholder="Enter name or Patient ID" value="<?= e($searchTerm) ?>" required>
        <button type="submit" class="btn"><i class="fas fa-search"></i> Search</button>
    </form>

    <?php if ($searchTerm !== ''): ?>
        <?php if (count($results) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Patient ID</th>
                        <th>Full Name</th>
                        <th>Date of Birth</th>
                        <th>Sex</th>
                        <th>Contact</th>
                        <th>Address</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $p): ?>
                        <tr>
                            <td><?= e($p['patient_id']) ?></td>
                            <td><?= e($p['full_name']) ?></td>
                            <td><?= e($p['date_of_birth']) ?></td>
                            <td><?= e($p['sex']) ?></td>
                            <td><?= e($p['contact']) ?></td>
                            <td><?= e($p['address']) ?></td>
                            <td>
                                <a href="record_consultation.php?patient_id=<?= (int)$p['patient_id'] ?>" class="btn" aria-label="Record consultation for <?= e($p['full_name']) ?>">
                                    <i class="fas fa-stethoscope" aria-hidden="true"></i> Record Consultation
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="margin-top:20px; color:#c62828;">No patient found.</p>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include '../includes/layout_end.php'; ?>