<!DOCTYPE html>
<html>
<head>
    <title>HeartTalks Login</title>
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
</head>
<body>

<div class="topbar">💜 HeartTalks</div>

<div class="auth-wrap">

    <h2>Log In</h2>

    <!-- SUCCESS MESSAGE (e.g. after registration) -->
    <?php if(session()->getFlashdata('msg')): ?>
    <p class="msg-success">
        <?= session()->getFlashdata('msg') ?>
    </p>
    <?php endif; ?>

    <!-- ERROR MESSAGE (wrong password / username not found) -->
    <?php if(isset($msg)): ?>
        <p class="msg-error"><?= $msg ?></p>
    <?php endif; ?>

    <!-- LOGIN FORM -->
    <form method="POST" action="<?= base_url('/login') ?>">

        <input type="text"
               name="username"
               placeholder="Username"
               required>

        <input type="password"
               name="password"
               placeholder="Password"
               required>

        <button type="submit">
            Log In
        </button>

    </form>

    <hr>

    <!-- LINK TO REGISTER PAGE -->
    <p>
        No account?
        <a href="<?= base_url('/register') ?>">
            Create one
        </a>
    </p>

</div>

</body>
</html>