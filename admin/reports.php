<?php
require_once '../includes/auth.php';
require_once '../config.php';
require_once '../includes/functions.php';

requireRole(['InCharge']);

$pageTitle   = "Reports";
$headerColor = "#1565c0";
$sidebarFile = "../includes/sidebar_admin.php";

// Default to current month
$requestedMonth = $_GET['month'] ?? '';
$month = is_string($requestedMonth) ? $requestedMonth : '';

if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
    $month = date('Y-m');
}
$reportDate = DateTime::createFromFormat('!Y-m-d', $month . '-01');
$monthLabel = $reportDate->format('F Y');

$stmt = $pdo->prepare("
    SELECT DATE(registered_at) as day, COUNT(*) as total
    FROM patients
    WHERE DATE_FORMAT(registered_at, '%Y-%m') = ?
    GROUP BY DATE(registered_at)
    ORDER BY day
");
$stmt->execute([$month]);
$attendance = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT d.drug_code, d.drug_name, SUM(p.quantity) as total_dispensed
    FROM prescriptions p
    JOIN drugs d ON p.drug_code = d.drug_code
    JOIN consultations c ON p.consultation_id = c.consultation_id
    WHERE DATE_FORMAT(c.consultation_date, '%Y-%m') = ?
    GROUP BY d.drug_code, d.drug_name
    ORDER BY total_dispensed DESC
");
$stmt->execute([$month]);
$consumption = $stmt->fetchAll();

if (($_GET['download'] ?? '') === 'csv') {
    $csvSafe = static function ($value) {
        $value = (string)$value;
        if (preg_match('/^\s*[=+\-@]/', $value)) {
            return "'" . $value;
        }
        return $value;
    };

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="monthly-report-' . $month . '.csv"');
    echo "\xEF\xBB\xBF";
    $output = fopen('php://output', 'w');
    if ($output === false) {
        throw new RuntimeException('Unable to open CSV output stream.');
    }

    fputcsv($output, ['Patient Attendance Report - ' . $monthLabel]);
    fputcsv($output, ['Date', 'New Patients Registered']);
    $totalNew = 0;
    foreach ($attendance as $row) {
        $totalNew += (int)$row['total'];
        fputcsv($output, [date('d M Y', strtotime($row['day'])), (int)$row['total']]);
    }
    fputcsv($output, ['Total', $totalNew]);
    fputcsv($output, []);
    fputcsv($output, ['Drug Consumption Report - ' . $monthLabel]);
    fputcsv($output, ['Drug Code', 'Drug Name', 'Total Quantity Dispensed']);
    foreach ($consumption as $row) {
        fputcsv($output, [
            $csvSafe($row['drug_code']),
            $csvSafe($row['drug_name']),
            (int)$row['total_dispensed']
        ]);
    }
    fclose($output);
    exit;
}

include '../includes/layout_start.php';
?>

<div class="card">
    <h2>Monthly Reports</h2>

    <form method="GET" style="margin:20px 0; display:flex; gap:10px; align-items:center;">
        <label for="report-month">Select Month:</label>
        <input id="report-month" type="month" name="month" value="<?= e($month) ?>">
        <button type="submit" class="btn">Generate</button>
        <button type="submit" name="download" value="csv" class="btn">Download CSV</button>
    </form>
</div>

<!-- Patient Attendance Report -->
<div class="card">
    <h3>Patient Attendance Report - <?= e($monthLabel) ?></h3>

    <?php if ($attendance): ?>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>New Patients Registered</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $totalNew = 0;
                foreach ($attendance as $row): 
                    $totalNew += $row['total'];
                ?>
                    <tr>
                        <td><?= date('d M Y', strtotime($row['day'])) ?></td>
                        <td><?= $row['total'] ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr style="font-weight:bold; background:#f5f5f5;">
                    <td>Total</td>
                    <td><?= $totalNew ?></td>
                </tr>
            </tbody>
        </table>
    <?php else: ?>
        <p style="color:#666;">No patient registrations in this month.</p>
    <?php endif; ?>
</div>

<!-- Drug Consumption Report -->
<div class="card">
    <h3>Drug Consumption Report - <?= e($monthLabel) ?></h3>

    <?php if ($consumption): ?>
        <table>
            <thead>
                <tr>
                    <th>Drug Code</th>
                    <th>Drug Name</th>
                    <th>Total Quantity Dispensed</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($consumption as $row): ?>
                    <tr>
                        <td><?= e($row['drug_code']) ?></td>
                        <td><?= e($row['drug_name']) ?></td>
                        <td><?= $row['total_dispensed'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="color:#666;">No drugs were dispensed in this month.</p>
    <?php endif; ?>
</div>

<?php include '../includes/layout_end.php'; ?>