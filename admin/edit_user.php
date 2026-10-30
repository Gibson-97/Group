<?php
require_once '../includes/auth.php';
require_once '../config.php';
require_once '../includes/functions.php';

requireRole(['InCharge']);

$id = (int)($_GET['id'] ?? 0);
$success = $error = "";

// Fetch user
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    die("User not found.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $role      = $_POST['role'] ?? '';
    $password  = trim($_POST['password'] ?? '');

    if (empty($full_name) || empty($role)) {
        $error = "Full name and role are required.";
    } else {
        try {
            if (!empty($password)) {
                // Update with new password
                if (strlen($password) < 6) {
                    throw new Exception("Password must be at least 6 characters.");
                }
                $hashed = hash('sha256', $password);
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, role = ?, password_hash = ? WHERE user_id = ?");
                $stmt->execute([$full_name, $role, $hashed, $id]);
            } else {
                // Update without changing password
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, role = ? WHERE user_id = ?");
                $stmt->execute([$full_name, $role, $id]);
            }

            $success = "User updated successfully!";
            
            // Refresh data
            $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch();
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$pageTitle   = "Edit User";
$headerColor = "#1565c0";
$sidebarFile = "../includes/sidebar_admin.php";

include '../includes/layout_start.php';
?>

<div class="card">
    <h2>Edit User #<?= $user['user_id'] ?></h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" style="margin-top: 20px;">
        <div class="form-group">
            <label>Username</label>
            <input type="text" value="<?= e($user['username']) ?>" readonly style="background:#f5f5f5;">
            <small style="color:#666;">Username cannot be changed</small>
        </div>

        <div class="form-group">
            <label>Full Name *</label>
            <input type="text" name="full_name" required value="<?= e($user['full_name']) ?>">
        </div>

        <div class="form-group">
            <label>Role *</label>
            <select name="role" required>
                <option value="Clinician" <?= $user['role'] === 'Clinician' ? 'selected' : '' ?>>Clinician / Nurse</option>
                <option value="PharmacyAssistant" <?= $user['role'] === 'PharmacyAssistant' ? 'selected' : '' ?>>Pharmacy Assistant</option>
                <option value="InCharge" <?= $user['role'] === 'InCharge' ? 'selected' : '' ?>>Clinic In-Charge</option>
            </select>
        </div>

        <div class="form-group">
            <label>New Password</label>
            <div style="display:flex; align-items:center; gap:8px;">
                <input id="new-password" type="password" name="password" minlength="6" placeholder="Leave blank to keep current password" autocomplete="new-password">
                <button id="toggle-password-visibility" type="button" class="btn" aria-label="Show password" aria-pressed="false" style="padding:10px 12px;">
                    <i class="fas fa-eye" aria-hidden="true"></i>
                </button>
            </div>
            <small style="color:#666;">Only fill this if you want to change the password</small>
        </div>

        <button type="submit" class="btn">Update User</button>
        <a href="users.php" class="btn" style="background:#666;">Back to Users</a>
    </form>
</div>

<script>
    const passwordInput = document.getElementById('new-password');
    const visibilityButton = document.getElementById('toggle-password-visibility');
    const visibilityIcon = visibilityButton.querySelector('i');

    visibilityButton.addEventListener('click', () => {
        const showingPassword = passwordInput.type === 'password';
        passwordInput.type = showingPassword ? 'text' : 'password';
        visibilityButton.setAttribute('aria-label', showingPassword ? 'Hide password' : 'Show password');
        visibilityButton.setAttribute('aria-pressed', String(showingPassword));
        visibilityIcon.classList.toggle('fa-eye', !showingPassword);
        visibilityIcon.classList.toggle('fa-eye-slash', showingPassword);
    });
</script>

<?php include '../includes/layout_end.php'; ?>