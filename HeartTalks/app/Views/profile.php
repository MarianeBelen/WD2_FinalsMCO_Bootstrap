<DOCTYPE html>
<html>
<head>
    <title>Profile</title>
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
</head>
<body>

<div class="topbar">💜 HeartTalks</div>

<!-- Navigation menu -->
<div class="topmenu">
    <a href="<?= base_url('/home') ?>">Home</a>
    |
    <a href="<?= base_url('/profile') ?>">Profile</a>
    |
    <a href="<?= base_url('/logout') ?>">Logout</a>
</div>

<div class="wrap">

    <!-- USER PROFILE CARD -->
    <div class="card">
        <!-- display logged-in user's name -->
        <h2><?= session()->get('fullname') ?></h2>
        <!-- display username -->
        <p>@<?= session()->get('username') ?></p>
        <!-- display total number of friends -->
        <p>👥 <?= $friendCount ?> friends</p>
    </div>

    <!-- REQUESTS -->
    <div class="card">
        <h3>Friend Requests</h3>

        <!-- if no friend requests exist -->
        <?php if (empty($requests)): ?>
            <p>No requests</p>
        <?php endif; ?>

        <!-- loop through all incoming friend requests -->
        <?php foreach ($requests as $r): ?>
            <div class="user">
                <!-- show sender name -->
                <span><?= esc($r['fullname']) ?> sent you a request</span>
                <span>
                    <!-- accept request -->
                    <a href="<?= base_url('profile?accept=' . $r['id']) ?>">Accept</a>
                    <!-- reject request -->
                    <a href="<?= base_url('profile?reject=' . $r['id']) ?>">Reject</a>
                </span>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- FRIENDS -->
    <div class="card">
        <h3>Friends</h3>

        <!-- if no friends yet -->
        <?php if (empty($friends)): ?>
            <p>No friends yet</p>
        <?php endif; ?>

        <!-- loop through friend list -->
        <?php foreach ($friends as $f): ?>
            <div class="user">
                <span><?= esc($f['fullname']) ?></span>
                <!-- remove friend -->
                <a href="<?= base_url('profile?remove=' . $f['id']) ?>">Remove</a>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- PEOPLE YOU MAY KNOW -->
    <div class="card">
        <h3>People You May Know</h3>

        <!-- loop through all other users -->
        <?php foreach ($users as $u): ?>
            <div class="user">
                <span><?= esc($u['fullname']) ?></span>
                <!-- add friend -->
                <a href="<?= base_url('profile?add=' . $u['id']) ?>">Add Friend</a>
            </div>
        <?php endforeach; ?>
    </div>

</div>

</body>
</html>