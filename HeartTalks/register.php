<?php
    // register.php - lets new users create an account
    // after registering successfully, they can go to index.php to log in

    include "config.php"; // need the db connection to check/insert users

    $msg = "";
    $success = false; // used to decide which CSS class to give the message (green or red)

    // only runs when the form is submitted
    if (isset($_POST['register'])) {
        $fullname = $_POST['fullname'];
        $username = $_POST['username'];

        // IMPORTANT: never save a plain text password to the database
        // password_hash() turns it into a long scrambled string
        // PASSWORD_DEFAULT uses bcrypt which is secure and recommended
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // check if someone already registered with this username
        // usernames have to be unique so we can look people up by them at login
        $check = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");

        if (mysqli_num_rows($check) > 0) {
            // username is taken, tell the user to pick a different one
            $msg = "Username already exists!";
        } else {
            // username is available, insert the new account into the users table
            mysqli_query($conn, "INSERT INTO users(fullname, username, password) VALUES('$fullname', '$username', '$password')");
            $msg = "Registration successful!";
            $success = true; // flip this so the message shows in green instead of red
        }
    }
?>
<!DOCTYPE html>
<html>
<head>
    <title>HeartTalks Register</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="topbar">💜 HeartTalks</div>

<div class="auth-wrap">
    <h2>Create Account</h2>

    <form method="POST">
        <input type="text"     name="fullname" placeholder="Full Name" required>
        <input type="text"     name="username" placeholder="Username"  required>
        <input type="password" name="password" placeholder="Password"  required>
        <button type="submit" name="register" style="width:100%;margin-top:14px;">Register</button>
    </form>

    <?php if ($msg): ?>
        <!-- msg-success is green, msg-error is red - defined in style.css -->
        <p class="<?php echo $success ? 'msg-success' : 'msg-error'; ?>"><?php echo $msg; ?></p>
    <?php endif; ?>

    <hr>
    <p>Already have an account? <a href="index.php">Log In</a></p>
</div>
</body>
</html>