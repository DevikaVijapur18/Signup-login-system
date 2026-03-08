<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}
$username = htmlspecialchars($_SESSION['username']);
$email    = htmlspecialchars($_SESSION['email'] ?? '');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard — <?= $username ?></title>
    <link rel="stylesheet" href="/loginsystem/style.css">
</head>
<body>
<?php require "partials/_nav.php"; ?>

<div class="dashboard-wrap">
    <div class="welcome-hero">
        <h1>Hello, <?= $username ?>! &#128075;</h1>
        <p>Welcome to your secure dashboard. Your account is verified and active.</p>
        <a href="logout.php" class="btn-light">&#128274; Logout securely</a>
    </div>

    <div class="info-grid">
        <div class="info-tile">
            <div class="tile-icon purple">&#128100;</div>
            <div>
                <div class="tile-label">Logged in as</div>
                <div class="tile-value"><?= $username ?></div>
            </div>
        </div>
        <div class="info-tile">
            <div class="tile-icon green">&#9989;</div>
            <div>
                <div class="tile-label">Account status</div>
                <div class="tile-value">Verified</div>
            </div>
        </div>
        <?php if ($email): ?>
        <div class="info-tile">
            <div class="tile-icon blue">&#9993;</div>
            <div>
                <div class="tile-label">Email</div>
                <div class="tile-value"><?= $email ?></div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
