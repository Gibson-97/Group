<?php
$current = basename($_SERVER['PHP_SELF']);
$alertCount = (int)$pdo->query("
    SELECT
        COALESCE(SUM(CASE WHEN current_quantity <= reorder_level THEN 1 ELSE 0 END), 0)
        + COALESCE(SUM(CASE WHEN expiry_date < CURDATE() THEN 1 ELSE 0 END), 0)
        + COALESCE(SUM(
            CASE
                WHEN expiry_date >= CURDATE()
                  AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 60 DAY)
                THEN 1 ELSE 0
            END
        ), 0)
    FROM drugs
")->fetchColumn();

$current = basename($_SERVER['PHP_SELF']);
?>
<a href="dashboard.php" class="<?= $current == 'dashboard.php' ? 'active' : '' ?>"><i class="fas fa-home"></i> Dashboard</a>
<a href="patients.php" class="<?= $current == 'patients.php' ? 'active' : '' ?>"><i class="fas fa-user-injured"></i> Patients</a>
<a href="consultations.php" class="<?= $current == 'consultations.php' ? 'active' : '' ?>"><i class="fas fa-stethoscope"></i> Consultations</a>
<a href="stock.php" class="<?= $current == 'stock.php' ? 'active' : '' ?>"><i class="fas fa-pills"></i> Pharmacy Stock</a>
<a href="alerts.php" class="<?= $current == 'alerts.php' ? 'active' : '' ?>"><i class="fas fa-bell"></i> Alerts <sup class="sidebar-alert-count" aria-label="<?= $alertCount ?> active alerts"><?= $alertCount ?></sup></a>
<a href="reports.php" class="<?= $current == 'reports.php' ? 'active' : '' ?>"><i class="fas fa-chart-bar"></i> Reports</a>
<a href="users.php" class="<?= $current == 'users.php' ? 'active' : '' ?>"><i class="fas fa-users"></i> Users</a>