<?php
/*
 * verify.php
 * Validates the token from the verification email and activates the account.
 */

$status = 'invalid'; // 'success' | 'already' | 'invalid'

if (!empty($_GET['token'])) {
    include "partials/_dbconnect.php";
    $token = mysqli_real_escape_string($conn, $_GET['token']);

    $sql    = "SELECT id, is_verified FROM `users` WHERE verify_token='$token'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        if ($row['is_verified'] == 1) {
            $status = 'already';
        } else {
            $upd = "UPDATE `users` SET is_verified=1, verify_token=NULL WHERE id={$row['id']}";
            if (mysqli_query($conn, $upd)) {
                $status = 'success';
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
    <title>Email Verification — GateKeep</title>
    <link rel="stylesheet" href="/loginsystem/style.css">
</head>
<body>
<?php require "partials/_nav.php"; ?>

<div class="page-center">
    <div class="info-card">
        <?php if ($status === 'success'): ?>
            <span class="big-icon">&#9989;</span>
            <h2>Email Verified!</h2>
            <p>Your account has been successfully activated.<br>You can now sign in to GateKeep.</p>
            <a href="login.php" class="btn btn-primary">&#128275; Go to Login</a>

        <?php elseif ($status === 'already'): ?>
            <span class="big-icon">&#128994;</span>
            <h2>Already Verified</h2>
            <p>This email address has already been verified.<br>Just sign in to continue.</p>
            <a href="login.php" class="btn btn-secondary">Go to Login</a>

        <?php else: ?>
            <span class="big-icon">&#10060;</span>
            <h2>Invalid or Expired Link</h2>
            <p>This verification link is invalid or has already been used.<br>Try signing up again or contact support.</p>
            <a href="signup.php" class="btn btn-secondary">Back to Sign Up</a>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
