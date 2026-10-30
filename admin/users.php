<?php
require_once '../includes/auth.php';
require_once '../config.php';
require_once '../includes/functions.php';

requireRole(['InCharge']);

$pageTitle   = "Manage Users";
$headerColor = "#1565c0";
$sidebarFile = "../includes/sidebar_admin.php";

$success = $error = "";

// ===================== ADD NEW USER =====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username  = trim($_POST['username'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $password  = trim($_POST['password'] ?? '');
    $role      = $_POST['role'] ?? '';

    if (empty($username) || empty($full_name) || empty($password) || empty($role)) {
        $error = "All fields are required.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        try {
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            
            if ($stmt->fetch()) {
                $error = "Username already exists. Please choose another.";
            } else {
                $hashed = hash('sha256', $password);

                $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, full_name, role) VALUES (?, ?, ?, ?)");
                $stmt->execute([$username, $hashed, $full_name, $role]);

                $success = "User <strong>" . e($full_name) . "</strong> added successfully!";
            }
        } catch (Exception $e) {
            $error = "Error adding user: " . $e->getMessage();
        }
    }
}

// ===================== TOGGLE ACTIVE STATUS =====================
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $user_id = (int)$_GET['toggle'];

    // Prevent admin from deactivating themselves
    if ($user_id === (int)currentUser()['user_id']) {
        $error = "You cannot deactivate your own account.";
    } else {
        $stmt = $pdo->prepare("UPDATE users SET is_active = NOT is_active WHERE user_id = ?");
        $stmt->execute([$user_id]);
        header("Location: users.php?msg=updated");
        exit;
    }
}

$searchTerm = trim($_GET['q'] ?? '');
if ($searchTerm !== '') {
    $stmt = $pdo->prepare("
        SELECT * FROM users
        WHERE full_name LIKE ? OR username LIKE ? OR role LIKE ?
        ORDER BY role, full_name
    ");
    $searchPattern = '%' . $searchTerm . '%';
    $stmt->execute([$searchPattern, $searchPattern, $searchPattern]);
    $users = $stmt->fetchAll();
} else {
    $users = $pdo->query("SELECT * FROM users ORDER BY role, full_name")->fetchAll();
}

include '../includes/layout_start.php';
?>

<?php if ($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>
<?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
    <div class="alert alert-success">User status updated successfully.</div>
<?php endif; ?>

<!-- ===================== ADD USER FORM ===================== -->
<div class="card">
    <h2>Add New User</h2>

    <form method="POST" style="margin-top:20px;">
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="full_name" required placeholder="e.g. Mary Banda">
            </div>

            <div class="form-group">
                <label>Username *</label>
                <input type="text" name="username" required placeholder="e.g. mary">
            </div>

            <div class="form-group">
                <label>Password *</label>
                <div style="display:flex; align-items:center; gap:8px;">
                    <input id="new-user-password" type="password" name="password" required minlength="6" placeholder="Minimum 6 characters">
                    <button id="toggle-new-user-password" type="button" class="btn" aria-label="Show password" aria-pressed="false" style="padding:10px 12px;">
                        <i class="fas fa-eye" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label>Role *</label>
                <select name="role" required>
                    <option value="">-- Select Role --</option>
                    <option value="Clinician">Clinician / Nurse</option>
                    <option value="PharmacyAssistant">Pharmacy Assistant</option>
                    <option value="InCharge">Clinic In-Charge</option>
                </select>
            </div>
        </div>

        <button type="submit" name="add_user" class="btn">Add User</button>
    </form>
</div>

<script>
    const newUserPassword = document.getElementById('new-user-password');
    const newUserPasswordToggle = document.getElementById('toggle-new-user-password');
    const newUserPasswordIcon = newUserPasswordToggle.querySelector('i');

    newUserPasswordToggle.addEventListener('click', () => {
        const showingPassword = newUserPassword.type === 'password';
        newUserPassword.type = showingPassword ? 'text' : 'password';
        newUserPasswordToggle.setAttribute('aria-label', showingPassword ? 'Hide password' : 'Show password');
        newUserPasswordToggle.setAttribute('aria-pressed', String(showingPassword));
        newUserPasswordIcon.classList.toggle('fa-eye', !showingPassword);
        newUserPasswordIcon.classList.toggle('fa-eye-slash', showingPassword);
    });
</script>

<!-- ===================== USERS LIST ===================== -->
<div class="card">
    <h2>System Users (<?= count($users) ?>)</h2>

    <form method="GET" style="margin:20px 0; display:flex; gap:10px; align-items:center;">
        <label for="user-search">Search users:</label>
        <input id="user-search" type="search" name="q" placeholder="Name, username, or role" value="<?= e($searchTerm) ?>">
        <button type="submit" class="btn"><i class="fas fa-search" aria-hidden="true"></i> Search</button>
        <?php if ($searchTerm !== ''): ?>
            <a href="users.php" class="btn" style="background:#666;">Clear</a>
        <?php endif; ?>
    </form>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Username</th>
                <th>Role</th>
                <th>Status</th>
                <th>Last Login</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($users): ?>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= $u['user_id'] ?></td>
                    <td><?= e($u['full_name']) ?></td>
                    <td><?= e($u['username']) ?></td>
                    <td>
                        <?php
                        $roleLabel = match($u['role']) {
                            'Clinician'         => 'Clinician / Nurse',
                            'PharmacyAssistant' => 'Pharmacy Assistant',
                            'InCharge'          => 'Clinic In-Charge',
                            default             => $u['role']
                        };
                        echo e($roleLabel);
                        ?>
                    </td>
                    <td>
                        <?php if ($u['is_active']): ?>
                            <span class="badge badge-success">Active</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= $u['last_login'] ? date('d M Y H:i', strtotime($u['last_login'])) : 'Never' ?>
                    </td>
                    <td>
                        <?php if ($u['user_id'] != currentUser()['user_id']): ?>
                            <a href="edit_user.php?id=<?= $u['user_id'] ?>" class="btn" style="padding:5px 10px; font-size:13px;">Edit</a>
                            <a href="users.php?toggle=<?= $u['user_id'] ?>" 
                               class="btn" 
                               style="padding:5px 10px; font-size:13px; background: <?= $u['is_active'] ? '#c62828' : '#2e7d32' ?>;"
                               onclick="return confirm('Are you sure you want to change this user status?')">
                                <?= $u['is_active'] ? 'Deactivate' : 'Activate' ?>
                            </a>
                        <?php else: ?>
                            <span style="color:#999; font-size:13px;">Current User</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">No users found<?= $searchTerm !== '' ? ' matching "' . e($searchTerm) . '"' : '' ?>.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/layout_end.php'; ?>