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
?>
<a href="dashboard.php" class="<?= $current == 'dashboard.php' ? 'active' : '' ?>"><i class="fas fa-home"></i> Dashboard</a>
<a href="stock.php" class="<?= $current == 'stock.php' ? 'active' : '' ?>"><i class="fas fa-boxes-stacked"></i> View Stock </a>
<a href="add_drug.php" class="<?= $current == 'add_drug.php' ? 'active' : '' ?>"><i class="fas fa-pills"></i> Add / Update Drug</a>
<a href="dispense.php" class="<?= $current == 'dispense.php' ? 'active' : '' ?>"><i class="fas fa-prescription-bottle-medical"></i> Dispense Drug</a>
<a href="alerts.php" class="<?= $current == 'alerts.php' ? 'active' : '' ?>"><i class="fas fa-bell"></i> Alerts <sup class="sidebar-alert-count" aria-label="<?= $alertCount ?> active alerts"><?= $alertCount ?></sup></a>