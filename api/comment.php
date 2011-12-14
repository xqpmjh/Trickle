<?php
include_once '../inc.php';

try {
    $action = isset($_POST['a']) ? $_POST['a'] : '';
    if (!$action) {
        $action = isset($_GET['a']) ? $_GET['a'] : '';
    }

    // comment object
    $dbAdapter = new MongoAdapter($config);
    $comment = new Comment($dbAdapter);

    $authImg = isset($_POST['auth_img_input']) ? strtolower($_POST['auth_img_input']) : '';

    // insert
    if ('insert' == $action) {
        $sess = new SessionHandle('56zvcode');
        $sess -> Session_Start();
        if (($authImg != strtolower($_SESSION['auth']) || empty($_SESSION['auth']) || !$authImg) && $pct != 6 && $pct != 22) {
            if (STAT3_ON === TRUE) {
                file_get_contents('http://stat3.corp.v-56.com/player.htm?s=revauth');
                file_put_contents("/tmp/auth_err.txt","cookie:{$_COOKIE['member_id']} comment_userid:{$comment_userid} auth_img:{$authImg} _SESSION['auth']:{$_SESSION['auth']} _COOKIE['auth_img_limit']:{$_COOKIE['auth_img_limit']} \n",FILE_APPEND);
            }
            $rs = array(0, "提示:请输入正确的验证码!", "", "E_001");
            $jsFunc = "parent.gReF.changeAuth();";
            echo g::msg($rs[1]);
        }
        
        if (!empty($_POST['content']) and !empty($_POST['vid'])) {
            $data = array(
                'vid' => $_POST['vid'],
                'pct' => $_POST['pct'],
                'v_userid' => $_POST['vuid'],
                'comment_userid' => 'xqpmjh', 
                'to_userid' => $_POST['vuid'],
                'v_name' => $_POST['vname'],
                'content' => $_POST['content'],
            );
            $comment->saveNew($data);
        }
    }

    // reply
    if ('reply' == $action) {
        if (!empty($_POST['content']) and !empty($_POST['cmt_id'])) {
            $data = array(
                'content' => $_POST['content'],
                'comment_id' => $_POST['cmt_id'],
            );
            $comment->saveReply($data);
        }
    }

    //delete
    if ('delete' == $action) {
        $commentId = $_GET['id'];
        $comment->delete($commentId);
    }

    //insist on
    if ('insist' == $action) {
        $commentId = $_GET['id'];
        $comment->insist($commentId);
    }
    
    //drop
    if ('drop' == $action) {
        $comment->drop();
    }

    /**
     * generate auth image
     * @todo should use self auth function to generate, it's only for testing for the moment
     * @deprecated http://comment.56.com/new/review/insert.utf8.php?a=getAuth
     */
    if ('getAuth' == $action) {
        // for local testing
        if (isset($_SERVER['REMOTE_ADDR']) and $_SERVER['REMOTE_ADDR'] == '127.0.0.1') {
            $sess = new SessionHandle('56zvcode');
			$sess->Session_Start();
			$_SESSION['auth'] = '1111';
        } else {
            header('P3P: CP="COR NOI CURa ADMa DSP DEVa PSAa PSDa OUR IND UNI PUR NAV"');
            $valid_key = Auth::MakeAuth(75, 20, 4, 'num', true, '56zvcode');
            header('Location:http://code.auth.56.com/index.php?key=' . $valid_key);
            //header('Location: http://comment.56.com/new/review/insert.utf8.php?a=getAuth');
        }
        die();
    }

    if (!isset($jsFunc)) {
        header('location: http://localhost/trickle/index.php');
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
    document.domain = "56.com";
    <?php echo isset($jsFunc) ? $jsFunc : ''; ?>
    </script>
</head>
<body>
</body>
</html>