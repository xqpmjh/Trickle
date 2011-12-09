<?php
include_once 'class.MongoAdapter.php';
include_once 'class.Comment.php';

// configs
$config = array(
        'servers'  => array(
            '172.16.245.126:27017',
        ),
        'database' => 'test',
);
if (isset($_SERVER['REMOTE_ADDR']) and $_SERVER['REMOTE_ADDR'] == '127.0.0.1') {
    // comment object
    $config = array(
        'servers'  => array(
            'localhost:27018',
            'localhost:27019',
            'localhost:27020',
        ),
        'database' => 'test',
        'username' => 'kim',
        'password' => 'kim',
    );
}

try {
    // comment object
    $dbAdapter = new MongoAdapter($config);
    $comment = new Comment($dbAdapter);

    // insert
    if (isset($_POST['a']) and $_POST['a'] == 'insert') {
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
        header('location: index.php');
    }

    // reply
    if (isset($_POST['a']) and $_POST['a'] == 'reply') {
        if (!empty($_POST['content']) and !empty($_POST['cmt_id'])) {
            $data = array(
                'content' => $_POST['content'],
                'comment_id' => $_POST['cmt_id'],
            );
            $comment->saveReply($data);
        }
        header('location: index.php');
    }

    //delete
    if (isset($_GET['action']) and $_GET['action'] == 'delete') {
        $commentId = $_GET['id'];
        $comment->delete($commentId);
        header('location: index.php');
    }
    
    //drop
    if (isset($_GET['action']) and $_GET['action'] == 'drop') {
        $comment->drop();
        header('location: index.php');
    }

    $comments = $comment->findAll();
    //echo '<pre>'; var_dump($comments); echo '</pre>';die;
    $total = $comment->total();

} catch (Exception $e) {
    echo $e->getMessage();
    die;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="description" content="56.com - 全国最大的免费视频分享平台" />
    <meta name="keywords" 	 content="video,视频,上传视频,录制视频,视频下载,56视频,56FLV,FlV" />
    <title>视频留言接口</title>
    <!--第一步/共六步:包含JS LIB -->
    <script type="text/javascript" src="http://www.56.com/js/fly56/lib.js"></script>
    <script type="text/javascript" src="http://www.56.com/js/fly56/o_.js"></script>
    <script type="text/javascript" src="http://www.56.com/js/fly56/oReview.js"></script>
    <script type="text/javascript" sr-c="oReview.js"></script>
</head>
<body>
    <!--第三步/共六步:把留言要加到的地方填上以下内容-->
    <!--留言内容 begin-->
    <br />
    <a href="index.php?action=drop">Drop Comments</a>
    <br /><br />
    
    <div class="border" id="Lword">
    	<div id="mb_review" clas="botop"></div>
	    <a id="CommentPlace" name="CommentPlace"></a>	
	    <div id="LwordRepost" class="reViewForm"></div>
        <div id="LwordContent" style="">
            <div id="leaveWordContenMain" style="">
                <div class="pn">&nbsp;
                    <wbr><span class="disabled">共20页</span>&nbsp;
                    <wbr><span class="disabled">首页</span>&nbsp;
                    <wbr><span class="disabled">上页</span>&nbsp;
                    <wbr><span class="current">1</span>&nbsp;
                    <wbr><a href="javascript:gReF.pg(2)">2</a>&nbsp;
                    <wbr><a href="javascript:gReF.pg(3)">3</a>&nbsp;
                    <wbr><a href="javascript:gReF.pg(4)">4</a>&nbsp;
                    <wbr><a href="javascript:gReF.pg(5)">5</a>&nbsp;
                    <wbr>...&nbsp;<wbr><a href="javascript:gReF.pg(2)">下页</a>&nbsp;
                    <wbr><a href="javascript:gReF.pg(20)">尾页</a>&nbsp;
                    <wbr>
                </div>
                &nbsp;<wbr><span id="cTotal" class="total">共<?php echo $total?>条评论</span>
            </div>

            <?php
            $i = 0;
            function displayTower($comment) {
                $html = '';
                if (isset($comment['comment_ref_ins']) and !empty($comment['comment_ref_ins'])) {
                    $html .= displayTower($comment['comment_ref_ins']);
                }
                if (!empty($comment)) {
                    $deleteLink = '<a href="index.php?action=delete&id=' . $comment['_id'] . '">删除本条？</a>';
                    $content = ($comment['status'] == Comment::STATUS_DELETED ? '<em style="color:gray;">该评论已被删除</em>' : $comment['content']);
                    $html .= '&nbsp;&nbsp;&nbsp;&nbsp;'
                           . $comment['comment_userid'] . ' 于 ' . $comment->created_ataa . ' 说：' . $content . ' ('.$deleteLink.')<br />';
                }
                return $html;
            }

            //echo '<pre>'; var_dump($comments);echo '</pre>';

            foreach ($comments as $cmt) {
                $formId = 'LwordForm_' . $cmt['_id'];
            ?>
    			<div class="LeaveWord">
                	<div class="cmf">
    		            <div style="float:left;">
    			            <a style="font-weight: bold" href="http://xqpmjh.v.56.com" target="_blank"></a>
    			            <a href="http://xqpmjh.56.com" target="_blank"><?php echo $cmt['v_userid']; ?></a>
    					</div>
    					<div class="MsgIco" onclick="_c.sendSms('xqpmjh')"></div>
    					    [<?php echo $cmt['created_at']; ?> 在<a href="http://www.56.com/u89/v_<?php echo $cmt['vid'] ?>.html" target="_blank">
    					    <span class="comSub"><?php echo $cmt['v_name'] ?></span></a> 说:
    				</div>
    				<div class="cmt">
    					<div id="LC_135490492" class="leave">
    					<!-- <img src="http://www.56.com/images/face/a/96.gif" border="0"> -->
    					<wbr><?php echo $cmt['content']; ?></div>
    					&nbsp;
    					<?php echo displayTower($cmt['comment_ref_ins']); ?>
    					
    				</div>
    				<div class="date">
    				    <span class="ope3"><a title="支持" href="javascript:gReF.ding(135490492);" id="ding_btn_135490492" class="up">&nbsp;</a><a href="javascript:;" onmousedown="gReF.ding(135490492)" id="ding_135490492"></a>
    				    <!--<a title="反对" href="javascript:;" id="dao_btn_135490492" onmousedown="gReF.dao(135490492)" class="down">&nbsp;</a><a href="javascript:;" onmousedown="gReF.dao(135490492)" id="dao_135490492">0</a>--></span>
    				    <form name="<?php echo $formId; ?>" id="<?php echo $formId; ?>" action="index.php" method="post" accept-charset="utf-8">
    				        <textarea tabindex="2" rows="8" cols="50" onfocus="" onkeydown="ctlent(this,event);" spanid="auth_img_span_id_bottom" onclick="gReF.face('',this,this);" spanidv="auth_img_span_id" onmousedown="gReF.auth(this);" id="content" name="content"></textarea>
    				        <input type="hidden" name="a" value="reply" />
    				        <input type="hidden" name="cmt_id" value="<?php echo $cmt['_id']; ?>" />
    				        <input type="submit" name="submit" />
    				    </form>
        				<!-- <span class="ope1"><a href="javascript:;" onclick="">回复</a></span> -->
                    </div>
    			</div>
		    <?php } ?>
		
        </div>
        
        <div class="reViewForm" id="LwordPost">
            <form accept-charset="utf-8" action="index.php" name="LwordForm" id="LwordForm" onsubmit="" method="post">
                <div class="lw_post">

                    <h3>我要说两句</h3>
                    <div class="face">
                        <img title="愤怒" alt="愤怒" src="http://www.56.com/images/face/a/angry.gif" spanidv="auth_img_span_id" onclick="gReF.face('[`a_angry`]',this);">
                        <wbr><img title="赞" alt="赞" src="http://www.56.com/images/face/a/cool.gif" spanidv="auth_img_span_id" onclick="gReF.face('[`a_cool`]',this);">
                        <wbr><img title="YY" alt="YY" src="http://www.56.com/images/face/a/heart.gif" spanidv="auth_img_span_id" onclick="gReF.face('[`a_heart`]',this);">
                        <wbr><img title="激动" alt="激动" src="http://www.56.com/images/face/a/lol.gif" spanidv="auth_img_span_id" onclick="gReF.face('[`a_lol`]',this);">
                        <wbr><img title="吐" alt="吐" src="http://www.56.com/images/face/a/35.gif" spanidv="auth_img_span_id" onclick="gReF.face('[`a_35`]',this);">
                        <wbr><img title="开心" alt="开心" src="http://www.56.com/images/face/a/36.gif" spanidv="auth_img_span_id" onclick="gReF.face('[`a_36`]',this);">
                        <wbr><img title="惊讶" alt="惊讶" src="http://www.56.com/images/face/a/53.gif" spanidv="auth_img_span_id" onclick="gReF.face('[`a_53`]',this);">
                        <wbr><img title="伤心" alt="伤心" src="http://www.56.com/images/face/a/96.gif" spanidv="auth_img_span_id" onclick="gReF.face('[`a_96`]',this);">
                    </div>

                    <!--
                    <input type="hidden" id="qt_0" name="quote_content">
                    <input type="hidden" id="qu_0" name="quote_userid">
                    <input type="hidden" name="callback" value="parent.gReF.insertCallback">
                    -->
                    <input type="hidden" name="vid" value="MTQ4NDM4MzA" />
                    <input type="hidden" name="pct" value="1" />
                    <input type="hidden" name="vuid" value="naonao" />
                    <input type="hidden" name="a" value="insert" />
                    <input type="hidden" name="vname" value="哈哈哈" />
                    <textarea tabindex="2" rows="8" cols="50" onfocus="" onkeydown="" spanid="auth_img_span_id_bottom" onclick="" spanidv="auth_img_span_id" onmousedown="" id="content" name="content"></textarea>

                    <div class="loginfo">
                        <p>您好56网友，建议先<a href="javascript:gReF.loginForm();">登录</a>
                        <span>|</span><a target="_blank" href="http://reg.56.com/newreg/register/">注册</a></p>
                    </div>
                    <div class="btn">
                        <p id="auth_img_p_" style="display:none;">
                            <input name="auth_img" tabindex="3" id="auth_img" maxlength="4" type="text" autocomplete="false" spanidv="auth_img_span_id" onmousedown="gReF.auth(this);" style="ime-mode:disabled" size="4"><span id="auth_img_span_id"></span>
                        </p>
                        <p>
                            <input type="submit" value="提交评论" name="postSubmit">
                            <span class="sub_tips">Ctrl+回车 提交</span>
                        </p>
                    </div>

                </div>
            </form>
        </div>
        
    </div>

<!--留言内容 end-->		

</body>
</html>