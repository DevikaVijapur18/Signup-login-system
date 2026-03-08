<?php
/*
 * forgot_password.php
 * Accepts email address, generates a reset token, and sends a reset link.
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
    $email = mysqli_real_escape_string($conn, trim($_POST["email"]));

    if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
        $showError = "Please enter a valid email address.";
    } else {
        $sql    = "SELECT id, username FROM `users` WHERE email='$email' AND is_verified=1";
        $result = mysqli_query($conn, $sql);

        // Always show the same success message to prevent user enumeration
        if ($result && mysqli_num_rows($result) === 1) {
            $row    = mysqli_fetch_assoc($result);
            $token  = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $uid    = $row['id'];

            mysqli_query($conn, "UPDATE `users` SET reset_token='$token', reset_token_expiry='$expiry' WHERE id=$uid");

            $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/loginsystem/reset_password.php?token=$token";
            $username  = htmlspecialchars($row['username']);

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'devikavijapur0718@gmail.com';   // ← YOUR email
                $mail->Password   = '123';      // ← App password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('your_email@gmail.com', 'GateKeep');
                $mail->addAddress($email, $username);
                $mail->isHTML(true);
                $mail->Subject = 'Reset your GateKeep password';
                $mail->Body    = "
                    <div style='font-family:Segoe UI,sans-serif;max-width:480px;margin:auto;'>
                      <div style='background:linear-gradient(135deg,#7c3aed,#9333ea);border-radius:14px 14px 0 0;padding:2rem;text-align:center;'>
                        <h1 style='color:#fff;font-size:1.6rem;margin:0;'>&#128274; GateKeep</h1>
                      </div>
                      <div style='background:#fff;border-radius:0 0 14px 14px;padding:2rem;border:1px solid #e5e7eb;border-top:none;'>
                        <h2 style='color:#1e1b4b;'>Password Reset Request</h2>
                        <p style='color:#6b7280;'>Hi <strong>$username</strong>, we received a request to reset your password. Click the button below to choose a new one.</p>
                        <div style='text-align:center;margin:1.8rem 0;'>
                          <a href='$resetLink'
                             style='background:linear-gradient(135deg,#7c3aed,#9333ea);color:#fff;padding:0.85rem 2rem;border-radius:10px;text-decoration:none;font-weight:700;font-size:1rem;display:inline-block;'>
                            &#128275; Reset Password
                          </a>
                        </div>
                        <p style='color:#9ca3af;font-size:0.82rem;'>This link expires in <strong>1 hour</strong>. If you didn&#39;t request this, ignore this email &mdash; your password won&#39;t change.</p>
                      </div>
                    </div>";
                $mail->AltBody = "Reset your password: $resetLink (expires in 1 hour)";
                $mail->send();
            } catch (Exception $e) {
                // Silent fail for security — don't expose SMTP errors
            }
        }
        // Always show the same message
        $showAlert = true;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password — GateKeep</title>
    <link rel="stylesheet" href="/loginsystem/style.css">
</head>
<body>
<?php require "partials/_nav.php"; ?>

<div class="page-center">
    <?php if ($showAlert): ?>
        <div class="info-card">
            <span class="big-icon">&#128140;</span>
            <h2>Check Your Inbox</h2>
            <p>If an account with that email exists, we've sent a password reset link. Check your inbox (and spam folder, just in case).</p>
            <a href="login.php" class="btn btn-primary">Back to Login</a>
        </div>
    <?php else: ?>
        <div class="auth-card">
            <div class="auth-card-header">
                <div class="auth-icon">&#128273;</div>
                <h2>Forgot Password?</h2>
                <p>We'll email you a reset link</p>
            </div>

            <div class="auth-card-body">
                <?php if ($showError): ?>
                    <div class="alert alert-danger">
                        <span class="alert-icon">&#9888;</span>
                        <div><?= htmlspecialchars($showError) ?></div>
                    </div>
                <?php endif; ?>

                <form action="/loginsystem/forgot_password.php" method="post">
                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <div class="input-wrapper">
                            <input type="email" class="form-control" id="email" name="email"
                                   placeholder="you@example.com" required autocomplete="email">
                            <span class="input-icon">&#9993;</span>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">&#128140; Send Reset Link</button>
                </form>

                <div class="auth-footer">
                    <a href="login.php">&#8592; Back to Login</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
