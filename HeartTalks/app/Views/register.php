<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
</head>
<body>

<div class="topbar">💜 HeartTalks</div>

<div class="auth-wrap">

    <h2>Create Account</h2>

    <!-- REGISTER FORM -->
    <form method="POST" action="<?= base_url('register') ?>">

    <!-- Full name input -->
        <input type="text"
               name="fullname"
               placeholder="Full Name"
               required>

        <input type="text"
               name="username"
               placeholder="Username"
               required>

    <!-- Username input -->
        <input type="text"
               name="username"
               placeholder="Username"
               required>

    <!-- Password input -->
        <input type="password"
               name="password"
               placeholder="Password"
               required>

    <!-- Submit button -->
        <button type="submit">
            Register
        </button>

    </form>

    <!-- ERROR MESSAGE -->
    <?php if(isset($msg)): ?>
    <p class="msg-error"><?= $msg ?></p>
<?php endif; ?>

</div>

</body>
</html>