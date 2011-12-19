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
        // check if is development
        if (IS_DEV) {
            $passResponse = array(true, '提示：测试环境ok！', 'parent.gReF.replyOk();', '');;
        } else {
            $passResponse = CommentValidate::doInsertValid();
        }

        /**
         * if pass validate, do insert
         * get comment user id, for unlogged in user, just name him/her '56com'
         */
        if (true == $passResponse[0]) {
            $commentUserId = CommentValidate::isGuest() ? '56com' : trim(user_id);

            $vid = isset($_POST['vid']) ? g::getUrlId($_POST['vid']) : '';
            $pct = isset($_POST['pct']) ? $_POST['pct'] : 1;
            $vName = isset($_POST['vname']) ? $_POST['vname'] : '';
            $vUserId = isset($_POST['v_userid']) ? urlencode($_POST['v_userid']) : '';
            $content = isset($_POST['content']) ? $_POST['content'] : '';
            $orgin = (isset($_POST['orgin']) and $_POST['orgin'] == 'T') ? $_POST['orgin'] : 'V';

            //$vid = '65273147';
            if (IS_DEV) {
                $status = Comment::STATUS_PENDING;
            } else {
                $status = CommentValidate::checkCommentStatus($content, $vid, $pct);
            }

            $data = array(
                    'vid' => $vid,
                    'pct' => $pct,
                    'v_userid' => $vUserId,
                    'comment_userid' => $commentUserId,
                    'to_userid' => $vUserId,
                    'v_name' => $vName,
                    'content' => $content,
                    'orgin' => $orgin,
                    'status' => $status,
            );
            $response = $comment->saveNew($data);
            
            /**
             * do backup... to keep it simple, so just reuse the old interface for the moment
             * @deprecated should be remove if no backup needed in the future
             */
            $db = new db($scfg['db']);
            $rs = reviewUtf8::insertNew($db,$content,$vUserId,$vid,$commentUserId,$pct,$orgin);

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
        if (!IS_DEV) {
            header('P3P: CP="COR NOI CURa ADMa DSP DEVa PSAa PSDa OUR IND UNI PUR NAV"');
            $valid_key = Auth::MakeAuth(75, 20, 4, 'num', true, '56zvcode');
            header('Location:http://code.auth.56.com/index.php?key=' . $valid_key);
        }
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