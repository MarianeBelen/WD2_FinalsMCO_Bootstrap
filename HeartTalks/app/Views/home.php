<!DOCTYPE html>
<html>
<head>
    <title>HeartTalks</title>
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
</head>
<body>

<div class="topbar">💜 HeartTalks</div>

<div class="topmenu">
    Welcome <b><?= session()->get('fullname') ?></b>
    |
    <a href="<?= base_url('/home') ?>">Home</a>
    |
    <a href="<?= base_url('/profile') ?>">Profile</a>
    |
    <a href="<?= base_url('/logout') ?>">Logout</a>
</div>

<div class="feed">

    <!-- POST BOX -->
     <!-- form sends POST request to Home controller -->
    <div class="postbox">
        <form method="POST" action="<?= base_url('home') ?>">
            <textarea name="content" rows="3" placeholder="What's on your heart?" required></textarea>
            <button type="submit" name="post_btn">Post</button>
        </form>
    </div>

    <!-- LOOP THROUGH POSTS -->
    <?php foreach ($posts as $row): ?>

        <?php
        // load models (used for reactions and comments per post)

            $postid = $row['id'];

            // get all reactions for this post
            $reactions = $reactionModel->where('post_id', $postid)->findAll();

            // count reactions per type
            $reactionCounts = [];
            foreach ($reactions as $r) {
                $reactionCounts[$r['type']] = ($reactionCounts[$r['type']] ?? 0) + 1;
            }

            $totalReactions = array_sum($reactionCounts);

             // check current user's reaction on this post
            $myReaction = $reactionModel
                ->where('user_id', $userid)
                ->where('post_id', $postid)
                ->first();

            // get comments with user names
            $comments = $commentModel
                ->select('comments.*, users.fullname')
                ->join('users', 'users.id = comments.user_id')
                ->where('post_id', $postid)
                ->orderBy('comments.id', 'ASC')
                ->findAll();

            // emoji mapping for reactions
            $emojis = [
                'like'=>'👍',
                'love'=>'❤️',
                'haha'=>'😂',
                'sad'=>'😢',
                'angry'=>'😡'
            ];
        ?>

        <div class="post">

             <!-- POST AUTHOR -->
            <h3><?= esc($row['fullname']) ?></h3>
            <!-- POST CONTENT -->
            <p><?= esc($row['content']) ?></p>

            <!-- REACTIONS -->
            <div class="reaction-bar">

                <div class="reaction-wrap">

                <!-- main reaction button -->
                    <button class="react-btn">
                        <?php if ($myReaction): ?>
                            <?= $emojis[$myReaction['type']] ?> <?= ucfirst($myReaction['type']) ?>
                        <?php else: ?>
                            👍 Like
                        <?php endif; ?>
                    </button>

                    <!-- reaction picker -->
                    <div class="reaction-picker">
                        <?php foreach ($emojis as $type => $emoji): ?>
                            <a href="<?= base_url('home?react='.$postid.'&type='.$type) ?>">
                                <?= $emoji ?>
                            </a>
                        <?php endforeach; ?>
                    </div>

                </div>

                <!-- reaction summary -->
                <?php if ($totalReactions > 0): ?>
                    <div>
                        <?php foreach ($reactionCounts as $type => $n): ?>
                            <?= $emojis[$type] ?>
                        <?php endforeach; ?>
                        <?= $totalReactions ?>
                    </div>
                <?php endif; ?>

            </div>

            <!-- COMMENTS -->
            <div class="comment-section">

             <!-- display comments -->
                <?php foreach ($comments as $c): ?>
                    <div>
                        <b><?= esc($c['fullname']) ?></b><br>
                        <?= esc($c['comment']) ?>
                    </div>
                <?php endforeach; ?>

                <!-- add comment form -->
                <form method="POST" action="<?= base_url('/home') ?>">
                    <input type="hidden" name="post_id" value="<?= $postid ?>">
                    <input type="text" name="comment" placeholder="Write a comment...">
                    <button type="submit" name="comment_btn">Send</button>
                </form>

            </div>

        </div>

    <?php endforeach; ?>

</div>

</body>
</html>