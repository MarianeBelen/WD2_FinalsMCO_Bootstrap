<?php
    // config.php - this file just connects to the database
    // This is included at the top of every page that needs the db

    // mysqli_connect(host, username, password, database_name)
    // "root" with no password is the default for XAMPP
    $conn = mysqli_connect("localhost", "root", "", "hearttalks");

    // if the connection fails, stop everything and show an error
    // this usually happens if XAMPP isn't running or the db name is wrong
    if (!$conn) {
        die("Connection Failed");
    }
?>