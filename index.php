<?php
require_once 'inc.php';

try {
    // comment object
    $dbAdapter = new MongoAdapter($config);
    $comment = new Comment($dbAdapter);

    // get pager
    if (isset($_GET['page'])) {
        $page = (int)$_GET['page'];
    } else {
        $page = 1;
    }
    $limit = 2;

    $comments = $comment->findAll($page, $limit);
    
    //echo '<pre>'; var_dump($comments); echo '</pre>';die;
    $totalNbComments = $comment->total();
    $totalNbPages = ceil($totalNbComments / $limit); 
    
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
    <script type="text/javascript" src="http://s1.56img.com/script/lib/jquery/jquery-1.4.4.min.js"></script>
    <!-- <script type="text/javascript" src="http://s2.56img.com/script/page/common/v3/o_utf8.js"></script> -->
    <script type="text/javascript" src="http://s2.56img.com/script/page/common/v3/o_utf8.js?v=1215v1"></script>
    <script type="text/javascript" src="http://s2.56img.com/script/page/login/v3/login_2011_v1.js?v=1215v2"></script>

    <script type="text/javascript" src="./oReview.js"></script>
</head>
<body>
    <!--第三步/共六步:把留言要加到的地方填上以下内容-->
    <!--留言内容 begin-->

    <script type="text/javascript">
    try {
        document.write('Is logged in : ' + gToolF.gIsLogin() + "<br />");
        document.write('User id : ' + usr.gLoginUser() + "<br />");
    } catch (e) {
        //
    }
    </script>
    
    <a href="api/commentApi.php?a=drop">Drop Comments</a>
    &nbsp;<br /><br />
    
    <div class="border" id="Lword">
    	<div id="mb_review" class="botop"></div>
	    <a id="CommentPlace" name="CommentPlace"></a>	
	    <div id="LwordRepost" class="reViewForm"></div>
        <div id="LwordContent" style="">
            <div id="leaveWordContenMain" style="">
                <div class="pn">&nbsp;
                    <?php
                    $pagesHtml = '<span class="disabled">共' . $totalNbPages . '页</span>&nbsp;'
                               . '<span class="disabled"><a href="index.php">首页</a></span>&nbsp;';
                    if ($page > 1) {
                        $pagesHtml .= '<a href="index.php?page=' . ($page - 1) . '">上页</span>&nbsp;';
                    }
                    for ($curPage = 1; $curPage <= $totalNbPages; $curPage++) {
                        if ($curPage == $page) {
                            $pagesHtml .= '<span class="current">' . $curPage . '</span>&nbsp;';
                        } else {
                            $pagesHtml .= '<a href="index.php?page=' . ($curPage) . '">' . $curPage . '</a>&nbsp;';
                        } 
                    }
                    if ($page < $totalNbPages) {
                        $pagesHtml .= '<a href="index.php?page=' . ($page + 1) . '">下页</span>&nbsp;';
                    }
                    $pagesHtml .= '<a href="index.php?page=' . $totalNbPages . '">尾页</a>&nbsp;';
                    echo $pagesHtml;
                    ?>
                    &nbsp;<span id="cTotal" class="total">共<?php echo $totalNbComments ?>条评论</span>
                </div> 
            </div>
            &nbsp;

            <?php
            $i = 0;
            function displayTower($comment) {
                $html = '';
                if (isset($comment['comment_ref_ins']) and !empty($comment['comment_ref_ins'])) {
                    $html .= displayTower($comment['comment_ref_ins']);
                }
                if (!empty($comment)) {
                    $deleteLink = '<a href="api/commentApi.php?a=delete&id=' . $comment['_id'] . '">删除</a>';
                    $content = ($comment['status'] == Comment::STATUS_DELETED ? '<em style="color:gray;">该评论已被删除</em>' : $comment['content']);
                    $html .= '&nbsp;&nbsp;&nbsp;&nbsp;'
                           . '56' . $comment['locate'] . '网友  '
                           . $comment['comment_userid'] . ' 于 ' . $comment->created_ataa 
                           . ' 说：' . $content . '  ' . $deleteLink . '<br />';
                }
                return $html;
            }

            //echo '<pre>'; var_dump($comments);echo '</pre>';

            foreach ($comments as $comment) {
                $formId = 'LwordForm_' . $comment['_id'];
                $insistLink = '<a href="api/commentApi.php?a=insist&id=' . $comment['_id'] . '">顶[' . (int)$comment['nb_insist'] . ']</a>';
            ?>
    			<div class="LeaveWord">
                	<div class="cmf">
    		            <div style="float:left;">
    			            <a style="font-weight: bold" href="http://xqpmjh.v.56.com" target="_blank"></a>
    			            <a href="http://xqpmjh.56.com" target="_blank"><?php echo $comment['v_userid']; ?></a>
    					</div>
    					<div class="MsgIco" onclick="_c.sendSms('xqpmjh')"></div>
    					    <?php echo $comment['created_at']; ?> 在 <a href="http://www.56.com/u89/v_<?php echo $comment['vid'] ?>.html" target="_blank">
    					    <span class="comSub"><?php echo $comment['v_name'] ?></span></a> 说 (<?php echo 'status : '.$comment['status'] ?>) :
    				</div>
    				<div class="cmt">
    					<div id="LC_135490492" class="leave">
    					<!-- <img src="http://www.56.com/images/face/a/96.gif" border="0"> -->
    					<?php echo $comment['content']; ?></div><br /><?php echo displayTower($comment['comment_ref_ins']); ?>
    					
    				</div>
    				<div class="date">
    				    <span class="ope3">
    				        <?php echo $insistLink ?>
    				        <!-- <a title="支持" href="gReF.ding(135490492)" id="ding_btn_135490492" class="up"></a>
    				        <a href="javascript:;" onmousedown="gReF.ding(135490492)" id="ding_135490492"></a> -->
    				        <!--<a title="反对" href="javascript:;" id="dao_btn_135490492" onmousedown="gReF.dao(135490492)" class="down">&nbsp;</a><a href="javascript:;" onmousedown="gReF.dao(135490492)" id="dao_135490492">0</a>-->
    				    </span>
    				    <form name="<?php echo $formId; ?>" id="<?php echo $formId; ?>" action="api/commentApi.php" method="post" accept-charset="utf-8">
    				        <textarea tabindex="2" rows="2" cols="50" onfocus="gReF.focusReplyTop(this);" onkeydown="gToolF.ctrlEnter(this, event);" 
    				        spanid="auth_img_span_id_bottom" onclick="gReF.face('',this,this);" spanidv="auth_img_span_id" onmousedown="gReF.openAuthBottom(this);" name="content"></textarea>
    				        <input type="hidden" name="a" value="reply" />
    				        <input type="hidden" name="cmt_id" value="<?php echo $comment['_id']; ?>" />
    				        <input type="submit" name="postSubmit" />
    				    </form>
        				<!-- <span class="ope1"><a href="javascript:;" onclick="">回复</a></span> -->
                    </div>
    			</div>
    			<br /><hr />
		    <?php } ?>
		
        </div>
        
        <div class="reViewForm" id="LwordPost">
            <form accept-charset="utf-8" action="api/commentApi.php" name="LwordForm" id="LwordForm" onsubmit="document.charset='utf-8';return gReF.checkSubmit(this);" method="post" target="add_favorite">
                <div class="lw_post">

                    <h3>我要说两句</h3>
                    <div class="face">
                        <img title="愤怒" alt="愤怒" src="http://www.56.com/images/face/a/angry.gif" spanidv="auth_img_span_id" onclick="gReF.getFace('[`a_angry`]',this);">
                        <img title="赞" alt="赞" src="http://www.56.com/images/face/a/cool.gif" spanidv="auth_img_span_id" onclick="gReF.getFace('[`a_cool`]',this);">
                        <img title="YY" alt="YY" src="http://www.56.com/images/face/a/heart.gif" spanidv="auth_img_span_id" onclick="gReF.getFace('[`a_heart`]',this);">
                        <img title="激动" alt="激动" src="http://www.56.com/images/face/a/lol.gif" spanidv="auth_img_span_id" onclick="gReF.getFace('[`a_lol`]',this);">
                        <img title="吐" alt="吐" src="http://www.56.com/images/face/a/35.gif" spanidv="auth_img_span_id" onclick="gReF.getFace('[`a_35`]',this);">
                        <img title="开心" alt="开心" src="http://www.56.com/images/face/a/36.gif" spanidv="auth_img_span_id" onclick="gReF.getFace('[`a_36`]',this);">
                        <img title="惊讶" alt="惊讶" src="http://www.56.com/images/face/a/53.gif" spanidv="auth_img_span_id" onclick="gReF.getFace('[`a_53`]',this);">
                        <img title="伤心" alt="伤心" src="http://www.56.com/images/face/a/96.gif" spanidv="auth_img_span_id" onclick="gReF.getFace('[`a_96`]',this);">
                    </div>

                    <!--
                    <input type="hidden" id="qt_0" name="quote_content">
                    <input type="hidden" id="qu_0" name="quote_userid">
                    <input type="hidden" name="callback" value="parent.gReF.insertCallback">
                    -->
                    <input type="hidden" name="vid" value="MTQ4NDM4MzA" />
                    <input type="hidden" name="pct" value="1" />
                    <input type="hidden" name="v_userid" value="naonao" />
                    <input type="hidden" name="a" value="insert" />
                    <input type="hidden" name="vname" value="哈哈哈" />
                    
                    <textarea tabindex="2" rows="2" cols="50" onfocus="gReF.focusReplyBottom(this);" onkeydown="gToolF.ctrlEnter(this, event);" 
                    spanid="auth_img_span_id_bottom" onclick="" spanidv="auth_img_span_id" onmousedown="" id="content" name="content"></textarea>

                    <div class="loginfo">
                        <p>您好56网友，建议先<a href="javascript:void(0);" onclick="gToolF.showLoginForm();">登录</a>
                        <span>|</span><a target="_blank" href="http://reg.56.com/newreg/register/">注册</a></p>
                    </div>
                    
                    <div id="regArea" style="display:none"></div>
                    
                    <div class="btn">
                        <p>
                            <div id="auth_img_p_bottom_comment" style="display:none;">
                                <input name="auth_img_input" id="auth_img_input" maxlength="4" type="text" autocomplete="false" spanidv="auth_img_span_id_top" 
                                    onmousedown="gReF.openAuthBottom(this);" style="ime-mode:disabled;" size="4" value="输入验证码" onfocus="gReF.clearAuthValue();" />
                                <span id="auth_img_span_id_top"></span>
                            </div>
                            <input type="submit" value="提交评论" name="postSubmit" id="postSubmit">
                            <span class="sub_tips">Ctrl+回车 提交</span>
                        </p>
                    </div>

                </div>
            </form>
        </div>
        
    </div>

<!--留言内容 end-->		

    <iframe name="add_favorite" id="add_favorite" src="http://www.56.com/domain.html" marginwidth="0" marginheight="0" frameborder="0" width="0" scrolling="0" height="0"></iframe>
    
</body>
</html>