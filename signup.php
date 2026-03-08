<?php
/*
 * signup.php
 * Creates account, stores unverified user, sends verification email via PHPMailer.
 *
 * DB table required (add columns to existing `users` table):
 *   ALTER TABLE `users`
 *     ADD COLUMN `email`             VARCHAR(255) NOT NULL AFTER `username`,
 *     ADD COLUMN `is_verified`       TINYINT(1)   NOT NULL DEFAULT 0,
 *     ADD COLUMN `verify_token`      VARCHAR(64)  DEFAULT NULL,
 *     ADD COLUMN `reset_token`       VARCHAR(64)  DEFAULT NULL,
 *     ADD COLUMN `reset_token_expiry` DATETIME    DEFAULT NULL;
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/phpmailer/phpmailer/src/Exception.php';
require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/phpmailer/src/SMTP.php';

$showAlert = false;
$showError = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    include "partials/_dbconnect.php";

    $username  = mysqli_real_escape_string($conn, trim($_POST["username"]));
    $email     = mysqli_real_escape_string($conn, trim($_POST["email"]));
    $password  = $_POST["password"];
    $cpassword = $_POST["cpassword"];

    // Validate
    if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
        $showError = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $showError = "Password must be at least 6 characters.";
    } elseif ($password !== $cpassword) {
        $showError = "Passwords do not match.";
    } else {
        // Check duplicates
        $checkSql = "SELECT id FROM `users` WHERE username='$username' OR email='$email'";
        $checkRes = mysqli_query($conn, $checkSql);
        if (mysqli_num_rows($checkRes) > 0) {
            $showError = "Username or email already registered.";
        } else {
            $hash  = password_hash($password, PASSWORD_DEFAULT);
            $token = bin2hex(random_bytes(32));

            $sql = "INSERT INTO `users` (`username`, `email`, `password`, `verify_token`, `is_verified`, `dt`)
                    VALUES ('$username', '$email', '$hash', '$token', 0, current_timestamp())";
            if (mysqli_query($conn, $sql)) {
                // Send verification email
                $verifyLink = "http://" . $_SERVER['HTTP_HOST'] . "/loginsystem/verify.php?token=$token";

                $mail = new PHPMailer(true);
                try {
                    // ── SMTP settings ──────────────────────────────────────────
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';   // Change to your SMTP host
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'devikavijapur0718@gmail.com';   // ← YOUR email
                    $mail->Password   = '123';      // ← App password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;
                    // ───────────────────────────────────────────────────────────

                    $mail->setFrom('your_email@gmail.com', 'GateKeep');
                    $mail->addAddress($email, $username);
                    $mail->isHTML(true);
                    $mail->Subject = 'Verify your GateKeep account';
                    $mail->Body    = "
                        <div style='font-family:Segoe UI,sans-serif;max-width:480px;margin:auto;'>
                          <div style='background:linear-gradient(135deg,#7c3aed,#9333ea);border-radius:14px 14px 0 0;padding:2rem;text-align:center;'>
                            <h1 style='color:#fff;font-size:1.6rem;margin:0;'>&#128274; GateKeep</h1>
                          </div>
                          <div style='background:#fff;border-radius:0 0 14px 14px;padding:2rem;border:1px solid #e5e7eb;border-top:none;'>
                            <h2 style='color:#1e1b4b;'>Hi, $username! &#128075;</h2>
                            <p style='color:#6b7280;'>Thanks for signing up. Click the button below to verify your email address and activate your account.</p>
                            <div style='text-align:center;margin:1.8rem 0;'>
                              <a href='$verifyLink'
                                 style='background:linear-gradient(135deg,#7c3aed,#9333ea);color:#fff;padding:0.85rem 2rem;border-radius:10px;text-decoration:none;font-weight:700;font-size:1rem;display:inline-block;'>
                                &#10003; Verify Email
                              </a>
                            </div>
                            <p style='color:#9ca3af;font-size:0.82rem;'>If you did not create an account, you can safely ignore this email.<br>This link expires in 24 hours.</p>
                          </div>
                        </div>";
                    $mail->AltBody = "Verify your account: $verifyLink";
                    $mail->send();
                    $showAlert = "We've sent a verification email to <strong>$email</strong>. Please check your inbox and click the link to activate your account.";
                } catch (Exception $e) {
                    // Account created but email failed — still show partial success
                    $showAlert = "Account created but we couldn't send the verification email. Please contact support. (Error: {$mail->ErrorInfo})";
                }
            } else {
                $showError = "Could not create account. Please try again.";
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign Up — GateKeep</title>
    <link rel="stylesheet" href="/loginsystem/style.css">
</head>
<body>
<?php require "partials/_nav.php"; ?>

<div class="page-center">
    <div class="auth-card">
        <div class="auth-card-header">
            <div class="auth-icon">&#128100;</div>
            <h2>Create Account</h2>
            <p>Join GateKeep — it's free</p>
        </div>

        <div class="auth-card-body">
            <?php if ($showAlert): ?>
                <div class="alert alert-success">
                    <span class="alert-icon">&#9989;</span>
                    <div><?= $showAlert ?></div>
                </div>
            <?php endif; ?>
            <?php if ($showError): ?>
                <div class="alert alert-danger">
                    <span class="alert-icon">&#9888;</span>
                    <div><strong>Error:</strong> <?= htmlspecialchars($showError) ?></div>
                </div>
            <?php endif; ?>

            <?php if (!$showAlert): ?>
            <form action="/loginsystem/signup.php" method="post" id="signupForm">
                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <div class="input-wrapper">
                        <input type="text" class="form-control" id="username" name="username"
                               placeholder="e.g. devika123" maxlength="30" required
                               autocomplete="username">
                        <span class="input-icon">&#128100;</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <div class="input-wrapper">
                        <input type="email" class="form-control" id="email" name="email"
                               placeholder="you@example.com" required
                               autocomplete="email">
                        <span class="input-icon">&#9993;</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-wrapper">
                        <input type="password" class="form-control" id="password" name="password"
                               placeholder="Min. 6 characters" maxlength="64" required
                               autocomplete="new-password">
                        <span class="input-icon">&#128274;</span>
                        <button type="button" class="toggle-pw" onclick="togglePw('password', this)" title="Show/hide password">&#128065;</button>
                    </div>
                    <div class="strength-bar-wrap"><div class="strength-bar" id="strengthBar"></div></div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="cpassword">Confirm Password</label>
                    <div class="input-wrapper">
                        <input type="password" class="form-control" id="cpassword" name="cpassword"
                               placeholder="Repeat your password" maxlength="64" required
                               autocomplete="new-password">
                        <span class="input-icon">&#128275;</span>
                        <button type="button" class="toggle-pw" onclick="togglePw('cpassword', this)" title="Show/hide password">&#128065;</button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">&#128640; Create Account</button>
            </form>
            <?php endif; ?>

            <div class="auth-footer">
                Already have an account? <a href="login.php">Sign in</a>
            </div>
        </div>
    </div>
</div>

<script>
function togglePw(id, btn) {
    const input = document.getElementById(id);
    if (input.type === 'password') {
        input.type = 'text';
        btn.textContent = '🙈';
    } else {
        input.type = 'password';
        btn.textContent = '👁';
    }
}

// Password strength indicator
document.getElementById('password')?.addEventListener('input', function () {
    const val = this.value;
    const bar = document.getElementById('strengthBar');
    let strength = 0;
    if (val.length >= 6)  strength++;
    if (val.length >= 10) strength++;
    if (/[A-Z]/.test(val)) strength++;
    if (/[0-9]/.test(val)) strength++;
    if (/[^A-Za-z0-9]/.test(val)) strength++;

    const colors = ['#ef4444','#f97316','#eab308','#22c55e','#16a34a'];
    const widths  = ['20%','40%','60%','80%','100%'];
    bar.style.width      = val.length ? widths[Math.min(strength - 1, 4)] : '0%';
    bar.style.background = val.length ? colors[Math.min(strength - 1, 4)] : 'transparent';
});
</script>
</body>
</html>
