<?php
// ============================================
// User Registration Page
// Electricity Complaint Management System
// ============================================

session_start();
include 'config/db.php';
/** @var mysqli $conn Database connection from config/db.php */

if (isset($_SESSION['user_id'])) {
    header('Location: user/dashboard.php');
    exit();
}

$error = '';
$success = '';

if (isset($_POST['register'])) {
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = strtolower(sanitizeInput($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = sanitizeInput($_POST['phone'] ?? '');

    if (empty($name)) {
        $error = "Name is required!";
    } elseif (empty($email)) {
        $error = "Email is required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } elseif (empty($password)) {
        $error = "Password is required!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters!";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (empty($phone)) {
        $error = "Phone number is required!";
    } elseif (!preg_match('/^[0-9]{10,15}$/', $phone)) {
        $error = "Phone number must be 10-15 digits!";
    } else {
        $check_email = "SELECT id FROM users WHERE email = '" . mysqli_real_escape_string($conn, $email) . "'";
        $result = executeQuery($check_email, $conn);

        if (mysqli_num_rows($result) > 0) {
            $error = "Email already exists! Please use a different email or login.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $insert_query = "INSERT INTO users (name, email, password, phone, role, status)
                            VALUES (
                                '" . mysqli_real_escape_string($conn, $name) . "',
                                '" . mysqli_real_escape_string($conn, $email) . "',
                                '" . mysqli_real_escape_string($conn, $hashed_password) . "',
                                '" . mysqli_real_escape_string($conn, $phone) . "',
                                'customer',
                                'active'
                            )";

            if (executeQuery($insert_query, $conn)) {
                $success = "Registration successful! Redirecting to login...";
                header("refresh:2;url=login.php");
            } else {
                $error = "Registration failed! Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - PowerDesk Electricity Portal</title>
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
                radial-gradient(circle at 20% 15%, rgba(35, 183, 217, 0.22), transparent 28%),
                linear-gradient(135deg, #101b2d 0%, #14365c 48%, #eef3f8 48%, #eef3f8 100%);
            color: #0f172a;
            font-family: "Segoe UI", Arial, sans-serif;
            margin: 0;
            min-height: 100vh;
        }
        .portal-shell {
            display: grid;
            grid-template-columns: 0.9fr 1.1fr;
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
        .hero-copy small {
            color: var(--gold);
            display: block;
            font-weight: 900;
            margin-bottom: 12px;
            text-transform: uppercase;
        }
        .hero-copy h1 {
            font-size: clamp(34px, 4vw, 54px);
            font-weight: 900;
            line-height: 1.05;
            margin-bottom: 18px;
        }
        .hero-copy p {
            color: #c8d7e8;
            font-size: 17px;
            line-height: 1.7;
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
        .register-container {
            background: rgba(255,255,255,0.96);
            border: 1px solid rgba(219, 229, 239, 0.9);
            border-radius: 24px;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.18);
            max-width: 720px;
            padding: 34px;
            width: 100%;
        }
        .register-container h2 {
            color: var(--navy);
            font-size: 30px;
            font-weight: 900;
            margin: 0 0 8px;
        }
        .register-subtitle {
            color: var(--muted);
            margin-bottom: 24px;
        }
        .form-grid {
            display: grid;
            gap: 16px;
            grid-template-columns: 1fr 1fr;
        }
        .form-group {
            margin-bottom: 16px;
        }
        .form-group.full {
            grid-column: 1 / -1;
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
            width: 100%;
        }
        .form-group input:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
            outline: none;
        }
        .btn-register {
            background: linear-gradient(135deg, var(--blue), var(--sky));
            border: none;
            border-radius: 12px;
            color: white;
            cursor: pointer;
            font-weight: 900;
            padding: 13px;
            width: 100%;
        }
        .login-link {
            margin-top: 20px;
            text-align: center;
        }
        .login-link a {
            color: var(--blue);
            font-weight: 800;
            text-decoration: none;
        }
        .alert {
            border-radius: 12px;
        }
        .notice-box {
            background: #f8fbfe;
            border: 1px solid var(--border);
            border-left: 5px solid var(--gold);
            border-radius: 14px;
            color: #43576d;
            margin-bottom: 22px;
            padding: 14px;
        }
        @media (max-width: 980px) {
            .portal-shell {
                grid-template-columns: 1fr;
            }
            .portal-left {
                gap: 32px;
                padding: 28px;
            }
            .portal-right {
                padding: 20px;
            }
            .form-grid {
                grid-template-columns: 1fr;
            }
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
                    <span>Consumer Registration</span>
                </div>
            </div>

            <div class="hero-copy">
                <small>Electricity Consumer Access</small>
                <h1>Create your complaint portal account.</h1>
                <p>
                    Register as a consumer to submit electricity complaints, track ticket
                    progress, use AI assistance, and communicate with support staff.
                </p>
            </div>

            <div class="portal-footer">Government Electricity Consumer Service Portal</div>
        </section>

        <section class="portal-right">
            <div class="register-container">
                <h2>Register</h2>
                <p class="register-subtitle">Create a secure consumer account for complaint services.</p>

                <div class="notice-box">
                    Use your active email and phone number. These details help support staff contact you about complaint updates.
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="register.php">
                    <div class="form-grid">
                        <div class="form-group full">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" placeholder="Enter your full name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" placeholder="name@example.com" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" placeholder="10-15 digit phone number" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" placeholder="Minimum 6 characters" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm password" required>
                        </div>
                    </div>

                    <button type="submit" name="register" class="btn-register">Create Consumer Account</button>
                </form>

                <div class="login-link">
                    Already registered? <a href="login.php">Login here</a>
                </div>
            </div>
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
