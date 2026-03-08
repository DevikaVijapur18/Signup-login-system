# GateKeep — Login System Setup Guide

## New Files Added
```
loginsystem/
├── style.css              ← Pure CSS3 UI (replaces Bootstrap)
├── signup.php             ← Updated: email field + sends verification email
├── login.php              ← Updated: blocks unverified accounts
├── verify.php             ← NEW: activates account from email link
├── forgot_password.php    ← NEW: sends password reset email
├── reset_password.php     ← NEW: sets new password via reset token
├── welcome.php            ← Updated: improved dashboard
├── logout.php             ← Unchanged logic
└── partials/
    ├── _nav.php           ← Updated: no Bootstrap
    └── _dbconnect.php     ← Unchanged
```

---

## Step 1 — Database Migration

Run this SQL to add the required columns to your existing `users` table:

```sql
ALTER TABLE `users`
  ADD COLUMN `email`              VARCHAR(255) NOT NULL          AFTER `username`,
  ADD COLUMN `is_verified`        TINYINT(1)   NOT NULL DEFAULT 0,
  ADD COLUMN `verify_token`       VARCHAR(64)  DEFAULT NULL,
  ADD COLUMN `reset_token`        VARCHAR(64)  DEFAULT NULL,
  ADD COLUMN `reset_token_expiry` DATETIME     DEFAULT NULL;
```

---

## Step 2 — Install PHPMailer via Composer

Inside your `loginsystem/` folder, run:

```bash
composer require phpmailer/phpmailer
```

This creates `vendor/` with PHPMailer inside. The code already points to:
```
vendor/phpmailer/phpmailer/src/PHPMailer.php
```

---

## Step 3 — Configure SMTP

In **both** `signup.php` and `forgot_password.php`, replace these lines:

```php
$mail->Username = 'your_email@gmail.com';   // ← your Gmail address
$mail->Password = 'your_app_password';      // ← Gmail App Password (not your login password)
$mail->setFrom('your_email@gmail.com', 'GateKeep');
```

### Getting a Gmail App Password:
1. Enable 2-Factor Authentication on your Google account
2. Go to: Google Account → Security → App Passwords
3. Generate a password for "Mail" → copy the 16-character password
4. Paste it as `$mail->Password`

---

## Step 4 — User Flow

```
Sign Up → email sent → click verify link → verify.php activates account
                                               ↓
                                           Login works ✓

Login → forgot password? → forgot_password.php
                               ↓
                         email sent with reset link
                               ↓
                         reset_password.php → new password set → login
```

---

## Features
- ✅ Pure CSS3 UI — no Bootstrap dependency
- ✅ Glassmorphism navbar with backdrop blur
- ✅ Animated auth cards
- ✅ Email verification on signup (PHPMailer)
- ✅ Blocks login for unverified accounts
- ✅ Forgot password → secure reset link (1 hour expiry)
- ✅ Password strength indicator
- ✅ Show/hide password toggles
- ✅ Security: user enumeration prevention on forgot password
- ✅ Password hashing with PASSWORD_DEFAULT (bcrypt)
