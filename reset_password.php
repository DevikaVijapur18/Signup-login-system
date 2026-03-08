<?php
/*
 * reset_password.php
 * Validates the reset token (max 1 hour old) and allows the user to set a new password.
 */

$token      = isset($_GET['token']) ? trim($_GET['token']) : '';
$showError  = false;
$showAlert  = false;
$validToken = false;

if (empty($token)) {
    header("Location: forgot_password.php");
    exit;
}

include "partials/_dbconnect.php";
$safeToken = mysqli_real_escape_string($conn, $token);

// Check token validity
$sql    = "SELECT id, username FROM `users`
           WHERE reset_token='$safeToken'
             AND reset_token_expiry > NOW()
             AND is_verified=1";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) === 1) {
    $validToken = true;
    $row        = mysqli_fetch_assoc($result);
    $uid        = $row['id'];
    $uname      = htmlspecialchars($row['username']);
} else {
    $showError = "This reset link is invalid or has expired. Please request a new one.";
}

// Handle new password submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && $validToken) {
    $password  = $_POST['password'];
    $cpassword = $_POST['cpassword'];

    if (strlen($password) < 6) {
        $showError  = "Password must be at least 6 characters.";
    } elseif ($password !== $cpassword) {
        $showError  = "Passwords do not match.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $upd  = "UPDATE `users` SET password='$hash', reset_token=NULL, reset_token_expiry=NULL WHERE id=$uid";
        if (mysqli_query($conn, $upd)) {
            $showAlert  = true;
            $validToken = false; // Don't show form again
        } else {
            $showError = "Something went wrong. Please try again.";
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password — GateKeep</title>
    <link rel="stylesheet" href="/loginsystem/style.css">
</head>
<body>
<?php require "partials/_nav.php"; ?>

<div class="page-center">
    <?php if ($showAlert): ?>
        <div class="info-card">
            <span class="big-icon">&#127881;</span>
            <h2>Password Updated!</h2>
            <p>Your password has been reset successfully. You can now log in with your new password.</p>
            <a href="login.php" class="btn btn-primary">&#128275; Go to Login</a>
        </div>

    <?php elseif ($validToken): ?>
        <div class="auth-card">
            <div class="auth-card-header">
                <div class="auth-icon">&#128275;</div>
                <h2>Reset Password</h2>
                <p>Hi <?= $uname ?>! Choose a strong new password</p>
            </div>

            <div class="auth-card-body">
                <?php if ($showError): ?>
                    <div class="alert alert-danger">
                        <span class="alert-icon">&#9888;</span>
                        <div><?= htmlspecialchars($showError) ?></div>
                    </div>
                <?php endif; ?>

                <form action="/loginsystem/reset_password.php?token=<?= htmlspecialchars($token) ?>" method="post">
                    <div class="form-group">
                        <label class="form-label" for="password">New Password</label>
                        <div class="input-wrapper">
                            <input type="password" class="form-control" id="password" name="password"
                                   placeholder="Min. 6 characters" maxlength="64" required
                                   autocomplete="new-password">
                            <span class="input-icon">&#128274;</span>
                            <button type="button" class="toggle-pw" onclick="togglePw('password', this)">&#128065;</button>
                        </div>
                        <div class="strength-bar-wrap"><div class="strength-bar" id="strengthBar"></div></div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="cpassword">Confirm New Password</label>
                        <div class="input-wrapper">
                            <input type="password" class="form-control" id="cpassword" name="cpassword"
                                   placeholder="Repeat your password" maxlength="64" required
                                   autocomplete="new-password">
                            <span class="input-icon">&#128275;</span>
                            <button type="button" class="toggle-pw" onclick="togglePw('cpassword', this)">&#128065;</button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">&#128640; Update Password</button>
                </form>
            </div>
        </div>

    <?php else: ?>
        <div class="info-card">
            <span class="big-icon">&#10060;</span>
            <h2>Link Invalid or Expired</h2>
            <p><?= htmlspecialchars($showError ?: "This reset link is no longer valid.") ?></p>
            <a href="forgot_password.php" class="btn btn-secondary">Request New Link</a>
        </div>
    <?php endif; ?>
</div>

<script>
function togglePw(id, btn) {
    const input = document.getElementById(id);
    if (input.type === 'password') { input.type = 'text'; btn.textContent = '🙈'; }
    else                           { input.type = 'password'; btn.textContent = '👁'; }
}
document.getElementById('password')?.addEventListener('input', function () {
    const val = this.value, bar = document.getElementById('strengthBar');
    let s = 0;
    if (val.length >= 6)  s++;
    if (val.length >= 10) s++;
    if (/[A-Z]/.test(val)) s++;
    if (/[0-9]/.test(val)) s++;
    if (/[^A-Za-z0-9]/.test(val)) s++;
    const colors = ['#ef4444','#f97316','#eab308','#22c55e','#16a34a'];
    const widths  = ['20%','40%','60%','80%','100%'];
    bar.style.width      = val.length ? widths[Math.min(s-1,4)] : '0%';
    bar.style.background = val.length ? colors[Math.min(s-1,4)] : 'transparent';
});
</script>
</body>
</html>
