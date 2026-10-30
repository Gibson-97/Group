<?php
require_once '../includes/auth.php';
require_once '../config.php';
require_once '../includes/functions.php';

requireRole(['Clinician', 'InCharge']);

$pageTitle   = "Patients";
$headerColor = "#2e7d32";
$sidebarFile = "../includes/sidebar_clinician.php";

$success = $error = "";

// ===================== SEARCH / LIST PATIENTS =====================
$search = trim($_GET['q'] ?? '');
$params = [];
$sql = "SELECT * FROM patients";

if ($search !== '') {
    if (is_numeric($search)) {
        $sql .= " WHERE patient_id = ?";
        $params[] = $search;
    } else {
        $sql .= " WHERE full_name LIKE ? OR contact LIKE ? OR address LIKE ?";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
}

$sql .= " ORDER BY registered_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$patients = $stmt->fetchAll();

include '../includes/layout_start.php';
?>

<style>
    .action-buttons a {
        display: inline-block;
        padding: 6px 10px;
        border-radius: 4px;
        color: #fff;
        text-decoration: none;
        font-size: 14px;
    }
    .action-buttons a:hover {
        opacity: 0.85;
    }
</style>

<?php if ($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<!-- SEARCH + PATIENT LIST -->
<div class="card" style="padding: 20px; margin-top: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>All Registered Patients (<?= count($patients) ?>)</h2>
        
        <!-- Search Bar -->
        <form method="GET" style="display: flex; gap: 10px;">
            <input type="text" name="q" placeholder="Search by name, ID, contact or address..." 
                   value="<?= e($search) ?>" style="width: 320px;">
            <button type="submit" class="btn">Search</button>
            <?php if ($search): ?>
                <a href="patients.php" class="btn" style="background:#666;">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (count($patients) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Date of Birth</th>
                    <th>Sex</th>
                    <th>Contact</th>
                    <th>Address</th>
                    <th>Registered On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($patients as $p): ?>
                    <tr>
                        <td><strong><?= $p['patient_id'] ?></strong></td>
                        <td><?= e($p['full_name']) ?></td>
                        <td><?= e($p['date_of_birth'] ?? '-') ?></td>
                        <td><?= e($p['sex'] ?? '-') ?></td>
                        <td><?= e($p['contact'] ?? '-') ?></td>
                        <td><?= e($p['address'] ?? '-') ?></td>
                        <td><?= date('d M Y', strtotime($p['registered_at'])) ?></td>
                        <td class="action-buttons" style="display: flex; gap: 6px;">
                            <a href="view_patient.php?id=<?= $p['patient_id'] ?>" class="btn" style="background:#1976d2;"><i class="fas fa-eye" text="View"></i></a>
                            <a href="edit_patient.php?id=<?= $p['patient_id'] ?>" class="btn" style="background:#f57c00;"><i class="fas fa-edit" text="Edit"></i></a>
                            <a href="record_consultation.php?patient_id=<?= (int)$p['patient_id'] ?>" class="btn" style="background:#4caf50;" aria-label="Record consultation for <?= e($p['full_name']) ?>" title="Record Consultation"><i class="fas fa-stethoscope" aria-hidden="true"></i></a>
                            <a href="delete_patient.php?id=<?= $p['patient_id'] ?>" class="btn" style="background:#c62828;" onclick="return confirm('Are you sure you want to delete this patient?');"><i class="fas fa-trash" text="Delete"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="padding: 20px 0; color: #666;">
            <?= $search ? 'No patients found matching your search.' : 'No patients registered yet.' ?>
        </p>
    <?php endif; ?>
</div>

<?php include '../includes/layout_end.php'; ?>