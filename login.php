<?php
// ============================================
// User Login Page
// Electricity Complaint Management System
// ============================================

session_start();
include 'config/db.php';
/** @var mysqli $conn Database connection from config/db.php */

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header('Location: admin/dashboard.php');
    } elseif ($_SESSION['role'] == 'agent') {
        header('Location: agent/dashboard.php');
    } else {
        header('Location: user/dashboard.php');
    }
    exit();
}

$error = '';

if (isset($_POST['login'])) {
    $email = strtolower(sanitizeInput($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (empty($email)) {
        $error = "Email is required!";
    } elseif (empty($password)) {
        $error = "Password is required!";
    } else {
        $query = "SELECT id, name, email, password, role, status FROM users
                 WHERE LOWER(email) = '" . mysqli_real_escape_string($conn, $email) . "'
                 LIMIT 1";

        $result = executeQuery($query, $conn);

        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            $password_matches = password_verify($password, $user['password']) || md5($password) === $user['password'];

            if (!$password_matches) {
                $error = "Invalid email or password!";
            } elseif ($user['status'] !== 'active') {
                $error = "Your account has been deactivated. Please contact support.";
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['login_time'] = time();

                if ($user['role'] == 'admin') {
                    header('Location: admin/dashboard.php');
                } elseif ($user['role'] == 'agent') {
                    header('Location: agent/dashboard.php');
                } else {
                    header('Location: user/dashboard.php');
                }
                exit();
            }
        } else {
            $error = "Invalid email or password!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PowerDesk Electricity Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --navy: #101b2d;
            --blue: #2563eb;
            --sky: #23b7d9;
            --gold: #f5c542;
            --muted: #71839a;
            --border: #dbe5ef;
        }
        * { box-sizing: border-box; }
        body {
            background:
                radial-gradient(circle at 18% 18%, rgba(35, 183, 217, 0.22), transparent 28%),
                linear-gradient(135deg, #101b2d 0%, #14365c 50%, #eef3f8 50%, #eef3f8 100%);
            color: #0f172a;
            font-family: "Segoe UI", Arial, sans-serif;
            margin: 0;
            min-height: 100vh;
        }
        .portal-shell {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            min-height: 100vh;
        }
        .portal-left {
            color: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 34px 48px;
        }
        .brand-row {
            align-items: center;
            display: flex;
            gap: 14px;
        }
        .brand-logo {
            align-items: center;
            background: var(--blue);
            border-radius: 12px;
            display: inline-flex;
            font-size: 20px;
            font-weight: 900;
            height: 48px;
            justify-content: center;
            width: 48px;
        }
        .brand-row strong {
            display: block;
            font-size: 20px;
        }
        .brand-row span {
            color: #9fc3e7;
            display: block;
            font-size: 12px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .hero-copy {
            max-width: 650px;
        }
        .hero-copy small {
            color: var(--gold);
            display: block;
            font-weight: 900;
            margin-bottom: 12px;
            text-transform: uppercase;
        }
        .hero-copy h1 {
            font-size: clamp(36px, 5vw, 62px);
            font-weight: 900;
            line-height: 1.03;
            margin-bottom: 18px;
        }
        .hero-copy p {
            color: #c8d7e8;
            font-size: 18px;
            line-height: 1.7;
            margin-bottom: 28px;
        }
        .service-points {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(3, minmax(140px, 1fr));
        }
        .service-point {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 14px;
            padding: 16px;
        }
        .service-point strong {
            display: block;
            font-size: 20px;
        }
        .service-point span {
            color: #a9bdd5;
            font-size: 13px;
        }
        .portal-footer {
            color: #9fb4cc;
            font-size: 13px;
        }
        .portal-right {
            align-items: center;
            display: flex;
            justify-content: center;
            padding: 34px;
        }
        .login-container {
            background: rgba(255,255,255,0.96);
            border: 1px solid rgba(219, 229, 239, 0.9);
            border-radius: 24px;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.22);
            max-width: 480px;
            padding: 34px;
            width: 100%;
        }
        .login-container h2 {
            color: var(--navy);
            font-size: 30px;
            font-weight: 900;
            margin: 0 0 8px;
        }
        .login-subtitle {
            color: var(--muted);
            margin-bottom: 24px;
        }
        .security-strip {
            align-items: center;
            background: #f8fbfe;
            border: 1px solid var(--border);
            border-left: 5px solid var(--gold);
            border-radius: 14px;
            display: flex;
            gap: 12px;
            margin-bottom: 22px;
            padding: 14px;
        }
        .security-strip strong {
            display: block;
            font-size: 14px;
        }
        .security-strip span {
            color: var(--muted);
            font-size: 13px;
        }
        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            color: var(--navy);
            font-size: 13px;
            font-weight: 900;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .form-group input {
            border: 1px solid var(--border);
            border-radius: 12px;
            font-size: 14px;
            padding: 13px 14px;
            transition: all 0.2s ease;
            width: 100%;
        }
        .form-group input:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
            outline: none;
        }
        .btn-login {
            background: linear-gradient(135deg, var(--blue), var(--sky));
            border: none;
            border-radius: 12px;
            color: white;
            cursor: pointer;
            font-weight: 900;
            margin-top: 10px;
            padding: 13px;
            width: 100%;
        }
        .btn-login:hover { filter: brightness(0.95); }
        .alert {
            border-radius: 12px;
            margin-bottom: 18px;
        }
        .register-link {
            margin-top: 20px;
            text-align: center;
        }
        .register-link a {
            color: var(--blue);
            font-weight: 800;
            text-decoration: none;
        }
        .demo-credentials {
            background: #f8fbfe;
            border: 1px solid var(--border);
            border-radius: 16px;
            font-size: 13px;
            margin-top: 20px;
            padding: 16px;
        }
        .demo-credentials h5 {
            color: var(--navy);
            font-size: 15px;
            font-weight: 900;
            margin-bottom: 10px;
        }
        .demo-grid {
            display: grid;
            gap: 8px;
        }
        .demo-item {
            background: #ffffff;
            border-radius: 10px;
            padding: 10px;
        }
        .demo-item strong { color: var(--blue); }
        @media (max-width: 980px) {
            .portal-shell { grid-template-columns: 1fr; }
            .portal-left {
                gap: 36px;
                min-height: auto;
                padding: 28px;
            }
            .portal-right { padding: 20px; }
            .service-points { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="portal-shell">
        <section class="portal-left">
            <div class="brand-row">
                <div class="brand-logo">PD</div>
                <div>
                    <strong>PowerDesk</strong>
                    <span>Government Electricity Portal</span>
                </div>
            </div>

            <div class="hero-copy">
                <small>Official Consumer Grievance Access</small>
                <h1>Reliable electricity support, one secure portal.</h1>
                <p>
                    Sign in to register complaints, manage field operations, monitor agents,
                    and track electricity service resolution with role-based access.
                </p>

                <div class="service-points">
                    <div class="service-point">
                        <strong>24x7</strong>
                        <span>Complaint assistance</span>
                    </div>
                    <div class="service-point">
                        <strong>1912</strong>
                        <span>Emergency helpline</span>
                    </div>
                    <div class="service-point">
                        <strong>AI</strong>
                        <span>Smart categorization</span>
                    </div>
                </div>
            </div>

            <div class="portal-footer">
                State Electricity Distribution Service | Secure role-based login
            </div>
        </section>

        <section class="portal-right">
            <div class="login-container">
                <h2>Sign in</h2>
                <p class="login-subtitle">Enter your registered credentials to continue.</p>

                <div class="security-strip">
                    <div class="brand-logo" style="width: 38px; height: 38px; border-radius: 10px; font-size: 14px;">ID</div>
                    <div>
                        <strong>Role-based secure access</strong>
                        <span>Admin, agent, and consumer portals are routed automatically.</span>
                    </div>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login.php">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="name@electricity.com" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter password" required>
                    </div>

                    <button type="submit" name="login" class="btn-login">Access Portal</button>
                </form>

                <div class="register-link">
                    New consumer? <a href="register.php">Register account</a>
                </div>

                <div class="demo-credentials">
                    <h5>Demo Access</h5>
                    <div class="demo-grid">
                        <div class="demo-item"><strong>Admin:</strong> admin@electricity.com / 123</div>
                        <div class="demo-item"><strong>Agent:</strong> billing@electricity.com / 123</div>
                        <div class="demo-item"><strong>Customer:</strong> customer@electricity.com / 123</div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
