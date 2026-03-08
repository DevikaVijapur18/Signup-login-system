<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$loggedin = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
?>
<nav class="navbar">
    <div class="navbar-inner">
        <a href="/loginsystem" class="navbar-brand">
            <span class="brand-icon">&#128274;</span>
            GateKeep
        </a>
        <ul class="navbar-links">
            <?php if ($loggedin): ?>
                <li><a href="/loginsystem/welcome.php" class="active">Home</a></li>
                <li><a href="/loginsystem/logout.php" class="btn-logout">Logout</a></li>
            <?php else: ?>
                <li><a href="/loginsystem/login.php">Login</a></li>
                <li><a href="/loginsystem/signup.php">Sign Up</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
