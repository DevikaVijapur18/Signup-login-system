<?php
/*
 * login.php
 * Authenticates user, blocks unverified accounts.
 */

$login     = false;
$showError = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    include "partials/_dbconnect.php";

    $username = mysqli_real_escape_string($conn, trim($_POST["username"]));
    $password = $_POST["password"];

    $sql    = "SELECT * FROM `users` WHERE username='$username'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        if (!password_verify($password, $row['password'])) {
            $showError = "Incorrect password. Please try again.";
        } elseif ($row['is_verified'] != 1) {
            $showError = "Your email is not verified yet. Please check your inbox for the verification link.";
        } else {
            if (session_status() === PHP_SESSION_NONE) session_start();
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $row['username'];
            $_SESSION['email']    = $row['email'];
            header("Location: welcome.php");
            exit;
        }
    } else {
        $showError = "No account found with that username.";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login — GateKeep</title>
    <link rel="stylesheet" href="/loginsystem/style.css">
</head>
<body>
<?php require "partials/_nav.php"; ?>

<div class="page-center">
    <div class="auth-card">
        <div class="auth-card-header">
            <div class="auth-icon">&#128275;</div>
            <h2>Welcome Back</h2>
            <p>Sign in to your account</p>
        </div>

        <div class="auth-card-body">
            <?php if ($showError): ?>
                <div class="alert alert-danger">
                    <span class="alert-icon">&#9888;</span>
                    <div><?= htmlspecialchars($showError) ?></div>
                </div>
            <?php endif; ?>

            <form action="/loginsystem/login.php" method="post">
                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <div class="input-wrapper">
                        <input type="text" class="form-control" id="username" name="username"
                               placeholder="Your username" required autocomplete="username">
                        <span class="input-icon">&#128100;</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-wrapper">
                        <input type="password" class="form-control" id="password" name="password"
                               placeholder="Your password" required autocomplete="current-password">
                        <span class="input-icon">&#128274;</span>
                        <button type="button" class="toggle-pw" onclick="togglePw('password', this)" title="Show/hide">&#128065;</button>
                    </div>
                </div>

                <a href="forgot_password.php" class="forgot-link">Forgot password?</a>

                <button type="submit" class="btn btn-primary">&#128275; Sign In</button>
            </form>

            <div class="auth-footer">
                Don't have an account? <a href="signup.php">Sign up</a>
            </div>
        </div>
    </div>
</div>

<script>
function togglePw(id, btn) {
    const input = document.getElementById(id);
    if (input.type === 'password') { input.type = 'text'; btn.textContent = '🙈'; }
    else                           { input.type = 'password'; btn.textContent = '👁'; }
}
</script>
</body>
</html>
