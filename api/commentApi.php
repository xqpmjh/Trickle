<?php
include_once '../inc.php';

try {
    // get the action
    $action = isset($_POST['a']) ? $_POST['a'] : '';
    if (!$action) {
        $action = isset($_GET['a']) ? $_GET['a'] : '';
    }

    // comment object
    $dbAdapter = new MongoAdapter($config);
    $comment = new Comment($dbAdapter);

    // insert
    if ('insert' == $action) {
        $passResponse = CommentValidate::doInsertValid(); 
        /**
         * if pass validate, do insert
         * get comment user id, for unlogged in user, just name him/her '56com'
         */
        if (CommentValidate::isGuest()) {
            $commentUserId = trim(user_id);
        } else {
            $commentUserId = '56com';
        }

        if (true == $passResponse[0]) {
            $vid = g::getUrlId($_POST['vid']);
            //$vid = '65273147';
            $data = array(
                    'vid' => $_POST['vid'],
                    'pct' => $_POST['pct'],
                    'v_userid' => $_POST['vuid'],
                    'comment_userid' => $commentUserId,
                    'to_userid' => $_POST['vuid'],
                    'v_name' => $_POST['vname'],
                    'content' => $_POST['content'],
                    'status' => CommentValidate::checkCommentStatus($_POST['content'], $vid, $_POST['pct']),
            );
            $response = $comment->saveNew($data);
        }
        $jsFunc = $passResponse[2];
        echo g::msg($passResponse[1]);
    }

    // reply
    if ('reply' == $action) {
        if (!empty($_POST['content']) and !empty($_POST['cmt_id'])) {
            $data = array(
                'content' => $_POST['content'],
                'comment_id' => $_POST['cmt_id'],
            );
            $response = $comment->saveReply($data);
            if ($response) {
                echo g::msg("成功:你的评论发表成功，请“确定”后查看！");
                header('location: ./../index.php');
            }
        } else {
            header('location: ./../index.php');
        }
    }

    //delete
    if ('delete' == $action) {
        $commentId = $_GET['id'];
        $comment->delete($commentId);
        header('location: ./../index.php');
    }

    //insist on
    if ('insist' == $action) {
        $commentId = $_GET['id'];
        $comment->insist($commentId);
        header('location: ./../index.php');
    }

    //drop
    if ('drop' == $action) {
        $comment->drop();
        header('location: ./../index.php');
    }

    /**
     * generate auth image
     * PS: only works on testing server or production
     */
    if ('getAuth' == $action) {
        header('P3P: CP="COR NOI CURa ADMa DSP DEVa PSAa PSDa OUR IND UNI PUR NAV"');
        $valid_key = Auth::MakeAuth(75, 20, 4, 'num', true, '56zvcode');
        header('Location:http://code.auth.56.com/index.php?key=' . $valid_key);
        die();
    }

} catch (Exception $e) {
    echo $e->getMessage();
    die;
}
?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <script type="text/javascript">
    try {
        document.domain = "56.com";
        <?php echo (isset($jsFunc) ? $jsFunc : ''); ?>
    } catch (e) {
        //
    }
    </script>
</head>
<body>
</body>
</html>