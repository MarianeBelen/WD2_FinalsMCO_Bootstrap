<?php
    // index.php - this is the login page, it's also the first page users see
    // if they're not logged in, other pages redirect here

    session_start(); // start the session so we can store login info later
    include "config.php"; // bring in the db connection from config.php

    $msg = ""; // this will hold any error message to show the user

    // this block only runs when the login form is submitted
    // isset() checks if the login button was clicked (it's a named submit button)
    if (isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // look for a user with this username in the database
        // we check the username first, then verify the password separately below
        $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");

        if (mysqli_num_rows($query) > 0) {
            // username exists, now grab the row so we can check the password
            $row = mysqli_fetch_assoc($query);

            // password_verify() compares the typed password to the hashed one in the db
            // we never store plain text passwords, so we can't just do $password == $row['password']
            if (password_verify($password, $row['password'])) {

                // password matched! save their info in the session
                // session variables carry over to other pages until logout
                $_SESSION['userid']   = $row['id'];
                $_SESSION['fullname'] = $row['fullname'];
                $_SESSION['username'] = $row['username'];

                // send them to the home feed
                header("Location: home.php");
                exit(); // always exit after a redirect so the rest of the code doesn't run
            } else {
                $msg = "Wrong password!";
            }
        } else {
            // no user found with that username
            $msg = "Username not found!";
        }
    }
?>
<!DOCTYPE html>
<html>
<head>
    <title>HeartTalks Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="topbar">💜 HeartTalks</div>

<div class="auth-wrap">
    <h2>Log In</h2>

    <!-- method="POST" so the username/password don't show up in the URL -->
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="login" style="width:100%;margin-top:14px;">Log In</button>
    </form>

    <!-- only show the error message if there is one -->
    <?php if ($msg): ?><p class="msg-error"><?php echo $msg; ?></p><?php endif; ?>

    <hr>
    <p>No account? <a href="register.php">Create one</a></p>
</div>
</body>
</html>