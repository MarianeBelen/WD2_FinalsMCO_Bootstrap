<?php
    // logout.php - clears the session and sends the user back to the login page
    // there's no HTML here, it just does the logout and redirects

    session_start(); // have to start the session before we can destroy it

    // session_destroy() wipes all the session variables ($_SESSION['userid'], etc.)
    // after this the user is basically a stranger to the site again
    session_destroy();

    // send them back to the login page
    header("Location: index.php");
    exit();
?>