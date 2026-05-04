<?php
    // profile.php - shows the user's profile, their friends, and people they may know
    // also handles friend requests (send, accept, reject, remove)

    session_start();
    include "config.php";

    // not logged in? go back to login
    if (!isset($_SESSION['userid'])) {
        header("Location:index.php");
        exit();
    }

    $userid = $_SESSION['userid'];

    # SEND FRIEND REQUEST
    # triggered by clicking "+ Add Friend" next to someone's name
    # the target user's ID comes through the URL as ?add=ID
    if (isset($_GET['add'])) {
        $target = $_GET['add'];

        // you shouldn't be able to send a friend request to yourself
        if ($target != $userid) {

            // check if a request from this user to the target already exists
            // we don't want to insert duplicate rows in friend_requests
            $check = mysqli_query($conn, "SELECT * FROM friend_requests WHERE sender_id='$userid' AND receiver_id='$target'");

            if (mysqli_num_rows($check) == 0) {
                // no existing request, so send one
                mysqli_query($conn, "INSERT INTO friend_requests(sender_id, receiver_id) VALUES('$userid', '$target')");
            }
            // if it already exists, just do nothing - no error needed
        }
    }

    # ACCEPT FRIEND REQUEST
    # triggered by clicking "✅ Accept" on an incoming request
    # the request ID comes through the URL as ?accept=ID
    if (isset($_GET['accept'])) {
        $rid = $_GET['accept']; // the friend_requests table row ID

        // fetch the request row so we know who the sender and receiver are
        $req = mysqli_query($conn, "SELECT * FROM friend_requests WHERE id='$rid'");
        $row = mysqli_fetch_assoc($req);

        // insert a new row into the friends table to officially make them friends
        mysqli_query($conn, "INSERT INTO friends(user1, user2) VALUES('{$row['sender_id']}', '{$row['receiver_id']}')");

        // delete the request now that it's been accepted
        mysqli_query($conn, "DELETE FROM friend_requests WHERE id='$rid'");
    }

    # REJECT FRIEND REQUEST
    # same as accept but we just delete the request without adding to friends
    if (isset($_GET['reject'])) {
        mysqli_query($conn, "DELETE FROM friend_requests WHERE id='{$_GET['reject']}'");
    }

    # REMOVE FRIEND
    # triggered by clicking "Remove" next to a friend's name
    # deletes the row from the friends table
    if (isset($_GET['remove'])) {
        mysqli_query($conn, "DELETE FROM friends WHERE id='{$_GET['remove']}'");
    }

    # LOAD DATA FOR THE PAGE

    // count how many friends this user has
    // the user could be in either the user1 or user2 column, so we check both
    $count = mysqli_query($conn, "SELECT COUNT(*) as total FROM friends WHERE user1='$userid' OR user2='$userid'");
    $friendCount = mysqli_fetch_assoc($count);

    // get every user except the current one - shown in "People You May Know"
    $users = mysqli_query($conn, "SELECT * FROM users WHERE id != '$userid'");

    // get incoming friend requests for the current user
    // JOIN with users so we can show the sender's name
    $requests = mysqli_query($conn, "
        SELECT friend_requests.*, users.fullname
        FROM friend_requests
        JOIN users ON friend_requests.sender_id = users.id
        WHERE receiver_id='$userid'
    ");

    // get the current user's friend list
    // the OR is needed because either user could be in user1 or user2
    // we JOIN so we can show the friend's name instead of just their ID
    $friendList = mysqli_query($conn, "
        SELECT friends.id, users.fullname
        FROM friends
        JOIN users ON (
            (users.id = friends.user1 AND friends.user2='$userid') OR
            (users.id = friends.user2 AND friends.user1='$userid')
        )
    ");

    // grab just the first letter of the user's name for the avatar circle
    // strtoupper makes it a capital letter, substr cuts it to 1 character
    $initial = strtoupper(substr($_SESSION['fullname'], 0, 1));
?>
<!DOCTYPE html>
<html>
<head>
    <title>Profile – HeartTalks</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="topbar">💜 HeartTalks</div>

<div class="topmenu">
    <a href="home.php">Home</a> &nbsp;|&nbsp;
    <a href="profile.php">Profile</a> &nbsp;|&nbsp;
    <a href="logout.php">Logout</a>
</div>

<div class="wrap">

    <!-- profile card: avatar, name, username, and friend count -->
    <div class="card">
        <div class="profile-header">
            <!-- avatar circle shows the first letter of the user's name since we don't have profile photos -->
            <div class="avatar-circle"><?php echo $initial; ?></div>
            <div>
                <h2 style="margin:0"><?php echo htmlspecialchars($_SESSION['fullname']); ?></h2>
                <p style="margin:2px 0" class="small">@<?php echo htmlspecialchars($_SESSION['username']); ?></p>
                <p class="friend-count">👥 <?php echo $friendCount['total']; ?> friends</p>
            </div>
        </div>
    </div>

    <!-- incoming friend requests -->
    <div class="card">
        <h3 style="margin-top:0">🔔 Friend Requests</h3>
        <?php
            $any = false; // track whether there are any requests to show
            while ($r = mysqli_fetch_assoc($requests)):
                $any = true;
        ?>
            <div class="user">
                <span><?php echo htmlspecialchars($r['fullname']); ?> sent you a friend request</span>
                <span>
                    <!-- pass the request's row ID so the server knows which one to accept/reject -->
                    <a href="profile.php?accept=<?php echo $r['id']; ?>">✅ Accept</a> &nbsp;
                    <a href="profile.php?reject=<?php echo $r['id']; ?>" style="color:#888;">❌ Reject</a>
                </span>
            </div>
        <?php endwhile; ?>
        <?php if (!$any): ?>
            <p class="small">No pending requests.</p>
        <?php endif; ?>
    </div>

    <!-- current friends list -->
    <div class="card">
        <h3 style="margin-top:0">👥 Your Friends</h3>
        <?php
            $any = false;
            while ($f = mysqli_fetch_assoc($friendList)):
                $any = true;
        ?>
            <div class="user">
                <span><?php echo htmlspecialchars($f['fullname']); ?></span>
                <!-- pass the friends table row ID so we know which friendship to delete -->
                <a href="profile.php?remove=<?php echo $f['id']; ?>" style="color:#888;font-size:13px;">Remove</a>
            </div>
        <?php endwhile; ?>
        <?php if (!$any): ?>
            <p class="small">No friends yet.</p>
        <?php endif; ?>
    </div>

    <!-- everyone else on the platform, so the user can send friend requests -->
    <div class="card">
        <h3 style="margin-top:0">People You May Know</h3>
        <?php while ($u = mysqli_fetch_assoc($users)): ?>
            <div class="user">
                <span><?php echo htmlspecialchars($u['fullname']); ?></span>
                <!-- clicking this reloads the page with ?add=ID which triggers the send request block at the top -->
                <a href="profile.php?add=<?php echo $u['id']; ?>">+ Add Friend</a>
            </div>
        <?php endwhile; ?>
    </div>

</div>
</body>
</html>