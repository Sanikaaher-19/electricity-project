<?php
// ============================================
// Landing Page
// Electricity Complaint Management System
// ============================================

session_start();

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PowerDesk Electricity Complaint Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --navy: #101b2d;
            --blue: #2563eb;
            --sky: #23b7d9;
            --gold: #f5c542;
            --muted: #9fb4cc;
            --border: rgba(255,255,255,0.12);
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            background:
                radial-gradient(circle at 70% 30%, rgba(35, 183, 217, 0.28), transparent 26%),
                linear-gradient(135deg, #101b2d 0%, #14365c 100%);
            color: #ffffff;
            font-family: "Segoe UI", Arial, sans-serif;
            min-height: 100vh;
        }
        .top-strip {
            background: rgba(255,255,255,0.08);
            border-bottom: 1px solid var(--border);
            color: #dbeafe;
            font-size: 13px;
            font-weight: 800;
            padding: 8px 0;
        }
        .top-strip .container {
            display: flex;
            justify-content: space-between;
            gap: 16px;
        }
        .navbar {
            padding: 18px 0;
        }
        .brand {
            align-items: center;
            color: #ffffff !important;
            display: flex;
            gap: 12px;
            text-decoration: none;
        }
        .brand-logo {
            align-items: center;
            background: var(--blue);
            border-radius: 12px;
            display: inline-flex;
            font-size: 19px;
            font-weight: 900;
            height: 46px;
            justify-content: center;
            width: 46px;
        }
        .brand strong {
            display: block;
            font-size: 19px;
            line-height: 1;
        }
        .brand span {
            color: #9fc3e7;
            display: block;
            font-size: 12px;
            letter-spacing: 1px;
            margin-top: 4px;
            text-transform: uppercase;
        }
        .navbar .nav-link {
            color: #dbeafe !important;
            font-weight: 800;
            margin-left: 8px;
        }
        .hero {
            align-items: center;
            display: flex;
            padding: 72px 0 56px;
        }
        .hero-grid {
            align-items: center;
            display: grid;
            gap: 48px;
            grid-template-columns: 1.05fr 0.95fr;
        }
        .eyebrow {
            color: var(--gold);
            display: block;
            font-weight: 900;
            margin-bottom: 14px;
            text-transform: uppercase;
        }
        h1 {
            font-size: clamp(42px, 6vw, 72px);
            font-weight: 900;
            line-height: 1.02;
            margin-bottom: 20px;
            max-width: 820px;
        }
        .hero p {
            color: #c8d7e8;
            font-size: 18px;
            line-height: 1.7;
            margin-bottom: 30px;
            max-width: 680px;
        }
        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }
        .btn-main {
            background: linear-gradient(135deg, #f5c542, #ffffff);
            border: 0;
            border-radius: 12px;
            box-shadow: 0 16px 34px rgba(0,0,0,0.22);
            color: var(--navy);
            display: inline-block;
            font-weight: 900;
            min-width: 140px;
            padding: 13px 22px;
            text-align: center;
            text-decoration: none;
        }
        .btn-main:hover {
            color: var(--navy);
            filter: brightness(0.96);
        }
        .btn-soft {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.18);
            border-radius: 12px;
            color: #ffffff;
            display: inline-block;
            font-weight: 900;
            min-width: 140px;
            padding: 12px 20px;
            text-align: center;
            text-decoration: none;
        }
        .btn-soft:hover {
            background: rgba(255,255,255,0.16);
            color: #ffffff;
        }
        .btn-main:focus-visible,
        .btn-soft:focus-visible,
        .portal-action:focus-visible {
            outline: 3px solid rgba(245,197,66,0.75);
            outline-offset: 3px;
        }
        .minimal-card {
            background: rgba(255,255,255,0.96);
            border-radius: 24px;
            box-shadow: 0 26px 70px rgba(0,0,0,0.28);
            color: var(--navy);
            padding: 30px;
        }
        .minimal-card h2 {
            font-size: 24px;
            font-weight: 900;
            margin-bottom: 20px;
        }
        .portal-row {
            align-items: center;
            border-top: 1px solid #dbe5ef;
            display: flex;
            justify-content: space-between;
            padding: 18px 0;
        }
        .portal-row:first-of-type {
            border-top: 0;
        }
        .portal-row strong {
            display: block;
            font-size: 17px;
        }
        .portal-row span {
            color: #64748b;
            font-size: 14px;
        }
        .portal-action {
            background: #e8f2ff;
            border-radius: 10px;
            color: #1d4ed8;
            display: inline-flex;
            font-size: 13px;
            font-weight: 900;
            justify-content: center;
            min-width: 82px;
            padding: 8px 12px;
            text-decoration: none;
        }
        .portal-action:hover {
            background: #dbeafe;
            color: #1e40af;
        }
        .pill {
            background: #e8f2ff;
            border-radius: 999px;
            color: #1d4ed8;
            font-size: 12px;
            font-weight: 900;
            padding: 7px 10px;
        }
        .services {
            background: #eef3f8;
            color: #0f172a;
            padding: 64px 0;
        }
        .section-heading {
            margin-bottom: 28px;
            max-width: 720px;
        }
        .section-heading span {
            color: var(--blue);
            display: block;
            font-size: 13px;
            font-weight: 900;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .section-heading h2 {
            color: var(--navy);
            font-size: 34px;
            font-weight: 900;
            margin: 0;
        }
        .service-grid {
            display: grid;
            gap: 18px;
            grid-template-columns: repeat(4, 1fr);
        }
        .service-card {
            background: #ffffff;
            border: 1px solid #dbe5ef;
            border-radius: 18px;
            box-shadow: 0 8px 24px rgba(15,23,42,0.07);
            padding: 24px;
        }
        .service-card strong {
            align-items: center;
            background: #e8f2ff;
            border-radius: 12px;
            color: var(--blue);
            display: inline-flex;
            font-weight: 900;
            height: 42px;
            justify-content: center;
            margin-bottom: 16px;
            width: 42px;
        }
        .service-card h3 {
            color: var(--navy);
            font-size: 18px;
            font-weight: 900;
            margin-bottom: 8px;
        }
        .service-card p {
            color: #64748b;
            line-height: 1.6;
            margin: 0;
        }
        footer {
            color: #9fb4cc;
            padding: 20px 0;
        }
        @media (max-width: 992px) {
            .navbar .nav-link {
                margin-left: 0;
                margin-top: 8px;
            }
            .hero-grid {
                grid-template-columns: 1fr;
            }
            .service-grid {
                grid-template-columns: 1fr;
            }
            .top-strip .container {
                flex-direction: column;
                gap: 4px;
            }
            .hero-actions a {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="top-strip">
        <div class="container">
            <span>Government Electricity Consumer Service Portal</span>
            <span>Helpline: 1912 | Emergency Support</span>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="brand" href="index.php">
                <span class="brand-logo">PD</span>
                <span>
                    <strong>PowerDesk</strong>
                    <span>Electricity Complaint Portal</span>
                </span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="hero">
        <div class="container hero-grid">
            <section>
                <span class="eyebrow">Smart Grievance Resolution</span>
                <h1>Electricity complaint services, simplified.</h1>
                <p>
                    A secure PowerDesk portal for consumers, field agents, and administrators
                    to register, route, track, and resolve electricity complaints.
                </p>
                <div class="hero-actions">
                    <a href="login.php" class="btn-main" aria-label="Login to PowerDesk portal">Login</a>
                    <a href="register.php" class="btn-soft" aria-label="Register a consumer account">Register</a>
                </div>
            </section>

            <aside class="minimal-card">
                <h2>Portal Access</h2>
                <div class="portal-row">
                    <div>
                        <strong>Consumer</strong>
                        <span>Register and track complaints</span>
                    </div>
                    <a href="register.php" class="portal-action">Register</a>
                </div>
                <div class="portal-row">
                    <div>
                        <strong>Agent</strong>
                        <span>Manage assigned field tickets</span>
                    </div>
                    <a href="login.php" class="portal-action">Login</a>
                </div>
                <div class="portal-row">
                    <div>
                        <strong>Admin</strong>
                        <span>Monitor analytics and agents</span>
                    </div>
                    <a href="login.php" class="portal-action">Login</a>
                </div>
            </aside>
        </div>
    </main>

    <section class="services" id="services">
        <div class="container">
            <div class="section-heading">
                <span>Services</span>
                <h2>Everything needed for electricity complaint resolution.</h2>
            </div>

            <div class="service-grid">
                <div class="service-card">
                    <strong>AI</strong>
                    <h3>AI Complaint Assistant</h3>
                    <p>Helps consumers with safe troubleshooting and creates tickets when needed.</p>
                </div>
                <div class="service-card">
                    <strong>CM</strong>
                    <h3>Complaint Management</h3>
                    <p>Register complaints, track status, and view service updates in one place.</p>
                </div>
                <div class="service-card">
                    <strong>AG</strong>
                    <h3>Agent Operations</h3>
                    <p>Agents manage assigned tickets, update progress, and add field notes.</p>
                </div>
                <div class="service-card">
                    <strong>AD</strong>
                    <h3>Admin Control</h3>
                    <p>Admins monitor analytics, manage agents, and supervise ticket resolution.</p>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container d-flex flex-wrap justify-content-between gap-2">
            <span>&copy; 2026 PowerDesk Electricity Complaint Portal</span>
            <span>Built for faster, safer consumer service resolution</span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
