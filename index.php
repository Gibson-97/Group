<?php
session_start();
require_once 'config.php';

// If already logged in, redirect to correct dashboard
if (isset($_SESSION['user'])) {
    redirectByRole($_SESSION['user']['role']);
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        $hashed = hash('sha256', $password);

        $stmt = $pdo->prepare("SELECT user_id, username, full_name, role 
                               FROM users 
                               WHERE username = ? AND password_hash = ? AND is_active = 1");
        $stmt->execute([$username, $hashed]);
        $user = $stmt->fetch();

        if ($user) {
            // Login successful
            $_SESSION['user'] = [
                'user_id'   => $user['user_id'],
                'username'  => $user['username'],
                'full_name' => $user['full_name'],
                'role'      => $user['role']
            ];

            // Update last login
            $update = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
            $update->execute([$user['user_id']]);

            // Redirect according to role
            redirectByRole($user['role']);
        } else {
            $error = "Invalid username or password.";
        }
    }
}

// Function to redirect based on role
function redirectByRole($role) {
    switch ($role) {
        case 'InCharge':
            header("Location: admin/dashboard.php");
            break;
        case 'Clinician':
            header("Location: clinician/dashboard.php");
            break;
        case 'PharmacyAssistant':
            header("Location: pharmacy/dashboard.php");
            break;
        default:
            header("Location: index.php");
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NCE Clinic - Login</title>
    <link rel="stylesheet" href="fontawesome/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #c9cacc, #3571cc);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
            color: #1565c0;
            margin-bottom: 10px;
        }
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #333;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 15px;
            transition: border-color 0.3s;
        }
        .password-field {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .password-field input[type="password"],
        .password-field input[type="text"] {
            flex: 1;
            min-width: 0;
        }
        .password-toggle {
            width: auto;
            flex: 0 0 auto;
            padding: 12px;
            background: #666;
        }
        .password-toggle:hover {
            background: #555;
        }
        input:focus {
            outline: none;
            border-color: #1565c0;
        }
        button {
            width: 100%;
            padding: 14px;
            background: #1565c0;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #0d47a1;
        }
        .error {
            background: #ffebee;
            color: #c62828;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }
        .demo-accounts {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 13px;
            color: #666;
        }
        .demo-accounts strong {
            color: #333;
        }
    </style>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <h2><i class="fas fa-clinic-medical"></i> NCE Clinic</h2>
        <p class="subtitle">Patient Registration & Pharmacy System</p>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-field">
                    <input type="password" id="password" name="password" required>
                    <button class="password-toggle" type="button" aria-label="Show password" aria-pressed="false">
                        <i class="fas fa-eye" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <button type="submit"><i class="fas fa-sign-in-alt"></i> Login</button>
        </form>

    </div>
    <script>
        document.querySelectorAll('.password-toggle').forEach((button) => {
            button.addEventListener('click', () => {
                const passwordInput = button.parentElement.querySelector('input');
                const showingPassword = passwordInput.type === 'password';
                passwordInput.type = showingPassword ? 'text' : 'password';
                button.setAttribute('aria-label', showingPassword ? 'Hide password' : 'Show password');
                button.setAttribute('aria-pressed', String(showingPassword));
                const icon = button.querySelector('i');
                icon.classList.toggle('fa-eye', !showingPassword);
                icon.classList.toggle('fa-eye-slash', showingPassword);
            });
        });
    </script>
</body>
</html>