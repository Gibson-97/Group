<?php
require_once '../includes/auth.php';
require_once '../config.php';
require_once '../includes/functions.php';

requireRole(['InCharge']);

$pageTitle   = "All Patients";
$headerColor = "#1565c0";
$sidebarFile = "../includes/sidebar_admin.php";

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

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2>All Registered Patients (<?= count($patients) ?>)</h2>

        <form method="GET" style="display:flex; gap:10px;">
            <input type="text" name="q" placeholder="Search by name, ID, contact..." 
                   value="<?= e($search) ?>" style="width:280px;">
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
                    <th>Sex</th>
                    <th>Contact</th>
                    <th>Address</th>
                    <th>Registered On</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($patients as $p): ?>
                    <tr>
                        <td><strong><?= $p['patient_id'] ?></strong></td>
                        <td><?= e($p['full_name']) ?></td>
                        <td><?= e($p['sex'] ?? '-') ?></td>
                        <td><?= e($p['contact'] ?? '-') ?></td>
                        <td><?= e($p['address'] ?? '-') ?></td>
                        <td><?= date('d M Y', strtotime($p['registered_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="color:#666;">No patients found.</p>
    <?php endif; ?>
</div>

<?php include '../includes/layout_end.php'; ?>