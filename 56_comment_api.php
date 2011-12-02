<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
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
<!--第二步/共六步:包含CSS LIB (这里可以先试一下,不能正常显示,才加这些样式)-->
<style type="text/css">
/* 评论(new LeaveWord) */
#Lword {font-size:12px;}
#Lword .red{color:#FF0000}
.LeaveWord 					{margin:15px auto 0 auto;text-align:left;overflow:hidden;}
.LeaveWord .cmf 			{padding:6px 8px 3px 8px;background:#ededed;vertical-align:top;word-wrap:break-word;}
.LeaveWord .cmf .MsgIco		{float:left;margin:2px 4px;background:url(img/xinxi.gif) no-repeat;width:13px;height:8px;cursor:pointer!important;cursor:hand;}
.LeaveWord .cmt				{color:#666;margin-top:2px;background:url(img/userinbg1.gif) repeat-x;overflow:hidden;_height:1%;}
.LeaveWord .cmt .leave		{text-indent:24px;margin:10px 4px;line-height:20px;WORD-BREAK: break-all; WORD-WRAP: break-word;}
.LeaveWord .cmt .quote		{clear:both;color:#000;margin:10px 8px 0 8px;padding:4px;background:#FFF;border:1px solid #ADC4E5;}
.LeaveWord .cmt .quote p 	{text-indent:24px;margin:8px 0 0 0;line-height:20px;}
.LeaveWord .cmt .revert		{clear:both;color:#000;margin:0 8px 0 8px;padding:4px;background:#FFFDF8;border:1px solid #FFD37A;}
.LeaveWord .cmt .revert p   {text-indent:24px;margin:8px 0 0 0;line-height:20px;}
.LeaveWord .cmt .adduction  {clear:both;color:#000;margin:16px 8px 0 8px;padding:4px;background:#fff;border:1px solid #b0c6e6;}
.LeaveWord .cmt .adduction p{text-indent:24px;margin:8px 0 0 0;line-height:20px;}
.LeaveWord .cmt .UserImg	{float:left;margin:6px 4px;}
.LeaveWord .cmt .UserImg img{width:60px;height:60px;border:1px solid #CCC;padding:1px; background:#FFF;}
.LeaveWord .date			{color:#999;text-align:right;margin:8px 2px 0 0; }
.LeaveWord .ope1			{background:url(img/bi.gif) no-repeat 0 1px;width:9px;height:9px;padding-left:12px}
.LeaveWord .ope2			{background:url(img/yinyong.gif) no-repeat 0 1px;width:8px;height:9px;padding-left:12px}
.LeaveWord .ope3			{background:url(img/del.gif) no-repeat 0 1px;width:9px;height:9px;padding-left:12px}
</style>
<!--第三步/共六步:把留言要加到的地方填上以下内容-->
<!--留言内容 begin-->
<div class="border" id="Lword">
	<div id="mb_review" clas="botop"></div>
	<a id="CommentPlace" name="CommentPlace"></a>						
	<div id="LwordRepost" class="reViewForm"></div>
	<div id="LwordContent" style="display:none;">
		<div class="SysKuan tright">[%=nextPage%]</div>						
		<!--%begin_0 key="data"%-->
			<div class="LeaveWord">
				<div class="cmf">
					<div style="float:left;">
						<a style="font-weight: bold" href="/*%=userSpace%*/" target="_blank">[%=userName%]</a>
					</div>
					<div class="MsgIco" onclick="/*%=sendMsg%*/"></div> [[%=commentTime%]] [%=dataNote%] 说:
				</div>
				<div class="cmt">
					[%=UserImg%]
					<div id="LC_/*%=id%*/" class="leave">[%=content%]</div>
					[%=revert%]
				</div>
				<div class="date">[%=commentOption%]</div>
			</div>
		<!--%end_0%-->
		<div class="SysKuan tright">[%=nextPage%]</div>
	</div>										
</div>
<!--留言内容 end-->		
<!--第四步/共六步:设定好留言参数-->
<script type="text/javascript">
var user_id='xqpmjh';//用户名 如用户dreamxyp@56.com 这里写dreamxyp 
try{document.domain="56.com";}catch (e){}
gReF.set({
			"gFace":"a",//表情 可选 a b
			"a":'flv', //模式 user 		加载单个用户所有留言
						//     flv 			加载单个flv或专辑下的留言(这里下面id不能为空)
						//     user_only	加载只对用户的留言 不示显对flv或专辑下的留言
						//     me			用户发表出去的留言 
			"open":1,	//发表留言的输入框 是否打开 0关 1开
			"product":1,//产品类型 1,上传视频 2,录制视频 3,相册视频 4,专辑
			"id":'NjQ5NDA0NjQ',		//模式为flv时 产品对ID
			"user_id":user_id,//用户名
			"pageRows":20//, //每一页显示多少个留言 可选5 10 20,
			//"insert":"insert.php?charset=utf8"   // 以utf-8加载，如果按gbk,这行可以注释掉
			//"load":"load.new.php"
		});
<!--第五步/共六步:在设定完触发加载留言事件 -->
$(function(){gReF.pg();});//$(function(){gReF.pg();}); 也可以是gReF.pg();不过这样写就要写在页面最后
</script>
<!--第六步/共六步:在页面最后加上以下空的iframe-->
<iframe name="add_favorite" id="add_favorite" src="http://www.56.com/domain.html" marginWidth="0" marginHeight="0" frameBorder="0" width="0" scrolling="0" height="0"></iframe>
</body>
</html>