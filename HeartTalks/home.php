<?php
    // home.php - the main feed page where users can post, react, and comment
    // this is the page users land on after logging in

    session_start();
    include "config.php";

    // if there's no userid in the session, the user isn't logged in
    // redirect them back to the login page
    if (!isset($_SESSION['userid'])) {
        header("Location: index.php");
        exit();
    }

    // save the userid to a variable so we don't have to type $_SESSION['userid'] every time
    $userid = $_SESSION['userid'];

    # CREATE POST
    # runs when the user clicks the "Post" button on the post box
    if (isset($_POST['post_btn'])) {
        $content = $_POST['content'];

        // don't insert anything if the textarea was empty
        if ($content != "") {
            // using a prepared statement here so apostrophes in the post
            // don't break the SQL query (e.g. "It's a great day!" would crash without this)
            $stmt = mysqli_prepare($conn, "INSERT INTO posts(user_id, content) VALUES(?, ?)");

            // "i" means integer, "s" means string - has to match the order of the ? placeholders
            mysqli_stmt_bind_param($stmt, "is", $userid, $content);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    # REACT TO POST (like / love / haha / sad / angry)
    # triggered by clicking one of the emoji links, which pass ?react=ID&type=TYPE in the URL
    if (isset($_GET['react']) && isset($_GET['type'])) {
        $postid  = $_GET['react'];
        $type    = $_GET['type'];

        // whitelist of allowed reaction types 
        // this stops someone from putting a random value in the URL
        $allowed = ['like', 'love', 'haha', 'sad', 'angry'];

        if (in_array($type, $allowed)) {

            // check if this user has already reacted to this post
            $stmt = mysqli_prepare($conn, "SELECT * FROM reactions WHERE user_id=? AND post_id=?");
            mysqli_stmt_bind_param($stmt, "ii", $userid, $postid);
            mysqli_stmt_execute($stmt);
            $check = mysqli_stmt_get_result($stmt);
            mysqli_stmt_close($stmt);

            if (mysqli_num_rows($check) == 0) {
                // no existing reaction - insert a brand new one
                $stmt = mysqli_prepare($conn, "INSERT INTO reactions(user_id, post_id, type) VALUES(?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "iis", $userid, $postid, $type);
            } else {
                // they already reacted - just change the type to the new one
                $stmt = mysqli_prepare($conn, "UPDATE reactions SET type=? WHERE user_id=? AND post_id=?");
                mysqli_stmt_bind_param($stmt, "sii", $type, $userid, $postid);
            }

            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    # ADD COMMENT
    # runs when the user submits the comment form under a post
    if (isset($_POST['comment_btn'])) {
        $postid  = $_POST['post_id']; // hidden input tells us which post this comment is for
        $comment = $_POST['comment'];

        // don't save an empty comment
        if ($comment != "") {
            $stmt = mysqli_prepare($conn, "INSERT INTO comments(user_id, post_id, comment) VALUES(?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "iis", $userid, $postid, $comment);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    # LOAD ALL POSTS
    # we JOIN with users so we can show the poster's name without a second query
    # ORDER BY posts.id DESC means newest posts appear at the top
    $posts = mysqli_query($conn, "
        SELECT posts.*, users.fullname
        FROM posts
        JOIN users ON posts.user_id = users.id
        ORDER BY posts.id DESC
    ");

    // this array maps reaction type names to their emoji
    // used in a few places below to display the right emoji for each reaction
    $emojis = ['like'=>'👍', 'love'=>'❤️', 'haha'=>'😂', 'sad'=>'😢', 'angry'=>'😡'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>HeartTalks</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="topbar">💜 HeartTalks</div>

<!-- navigation bar shown at the top of every logged-in page -->
<div class="topmenu">
    Welcome <b><?php echo htmlspecialchars($_SESSION['fullname']); ?></b> &nbsp;|&nbsp;
    <a href="home.php">Home</a> &nbsp;|&nbsp;
    <a href="profile.php">Profile</a> &nbsp;|&nbsp;
    <a href="logout.php">Logout</a>
</div>

<div class="feed">

    <!-- post creation box at the top of the feed -->
    <div class="postbox">
        <form method="POST">
            <textarea name="content" rows="3" placeholder="What's on your heart, <?php echo htmlspecialchars($_SESSION['fullname']); ?>?"></textarea>
            <button type="submit" name="post_btn">Post</button>
        </form>
    </div>

    <!-- loop through every post and display it -->
    <?php while ($row = mysqli_fetch_assoc($posts)) { ?>
    <?php
        $postid = $row['id'];

        // ----- REACTION COUNTS -----
        // group by type so we get a count for each reaction (like: 3, love: 1, etc.)
        $reactionCounts = [];
        $rq = mysqli_query($conn, "SELECT type, COUNT(*) as n FROM reactions WHERE post_id='$postid' GROUP BY type");
        while ($rr = mysqli_fetch_assoc($rq)) {
            $reactionCounts[$rr['type']] = $rr['n'];
        }
        // add up all the counts to get the total number of reactions on this post
        $totalReactions = array_sum($reactionCounts);

        // ----- CURRENT USER'S REACTION -----
        // check if the logged-in user has reacted to this post, and if so, which type
        // we use this to show their current reaction on the button (e.g. "❤️ Love")
        $myReact = mysqli_query($conn, "SELECT type FROM reactions WHERE user_id='$userid' AND post_id='$postid'");
        $myType  = mysqli_num_rows($myReact) > 0 ? mysqli_fetch_assoc($myReact)['type'] : null;

        // ----- COMMENTS -----
        // get all comments for this post, joined with users to show commenter names
        // ASC order so the oldest comment shows first (like a real chat)
        $comments = mysqli_query($conn, "
            SELECT comments.*, users.fullname FROM comments
            JOIN users ON comments.user_id = users.id
            WHERE post_id='$postid' ORDER BY comments.id ASC
        ");
    ?>

    <div class="post">
        <!-- post header: author name and timestamp -->
        <h3 class="post-author"><?php echo htmlspecialchars($row['fullname']); ?></h3>
        <p class="small"><?php echo $row['created_at']; ?></p>

        <!-- the actual post text -->
        <!-- htmlspecialchars() prevents XSS by converting < > & etc. to safe HTML entities -->
        <p class="post-content"><?php echo htmlspecialchars($row['content']); ?></p>

        <!-- ---- REACTION BAR ---- -->
        <div class="reaction-bar">

            <!-- the reaction-wrap div is what makes the hover picker work (see style.css) -->
            <div class="reaction-wrap">

                <!-- main react button - shows the user's current reaction, or "👍 Like" by default -->
                <button class="react-btn" onclick="return false;">
                    <?php if ($myType): ?>
                        <!-- they already reacted - show which one they picked -->
                        <?php echo $emojis[$myType]; ?> <?php echo ucfirst($myType); ?>
                    <?php else: ?>
                        👍 Like
                    <?php endif; ?>
                </button>

                <!-- emoji picker popup - hidden by default, shown on hover via CSS -->
                <!-- each emoji is a link that reloads the page with ?react=ID&type=TYPE -->
                <div class="reaction-picker">
                    <?php foreach ($emojis as $type => $emoji): ?>
                        <a class="reaction-emoji"
                           href="home.php?react=<?php echo $postid; ?>&type=<?php echo $type; ?>"
                           title="<?php echo ucfirst($type); ?>">
                            <?php echo $emoji; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- reaction summary: shows which emojis were used and the total count -->
            <!-- only shows if at least one person reacted -->
            <?php if ($totalReactions > 0): ?>
            <div class="reaction-summary">
                <?php foreach ($reactionCounts as $type => $n): ?>
                    <?php echo $emojis[$type]; ?> <!-- show just the emoji, not the count per type -->
                <?php endforeach; ?>
                &nbsp;<?php echo $totalReactions; ?> <!-- total count on the right -->
            </div>
            <?php endif; ?>
        </div>
        <!-- ---- END REACTION BAR ---- -->


        <!-- ---- COMMENTS SECTION ---- -->
        <div class="comment-section">

            <!-- display all existing comments for this post -->
            <?php while ($c = mysqli_fetch_assoc($comments)): ?>
                <div class="comment">
                    <div class="comment-bubble">
                        <b><?php echo htmlspecialchars($c['fullname']); ?></b><br>
                        <?php echo htmlspecialchars($c['comment']); ?>
                    </div>
                </div>
            <?php endwhile; ?>

            <!-- comment input form -->
            <!-- the hidden post_id field tells the server which post this comment belongs to -->
            <form method="POST" class="comment-form">
                <input type="hidden" name="post_id" value="<?php echo $postid; ?>">
                <input type="text" name="comment" placeholder="Write a comment...">
                <button type="submit" name="comment_btn">Send</button>
            </form>
        </div>
        <!-- ---- END COMMENTS ---- -->

    </div>
    <?php } // end of posts loop ?>

</div>
</body>
</html>