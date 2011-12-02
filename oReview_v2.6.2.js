if(browser == undefined){
	var browser =(function getBrowser() {
		var b = navigator.userAgent.toLowerCase();
		return { 
			safari: /webkit/.test(b),
			opera: /opera/.test(b),
			ie6: /msie 6/.test(b) && !/opera/.test(b),
			ie7: /msie 7/.test(b) && !/opera/.test(b),
			msie: /msie/.test(b) && !/opera/.test(b),
			mozilla: /mozilla/.test(b) && !/(compatible|webkit)/.test(b)
		};
	})();
}


var ubb={
	set:{},
	en:function(str){
		str = str.replace(/<[^<]*icon_([^<]*)\.gif[^<]*>/ig,'[`a_$1`]')
			.replace(/<[^<]*face\/([^\/]*)\/([^\/]*)\.gif[^<]*>/ig,'[`$1_$2`]')
			//.replace(/<(\/?)p>/ig,"[$1p]")
			.replace(/<br\s?\/?>/ig,"[br]")
			.replace(/(\[br\]){1,}/igm,"[br]")
			.replace(/<a[^>]+u_(\w+)\.html[^>]*?>(.*?)<\/a>/ig,"[user=$1]$2[/user]")
			.replace(/<[^<]*em\s*class=\"face\sface_*(\w*)\">/ig,"[`x_$1`]")
		    .replace(/<([^<]*)>/ig,"");
		return str;
	},
	de:function(str){
		str=str.replace(/(\$`|\$')/igm,"*")
				.replace(/<[^<]*icon_([^<]*)\.gif[^<]*>/ig,'[`a_$1`]')
				.replace(/<[^<]*face\/([^\/]*)\/([^\/]*)\.gif[^<]*>/ig,'[`$1_$2`]')
				.replace(/<br \/>/ig,'\n').replace(/</g,"&lt;").replace(/>/g,"&gt;")
				.replace(/(\n)+/igm,"<br>")
				.replace(/\[quote(.*?)\]((.|\s)*?)\[\/quote\1\]/igm,"<div class=\"quote\" id=\"quote_div_$1\">$2</div>")
				.replace(/\[quote\]((.|\s)*?)\[\/quote\]/igm,"<div class=\"quote\">$1</div>")
				.replace(/\[user\](.*?)\[\/user\]/igm,"<a href=\"http://www.56.com/w/u_$1.html\" target=\"_blank\">$1</a>")
				.replace(/\[user=(.*?)\](.*?)\[\/user\]/igm,"<a href=\"http://www.56.com/w/u_$1.html\" target=\"_blank\">$2</a>")
				.replace(/\[rev=(.*?)\]((.|\n|\f|\r|\t|\v| )*?)\[\/rev=(.*?)\]/igm,"<div class=\"revert\"><p><a href=\"http://www.56.com/w/u_$1.html\" target=\"_blank\">$4</a> 回复说：</p>$2</div><br>")
				.replace(/\[u\](.*?)\[\/u\]/igm,"<u>$1</u>")
				.replace(/\[i\](.*?)\[\/i\]/igm,"<i>$i</i>")
				.replace(/\[b\](.*?)\[\/b\]/igm,"<b>$1</b>")
				.replace(/\[p\](.*?)\[\/p\]/igm,"<p>$1</p>")
				.replace(/\[hot\s{1}(.*?)\](.*?)\[\/hot\]/igm,"<a class='so' title='搜索$2' target='_blank' onclick=\"setStat('cliso',1000)\" href='http://so.56.com/video/$1/'>$2</a>")
				.replace(/(\[br\])+/igm,"<br>")
                                .replace(/\[`([x])_(.*?)`\]/ig,"<em class=\"face face_$2\" ></em><wbr />")
				.replace(/\[`([A-Za-z0-9])_(.*?)`\]/ig,"<img src=\"http://s1.56img.com/images/face/$1/$2.gif\" border=\"0\" /><wbr />")
				.replace(/\[.+?\]/g,"");
		
		if(/(群)|(资料)|(说明)|(信息)|(赚)|(钱)|(职业)|(详细)|(每天)/i.test(str)){
			str=str.replace(/零/ig,"0").replace(/壹/ig,"1").replace(/贰/ig,"2").replace(/叁/ig,"3").replace(/肆/ig,"4").replace(/伍/ig,"5").replace(/陆/ig,"6").replace(/柒/ig,"7").replace(/捌/ig,"8").replace(/玖/ig,"9").replace(/○/ig,"0").replace(/一/ig,"1").replace(/二/ig,"2").replace(/三/ig,"3").replace(/四/ig,"4").replace(/五/ig,"5").replace(/六/ig,"6").replace(/七/ig,"7").replace(/八/ig,"8").replace(/九/ig,"9").replace(/０/ig,"0").replace(/１/ig,"1").replace(/２/ig,"2").replace(/３/ig,"3").replace(/４/ig,"4").replace(/５/ig,"5").replace(/６/ig,"6").replace(/７/ig,"7").replace(/８/ig,"8").replace(/９/ig,"9").replace(/(\d+)(\s*)/ig,"$1").replace(/(\d{2,2}?)(\d{1,7})(\d{2,2}?)/ig,"$1****$3");
		}
		str=str.replace("@41c11eea9ef5a5cc@","<p class=\"tips1\"><a target=\"_blank\" href=\"http://www.qq.com/\">[此评论来自腾讯网]</a></p>"); 
		return str;
	},
	heart:function(str)
	{
		//http://www.56.com/h82/u_aiguozhe188.html
		return str.replace(/(<[^<]*cheart\.gif[^<]*>)/ig,'&nbsp;<a href="http://www.56.com/h44/u_torchgz.html" title="火炬传递拍客征集" target="_blank">$1</a>');
	}
};

var gReCfg={
		"host":"http://comment.56.com/new/review/",
		"div":"Lword",
		"gFace":'a',
		"target":"add_favorite",
		"insert":"insert.utf8.php",
		"load":"load.utf8.php",
		"userFace":0,
		"product":0,
		"commentFrom":"qCommentsForm",
		"Repost":"LwordPost",
		"Content":"Lword",
		"ContentMain":"leaveWordContenMain",
		'commentsCorn':"qCommentsCorn",
		"from":"LwordForm",
		"LC_id":"LC_",
	  "LC_id_fd":"LC_fd_",
		"open":true,
		"otherVars":"",
		"grand":0,
		"pg":1,
		"a":"flv",
		"show":1,
		"id":"0",
		"user_id":"56com",
		'lw_page':'lw_page',
		"pageRows":"20",
		"maxPage":5,
		"Cookie":'sReview',
		"oFlv_score":"",
		"can_score":true,
		"closeTopFormOnShowFastReplyForm":false,
		"forceInsertOnFastReply":true,
		"showFaceOnFastReply":true,
		"showBottomForm":true,
		"allowGuest":true,
		"auth_img":false,//默认是否需要验证码
		"guestName":"56网友",
		"reName":"回复",
		"guestLink":"http://www.56.com/w11/space_index.phtml",
		"quickPost":true,//是否开启快捷评论
		"quickPostId":"lw_qpost",//快捷评论的ID
		"quickPostDftCont":"我来说两句...",
		"loginFunction":"show_login()"
	};
	
																		
var gFace={
		"x":['good','fil','trick','laugh', 'indecent', 'badluck', 'drop', 'grief','lovely','rage','scare','sleep'],
		"desc_a":["赞","来电","淫笑","偷笑","挖鼻孔","衰","流汗","伤心","可怜","气愤","惊吓","困了"],
		"get":function(a, b){
				var i,rs=[],a=a||gReCfg.gFace||'a';
				o = this[a];
				if(o.length > 0){
					rs.push("<h4 onclick=\"showFaceBox();\"><em title='赞' class='face face_good'>赞</em>表情</h4>");
					rs.push("<div class='lw_face_box' id='lw_face_box'>");
				}
				for(i=0; i < o.length; i++){
					rs.push('<em class="face face_' + o[i] + '" title="'+this.desc_a[i]+'" alt="'+this.desc_a[i]+'"  onclick="gReF.face(\'[`'+a+'_'+o[i]+'`]\',this);" />' + this.desc_a[i] + '</em>');
				}
				if(o.length > 0){
					rs.push("<a href='javascript:;' class='close' onClick=\"$('.lw_face_box').hide()\">x</a>");
					rs.push("</div>");
				}
				return rs.join('<wbr />');
		}
};

var gFace_comment={
		"x":['good','fil','trick','laugh', 'indecent', 'badluck', 'drop', 'grief','lovely','rage','scare','sleep'],
		"desc_a":["赞","来电","淫笑","偷笑","挖鼻孔","衰","流汗","伤心","可怜","气愤","惊吓","困了"],
		"get":function(a, b){
				var i,rs=[],a=a||gReCfg.gFace||'a';
				o = this[a];
				if(o.length > 0){
					rs.push("<h4 onclick=\"showComnentFaceBox();\"><em title='赞' class='face face_good'>赞</em>表情</h4>");
					rs.push("<div class='lw_face_box' id='lw_face_box_comment'>");
				}
				for(i=0; i < o.length; i++){
					rs.push('<em class="face face_' + o[i] + '" title="'+this.desc_a[i]+'" alt="'+this.desc_a[i]+'"  onclick="gReF.face(\'[`'+a+'_'+o[i]+'`]\',this);" />' + this.desc_a[i] + '</em>');
				}
				if(o.length > 0){
					rs.push("<a href='javascript:;' class='close' onClick=\"$('.lw_face_box').hide()\">x</a>");
					rs.push("</div>");
				}
				return rs.join('<wbr />');
		}
};


	
var gReF={
	"cacheTxt":'',
	"openId":0,
	"navigator":-1,
	"userLoginYN":0,
	"leaveWordRawTpl":"",
	"isPost":false,
	"loadTimes":0,
	"usingQuickPost":false,//在用快捷回复
	"isTyping":false,//是否正在输入评论，用于输入评论的时候暂停广告轮换，减少对键入的影响，评论完再恢复轮换。
	"isTop":false,//是否顶部评论
	"formTpl":function(type){
		var type = type||3;
		var tabindex = parseInt(this.openId%99 + 1);
	
		var rs = '\
		  <div class="lw_post"  id="qCommentsForm">\
			<form accept-charset="utf-8" action="'+this.getHost()+gReCfg.insert+'" name="'+gReCfg.from+(this.openId ? '_'+this.openId : '')+'" id="'+gReCfg.from+(this.openId ? '_'+this.openId : '')+'" onsubmit="document.charset=\'utf-8\';return gReF.submit(this);" method="post" target="'+gReCfg.target+'">\
					<!-- lw_post -->\
						<input type="hidden" id="qt_'+this.openId+'" name="quote_content">\
						<input type="hidden" id="qu_'+this.openId+'" name="quote_userid">\
						<input type="hidden" name="callback" value="parent.gReF.insertCallback">\
						<input value="'+gReCfg.user_id+'" name="v_userid" type="hidden" />\
						<input value="insert" name="a" type="hidden" />\
						<input value="'+gReCfg.id+'" name="id" type="hidden" />\
						<input value="'+gReCfg.product+'" name="pct" type="hidden" />\
						<textarea tabindex="'+( ++tabindex )+'" rows="8" cols="50"  onfocus="gReF.contentFocus(this);" onkeyDown="ctlent(this,event);" spanid="auth_img_span_id_bottom" onclick="gReF.face(\'\',this,this);" ' + (!this.gIsLogin() ? 'spanIdv="auth_img_span_id" onmousedown="gReF.auth(this);"' : '') + ' id="content" name="content">'+this.cacheTxt+'</textarea>\
						<div class="lw_post_extra">\
						<div class="lw_face">' +  gFace.get('','auth_img_span_id') + '</div>\
						<div class="lw_post_opt" id="lw_post_opt">\
							<ul class="lw_btn">\
								' + ((!this.gIsLogin()) ? '<li ><a href="javascript:' + gReCfg.loginFunction + ';">登录</a><span>|</span><a href="javascript:show_reg();">注册</a></li>' : '')+'\
								' + ((!this.gIsLogin() || gReCfg.auth_img === true) ? '<li id="auth_img_p_'+(this.openId ? '_'+this.openId : '')+'" style="display:none;"><input name="auth_img" tabindex="'+( ++tabindex )+'" id="auth_img" maxlength="4" type="text" autocomplete="false" spanIdv="auth_img_span_id" onmousedown="gReF.auth(this);" style="ime-mode:disabled" size="4" maxlength="6" value="输入验证码" onfocus="clearAuthValue()"/><span id="auth_img_span_id"></span></li>' : '')+'\
						   <li><input class="btn_submit" value="提交评论" name="postSubmit" type="submit" ></li>\
							</ul>\
					  </div>\
			 </form>\
			 </div>';
			
			if(type == 3){		
				rs = rs.replace(/name="(\w+)"/,'name="$1_top_comment"');//匹配第一个
				rs = rs.replace(/id="(\w+)"/,'id="$1_top_comment"');
				rs = rs.replace(/gReF\.loginForm\(\)/,'gReF.loginForm(\'top\')');
				rs = rs.replace(/id="auth_img_p_"/,'id="auth_img_p_top_comment"');
				rs = rs.replace(/spanIdv="auth_img_span_id"/g,'spanIdv="auth_img_span_id_top"');//验证码图片
				rs = rs.replace(/id="auth_img_span_id"/g,'id="auth_img_span_id_top"');//验证码图片
			}
		  
		  
			
			return rs;		
	},
	"quickFormTpl":function(){
		var tabindex = 100;
		var loadAuthImg = (!this.gIsLogin() || gReCfg.auth_img === true) ? true : false;
		var tpl = '\
		<div class="lw_post"  id="qCommentsForm">\
			<form accept-charset="utf-8" action="'+this.getHost()+gReCfg.insert+'" name="quickFrom" id="quickFrom" onsubmit="document.charset=\'utf-8\';return gReF.submit(this);" method="post" target="'+gReCfg.target+'">\
					<!-- lw_post -->\
						<input type="hidden" id="qt_'+this.openId+'" name="quote_content">\
						<input type="hidden" id="qu_'+this.openId+'" name="quote_userid">\
						<input type="hidden" name="callback" value="parent.gReF.insertCallback">\
						<input value="'+gReCfg.user_id+'" name="v_userid" type="hidden" />\
						<input value="insert" name="a" type="hidden" />\
						<input value="'+gReCfg.id+'" name="id" type="hidden" />\
						<input value="'+gReCfg.product+'" name="pct" type="hidden" />\
						<div class="lw_post_hd"><h4>我要回应</h4><a href="javascript:;" onclick="hiddenQuickDiv()"  class="close">x</a></div>\
						<textarea tabindex="'+( ++tabindex )+'" rows="8" cols="50"  onfocus="gReF.contentFocus(this);" onkeyDown="ctlent(this,event);" spanid="auth_img_span_id_bottom" onclick="gReF.face(\'\',this,this);" ' + (!this.gIsLogin() ? 'spanIdv="auth_img_span_id" onmousedown="gReF.auth(this);"' : '') + ' id="content" name="content">'+this.cacheTxt+'</textarea>\
						<div class="lw_post_extra">\
						<div class="lw_face">' +  gFace_comment.get('','auth_img_span_id') + '</div>\
						<div class="lw_post_opt" id="lw_post_opt">\
							<ul class="lw_btn">\
								' + ((!this.gIsLogin()) ? '<li ><span id="suggest2"></span><a href="javascript:'+ gReCfg.loginFunction +';">登录</a><span>|</span><a href="javascript:show_reg();">注册</a></li>' : '')+'\
								' + ((!this.gIsLogin() || gReCfg.auth_img === true) ? '<li id="auth_img_p_'+(this.openId ? '_'+this.openId : '')+'" style="display:none;"><input name="auth_img" tabindex="'+( ++tabindex )+'" id="auth_img" maxlength="4" type="text" autocomplete="false" spanIdv="auth_img_span_id" onmousedown="gReF.auth(this);" style="ime-mode:disabled" size="4" maxlength="6" value="输入验证码" onfocus="clearAuthValue()"/><span id="auth_img_span_id"></span></li>' : '')+'\
						   <li><input class="btn_submit" value="提交评论" name="postSubmit" type="submit" ></li>\
							</ul>\
					  </div>\
			</form>\
			</div>';
		return tpl;	
	},
	"loadQuickForm":function(){
		if(gReCfg.quickPost === true && _.e(gReCfg.quickPostId)){
			//_.e(gReCfg.quickPostId).innerHTML = this.quickFormTpl();
			//this.isTop = true;
			//this.openId=0;
			
			_.e(gReCfg.quickPostId).innerHTML = this.formTpl(3);
			_.e(gReCfg.quickPostId).style.display = "";
		}
	},
	loadTopCommentForm:function(){
		this.closeRev();
		//顶部评论框修改 add by pengkl 2011-01-17
		if(gReCfg.quickPost === true && _.e(gReCfg.quickPostId)){
			this.isTop = true;
			this.openId=0;
			_.e(gReCfg.quickPostId).innerHTML = this.formTpl(3);
			_.e(gReCfg.quickPostId).style.display = "";
			setTimeout(function(){try{document[gReCfg.from+"_top_comment"].content.focus();}catch(e){}},1000);//pengkl@110407			
		}
	},
	topRev:function(){
		//加载顶部原来快速回复 add by pengkl 2011-01-17
		if(gReCfg.quickPost === true && _.e(gReCfg.quickPostId)){
			this.loadQuickForm();
			this.openId=0;
			this.isTop = false;
		}
	},
	openTopForm:function(){
		//顶部评论处理 2011-01-18
			if(this.isTop){
				if(gReCfg.quickPost === true && _.e(gReCfg.quickPostId)){
					this.loadTopCommentForm();
					return;
				}
			}
	},
	"openForm":function(getFormStr){
			getFormStr = getFormStr||null;
			
			this.closeRev();
			
			//用户登陆成功影藏登录按钮
			if(this.gIsLogin() && document.getElementById('lw_post_opt')){
				 document.getElementById('lw_post_opt').innerHTML = '<ul class="lw_btn"><li><input class="btn_submit" type="submit" name="postSubmit" value="提交评论"></li></ul>';
			}
			
			var rs = this.formTpl(3);//form模板
			/*
			if(!_.e(gReCfg.Repost)){
				rs = '<div class="reViewForm" id="LwordPost">'+rs+'</div>';
			}*/
			if(getFormStr){
				return rs;
			}
			this._setRepost(rs);
		
			this.openId=0;
			//this.topRev();
			_.e(gReCfg.lw_page).style.display = "block";
			return;
		},
	"loginTpl":function(){
		
			var rs = '\
				<div class="lw_login">\
					<p class="tips"><span class="warning" id="warning" style="color: red; display:none;">输入的用户名或密码不对哦！</span></p>\
					<form accept-charset="utf-8" method="post" name="'+gReCfg.from+'" action="http://urs.56.com/php/urs.php" target="'+gReCfg.target+'" onSubmit="document.charset=\'utf-8\';return gReF.submit(this)">\
						<h3>请输入用户名和密码：</h3>\
						<input value="1" name="verifycookie" type="hidden">\
						<input value="'+this.getHost()+gReCfg.insert+'?a=login" name="ourl" type="hidden">\
						<input value="http://www.56.com/js/login/login_box_error.html" name="errurl" type="hidden">\
						<p><label>用户名:</label><input tabindex="9" class="txtinput" name="username" type="text"></p>\
						<p><label>密码:</label><input tabindex="10" class="txtinput" name="password" type="password"></p>\
						<p><input tabindex="11" name="postSubmit" value="登&nbsp;录" class="btn_login" type="submit"><a target="_blank" href="http://urs.56.com/Reg1.php?vReview">注册</a><a href="javascript:gReF.pg_del();">返回</a></p>\
					</from>\
				</div>';
			
		    _.e(gReCfg.lw_page).style.display = "none";
		    this.openId=0;
			return rs;
		},
	"loginForm":function(){
		var a = this.openId;
		//this.closeRev();
		if(this.gIsLogin()){
			this.openForm();
		}else{
			//var rs = this.loginTpl();
			
			//this._setRepost(rs,"qCommentsForm");
                         show_login();
			/*
			if(_.e('REV_'+a)){
			
				_.e('REV_'+a).innerHTML = rs;
			}else{
					this._setRepost(rs,"qCommentsForm");
			}*/
		}
		return;
	},
	stat:function(note){
		note = note || 'comment';
		setStat(note,3000);
	},
	chklengh:function(str)
	{
		return str.replace(/[^\x00-\xff]/g,"00").length;
	},
	quickSubmit:function(a){
		if(this.submit(a) === true){
			a.submit();
		}
	},
	submit:function(a){
		try{
			document.charset='utf-8';
			document.domain="56.com";
		}catch(e){};
			
		if(a.name == gReCfg.from+'_quick'){
			this.usingQuickPost = true;
			if(a.content.value == gReCfg.quickPostDftCont){
				a.content.value = "";
				a.content.className = "inp_txt";
				//a.content.focus();
				return false;
			}
			this.stat("comment_quick");
		}else{
			this.usingQuickPost = false;
			this.stat('c_bot_submit');
		}
		
		if(a.content&&gReF.gFace(a.content.value)){
			return false;
		}
	
		if(a.content){
			this.cacheTxt=a.content.value;
		}
	
		var match = [];
		if(a.content && a.content.value){
			match = a.content.value.match(/(\[`x_\w+`\])/g);//匹配表情
		}
	  	
		if(a.content&&(a.content.value==''||this.chklengh(a.content.value)<2)){
			this.stat("RE_E_010","#iframe_stat_call");
			this.alert("提示:您忘了填写评论内容哦，至少1个表情或2个字符！");
			a.content.focus();
			return false;
		}else if(match && match.length > 5){
			this.alert("提示:您添加表情太多了哦，最多为5个！");
			a.content.focus();
			return false;
		}else if(!this.gIsLogin() && gReCfg.auth_img === true && a.auth_img && a.auth_img.value == ''){
			this.stat("RE_E_011","#iframe_stat_call");
			this.alert("提示:您忘了输入验证码哦!");
			a.auth_img.focus();
			return false;
		}else if(!this.gIsLogin() && gReCfg.auth_img === true && a.auth_img && a.auth_img.value.length != 4) {
			this.stat("RE_E_011", "#iframe_stat_call");
			this.alert("您输入的验证码不足4位哦！");
			a.auth_img.focus();
			return false;
		}else if(a.username && a.username.value==''){
			this.alert("提示:请输入用户名!");
			a.username.focus();
			return false;
		}else if(a.password && a.password.value==''){
			this.alert("提示:请输入密码!");
			a.password.focus();
			return false;
		}else{
			//add by jingki 20080304
			if(gReCfg["forceInsertOnFastReply"] && a.quote_content){
				try{
					a.a.value = "insert";
					a.id.value = gReCfg.id;
					a.content.value += a.quote_content.value
					a.quote_content.value = "";
				}catch(e){};
			
			}
			if(window.submitFormName==undefined){
				window.submitFormName = new String();
				window.submitFormName = a.name;
			}else{
				window.submitFormName = a.name;
			}
			//end
			
			a.postSubmit.value='提交中..';
			a.postSubmit.disabled=true;
			setTimeout(function(){try{a.postSubmit.disabled=false;a.postSubmit.value='提交';}catch(e){}},500);
			
			try{
			if(typeof weibo != "undefined"){
				weibo.comment_text = a.content.value;
			}
			}catch(e){}
			return true;
		}
	},
	"Ok":function(pg,reload){
		pg = pg || 0;
		this.cacheTxt='';
		this.isPost = true;
		this.rand();
		this.pg_del(pg);
		this.extentAction();//外调接口
	},
	"insertCallback":function(o){//插入成功的返回，返回最新的那条数据，Melon``100413
		if(o.code == 1){
			if(typeof oJson != 'undefined' && oJson.Lword){
				this.cacheTxt = '';
				this.isPost = true;
				
				toggleAds.startAds();//重新开启广告
				
				if(document.getElementById('auth_img_p_top_comment')){
					document.getElementById('auth_img_p_top_comment').style.display= "none"; 
		    }
						
				this.openForm();
				this.openId = 0;
				this.isTyping = false;
				oJson.Lword.data.count++;
				oJson.Lword.data.data.unshift(o.data);
				if(oJson.Lword.data.touch) {oJson.Lword.data.touch++;}
				//synShowTip.addComment();
				this.dataInit(gReCfg.ContentMain,oJson.Lword.data);
				
				$('.lw_face_box').hide();
				// 可能cookie 没那么快读取到记录
				gReCfg.auth_img = false ;
				
				//this.extentAction();//外调接口
				try{
				if(typeof weibo != "undefined"){  //同步到微薄
					weibo.sync('comment',oFlv.o.EnId);
				}
				}catch(e){}
			}
		}
	},
	"extentAction" : function(){
		//renren接口
		try{
			var action = function(id){
				var id = id,type = 4;
				if(_.getCookie("renren_setting")){
					eval("var rs = "+_.getCookie("renren_setting")+";");		
					if(rs && rs.fav == 1 && usr.user_id()){
						var iframe = document.createElement("iframe");
						var src = 'http://app.56.com/cooperate/index.php?action=RenRen&do=Feed&flvid='+id+'id&type='+type;
						iframe.setAttribute("width",0);
						iframe.setAttribute("height",0);
						iframe.setAttribute("name","action");
						iframe.setAttribute("src",src);
						document.getElementsByTagName('body')[0].appendChild(iframe);
					}	
				}
			};
			action(gReCfg.id);//renren接口 end
		}catch(e){
			if(usr.user_id() == 'melon_huang'){
				alert('renren评论接口异常');
			}
		}
	},
	"rev":function(a,tim,user_id,user_name){
		if(!this.gIsLogin() && !gReCfg["allowGuest"]){
			this.loginForm();
			_.gowin('#play_action_extra');
			return;
		}else if(! gReCfg["forceInsertOnFastReply"]){
			_.gowin('#play_action_extra');
		}
		a=a||this.openId;
		if(this.openId==0 && gReCfg['closeTopFormOnShowFastReplyForm']){
			_.e(gReCfg.Repost).innerHTML='';
			_.e(gReCfg.Repost).style.display='none';
		}else if(a!=this.openId){
			this.closeRev();
		};
		
		this.openId=a;
		if(!_.e('REV_'+a)){
		
			var _div = document.createElement("div");
			_div.className = "reply";
			
			_div.setAttribute("id","REV_"+a);
			
			try{
		     _.e(gReCfg.LC_id_fd+a).appendChild(_div);
		  }catch(e){
		     	
		  }
		  
			_div.innerHTML = this.quickFormTpl();
			//add by jingki 20080304
			if(gReCfg["forceInsertOnFastReply"] && arguments.length==4){
				var o=document[gReCfg.from];
				var quote_value = _.e(gReCfg.LC_id+a).innerHTML ;
				if(usr.user_id() == 'melon_huang'){
					//alert(quote_value);
				}
				
				//quote_value = quote_value.replace(/<div[^>]+quote_div_(\d+)(?:[^>]*?)>((.|\n)*?)<\/div>/igm,"[quote$1]$2[/quote$1]");
				quote_value = quote_value.replace(/<div[^>]+quote_div_(\d+)(?:[^>]*?)>((.|\n)*?)<\/div>/igm,"");
				
				quote_value = ubb.en(quote_value);
				
				if(usr.user_id() == 'melon_huang'){
					//alert(quote_value);
				}
				
				//quote_value="[quote][p]回应[b][user="+user_id+"]"+user_name+"[/user]在 "+_.time(tim)+" 发表的留言：[/b][/p]"+quote_value+"[/quote]";
				quote_value="[quote"+a+''+tim+"][p][user="+user_id+"]"+user_name+"[/user]：[/p][p]"+quote_value+"[/p][/quote"+a+''+tim+"]";
				
				if(usr.user_id() == 'melon_huang'){
					//alert(quote_value);
				}
				
				_.e('qt_'+a).value = quote_value;
				_.e('qu_'+a).value = user_id;
			}
			//end
			this.stat('c_mid_replay');//add by pengkl 2010-11-23
		}else{
			
			this.closeRev();
			this.openId=0;
			if(gReCfg['closeTopFormOnShowFastReplyForm'] && gReCfg['open'])this.openForm();
		}
	},
	"closeRev":function(){
		if(this.openId){
			_.e('REV_'+this.openId).parentNode.removeChild(_.e('REV_'+this.openId));
			this.openId=0;
		}
	},
	"reviewFormOk":function(){
		var a=this.openId;
		var e=gReCfg.LC_id+a;
		this.cacheTxt='';
		this.rand();
		this.closeRev();
		var url=this.getHost()+gReCfg.load+'?a=review&id='+a+'&pct='+gReCfg.product+'&gRand='+this.getRand();
		fJson.set(e,{
		"data":"n","str":"gReF.RevInit('"+e+"',oJson."+e+".data)"});
		fJson.charset = 'utf-8';
		fJson.main(url,e);
		this.openForm()
	},
	
	"RevInit":function(e,o){
		_.e(e).innerHTML=ubb.de(o.content);
	},
	"del":function(a){
		var yn=window.confirm("确定要删除该评论？");
		if(yn)
		{
			var b = this.getHost()+gReCfg.insert;
			window[gReCfg.target].location=b+(b.indexOf('?')==-1?'?':'&')+'a=del&id='+a;
		};
	},
	"ban":function(id,user){
		var yn=window.confirm("禁止了以后，他将无法在您的所有视频和地盘里留言！您确认要禁止此人对您评论吗？");
		if(yn){
		var b = this.getHost()+gReCfg.insert;
		window[gReCfg.target].location=b+(b.indexOf('?')==-1?'?':'&')+'a=ban&id='+id+'&buser='+user};
	},
	"quote":function(id,tim,user_id,user_name){
		this.openForm();
		this.face();
		var a=document[gReCfg.from];
		var quote_value=ubb.en(_.e(gReCfg.LC_id+id).innerHTML);
		var print_value="[quote][p][user="+user_id+"]"+user_name+"[/user]：[/p][p]"+quote_value+"[/p][/quote]\n";
		a.content.value+=print_value;
		a.content.focus();
		this.auth();
	},
	"getKeyCode" : function(evt){
		evt=window.event||evt;
		var ret=evt.keyCode?evt.keyCode:evt.which;
		return ret;
	},
	"pg":function(pg){
		if(pg) {
		
			this.setPg(pg);
		 	this.openForm();
      //document.getElementById('content').focus();
      document.getElementById('content').blur();
			this.stat('c_apage');
		}else{
			if(gReCfg["allowGuest"] && gReCfg["showBottomForm"]){
				if(this.leaveWordRawTpl){	
					_.e(gReCfg["Content"]).innerHTML = this.leaveWordRawTpl;
				}else{
					this.leaveWordRawTpl = _.e(gReCfg["Content"]).innerHTML;
				}
				_.e(gReCfg["Content"]).innerHTML =  this.openForm(true) + "</div><div id='"+gReCfg.ContentMain +  "' style='display:none;'>" +  _.e(gReCfg["Content"]).innerHTML ;
				_.e(gReCfg["Content"]).style.display = "";
			}
		}
		
		if(!gReCfg.show){
			//this.openForm();
			return;
		}
		/*if(gReCfg['open']&&(_.e(gReCfg.Repost).style.display=='none'||_.e(gReCfg.Repost).innerHTML=='')) {
			this.openForm();
		}*/
		//_.loading(gReCfg.Content);
		
		var url=this.getHost()+gReCfg.load+'?a='+gReCfg.a+'&pct='+gReCfg.product+'&id='+gReCfg.id+'&user_id='+gReCfg.user_id+'&page='+this.getPg()+'&pageRows='+gReCfg.pageRows+'&gRand='+this.getRand()+gReCfg.otherVars;
		fJson.set(gReCfg.Content,{"data":"n","str":"gReF.dataInit('"+gReCfg.ContentMain+"',oJson."+gReCfg.Content+".data)"});
		fJson.charset = 'utf-8';
		fJson.main(url,gReCfg.Content);
		this.openId=0;
		
	},
	"pg_del":function(pg){
		this.openId=0;
		if(pg) {
		
			this.setPg(pg);
		 	this.openForm();
			this.stat('c_apage');
		}else{
			if(_.e(gReCfg.lw_page)){
			 _.e(gReCfg.lw_page).style.display = "block";
			}
			if(gReCfg["allowGuest"] && gReCfg["showBottomForm"]){
				if(this.leaveWordRawTpl){	
					_.e(gReCfg["Content"]).innerHTML = this.leaveWordRawTpl;
				}else{
					this.leaveWordRawTpl = _.e(gReCfg["Content"]).innerHTML;
				}
				if (navigator.userAgent.indexOf('Firefox') < 0){
					var oDiv= document.getElementById("lw_page");
			      		if(oDiv){
            					_.e(gReCfg["Content"]).removeChild(oDiv);
            				}
			      _.e(gReCfg["Content"]).innerHTML =  this.openForm(true) + "</div><div id='"+gReCfg.ContentMain +  "' style='display:none;'>" +  _.e(gReCfg["Content"]).innerHTML + '<ul class="comment_list"><!--%begin_0 key="data"%--><li class="comment_item"><div class="cmt_hd"><p class ="meta"><a class="author" href="/%=userSpace%*/" target="_blank">[%=userName%]</a> <cite class="time">[%=commentTime%]</cite> [%=dataNote%]说: </p><div class="actions">[%=commentOption1%]</div></div><div class="cmt_bd"><p class="content" id="LC_/*%=id%*/">[%=content%]</p></div><div class="cmt_fd" id="LC_fd_/*%=id%*/"><div class="actions"><span class="opt">[%=commentOption%]</span></div></div></li><!--%end_0%--></ul>';
			    	_.e(gReCfg["Content"]).style.display = "block";
			    	document.getElementById("commentContent").style.display = "none";
                                 var nDiv=document.createElement("div");
            		 	 nDiv.id = "lw_page";
            			 nDiv.className = "lw_page";
			    	_.e(gReCfg["Content"]).appendChild(nDiv);
				}else{
				  	_.e(gReCfg["Content"]).innerHTML =  this.openForm(true) + "</div><div id='"+gReCfg.ContentMain +  "' style='display:none;'>" +  _.e(gReCfg["Content"]).innerHTML;
				  	_.e(gReCfg["Content"]).style.display = "";
			  }
			}
		}
		
		
		if(!gReCfg.show){
			//this.openForm();
			return;
		}
		
		/*if(gReCfg['open']&&(_.e(gReCfg.Repost).style.display=='none'||_.e(gReCfg.Repost).innerHTML=='')) {
			this.openForm();
		}*/
		//_.loading(gReCfg.Content);
		var url=this.getHost()+gReCfg.load+'?a='+gReCfg.a+'&pct='+gReCfg.product+'&id='+gReCfg.id+'&user_id='+gReCfg.user_id+'&page='+this.getPg()+'&pageRows='+gReCfg.pageRows+'&gRand='+this.getRand()+gReCfg.otherVars;
		fJson.set(gReCfg.Content,{"data":"n","str":"gReF.dataInit('"+gReCfg.ContentMain+"',oJson."+gReCfg.Content+".data)"});
		fJson.charset = 'utf-8';
		fJson.main(url,gReCfg.Content);
	
		
		this.reload();
	},
	
	
	reloadReset:function(){
		this.loadTimes = 0;
	},
	
	reload:function(t){
		//如果评论3秒后没有出来，则多加载一次。
		var t=t||5000;
		
		setTimeout(function(){
			if(gReF.loadTimes === 0){
				gReF.loadTimes++;
				gReF.pg();
			}else if(gReF.loadTimes === 1){
				gReF.loadTimes++;
				gReF.dataInit(gReCfg.ContentMain,{"count":"0","data":[],"thisPage":1,"pageCount":0,'error':true});
			}
		},t);
	},
	time:function(a,b)
    {
    	var s,d,D,y,now,delta;
    	D = new Date();
    	now = D.getTime();
    	d = new Date(a*1000);
    	delta = Math.floor(now / 1000 - a);
    	if(delta < 86400) {
    		if(delta < 60) {
    			 s = 1 + '分钟前';
    		} else if(delta >= 60 && delta < 3600) {
    			s = Math.floor(delta / 60) + '分钟前';
    		} else if(delta >= 3600 && delta < 86400) {
    			s = Math.floor(delta / 3600) + '小时前';
    			//s += Math.floor((delta % 3600)/60) + '分钟前';
    		}
    	}else if(delta >= 86400 && delta < 86400 * 2){
    		s = '昨天';
    	}else if(delta >= 86400 * 2 && delta < 86400 * 3){
    		s = '前天';
    	}else if(delta >= 86400 * 3  && delta < 86400 * 7){
    		s = Math.floor(delta / (86400)) + '天前';
    	}else{	
			y = d.getFullYear();
			s = y + 
			"-"+ (d.getMonth() + 1) + 
			"-"+ d.getDate();
    	}
    	return s;
    },
	"dataInit":function(e,o){
	  
		try{
			document.charset='utf-8';
			document.domain="56.com";
		}catch(e){};
		//add by jingki 20081112 视频所有者可以看到视频的所有评论
		var owner_id = typeof(user_id)!="undefined"?user_id:"";
		var commentIds = [];
		if(!owner_id && typeof(oFlv)=="object" && oFlv.o && oFlv.o.user_id) {
			owner_id = oFlv.o.user_id;
		}
		var is_owner = (owner_id==usr.gLoginUser() || usr.gLoginUser()=="jingki");
		
		document.getElementById('LwordContent').style.display = "block";
		var delarray=[];
		
		//判断铁杆粉丝（美女主播） Add By Tingo 2008-11-21
		var is_best_fan = false;
		if(usr.gLoginUser() == 'tingoooo') is_best_fan = true;
		if(typeof(o.best_fans) != 'undefined') {
			var bestFans = o.best_fans;
			for(i = 0;i < bestFans.length;i++) {
				if(usr.gLoginUser() && bestFans[i] && usr.gLoginUser() == bestFans[i]) {
					is_best_fan = true;
				}
			}
		}
		
		
		 
		if(this.loadTimes === 0){
			this.sortDing(o.data);//顶排序
		}
		
		if(o.error === true){
			_.e(gReCfg.commentsCorn).innerHTML = '<div class="comment_output"><p class="output_box"><img src="http://s3.56img.com/style/include/comment/v1/img/output_error.png" alt="抱歉，服务器在闹脾气！暂时看不了评论" /></p></div>';
    }else if(o.data == "" || o.data.length == 0){
		 	 _.e(gReCfg.commentsCorn).innerHTML = '<div class="comment_output"><p class="output_box"><a href="javascript:;" onclick="gReF.focus();setStat(\'clifoc\');"><img src="http://s3.56img.com/style/include/comment/v1/img/output_sofa.png" alt="咦，这里还没有人评论！赶紧说两句，抢个沙发坐吧" /></a></p></div>';
		  //_.e(gReCfg.commentsCorn).innerHTML  = '<div class="comment_output"><p class="output_box"><img src="http://s3.56img.com/style/include/comment/v1/img/output_error.png" alt="抱歉，服务器在闹脾气！暂时看不了评论" /></p></div>';
		}else if(o.data.length >= 1){//服务器忙
				_.e(gReCfg.commentsCorn).innerHTML = '<div class="comment_output"><p class="output_box"><img src="http://s3.56img.com/style/include/comment/v1/img/output_loading.png" alt="麻烦等一下！正在加载大家的评论" /></p></div>';
				this.loadQuickForm();//快捷评论
		}
	 
		for(var i=0;i<o.data.length;i++){
			var a=o.data[i],b=usr.gLoginUser(),tmp;
			
			if(typeof(a) != 'object'){
				continue;
			}
			
			if(a['approved']=='N'/* && (a.comment_userid != b || a.comment_userid == '56com')*/){
				//if(!is_owner) {
					delarray.push(i);
					o.count--;
					continue;
				//}
			}
			if(a['hadBadw'] == 'true' && a.comment_userid != b) {
				delarray.push(i);
				o.count--;
				continue;
			}
			
			if(gReCfg.user_id==b) gReCfg.userFace=1;
			if(gReCfg.a=='me'){
				tmp=a.comment_userid;
				a.comment_userid=a.v_userid;
				a.v_userid=tmp;
			}
			if(!a.content) a.content = '[`x_good`][`x_good`][`x_good`]';
			if(a.content == null) a.content = '[`x_good`][`x_good`][`x_good`]';
			//a.content=unescape(a.content);
			if(a.de === undefined){
				a.content=ubb.de(a.content);
				a.content=ubb.heart(a.content);
				a.de = 1;
			}
			if((gReCfg.a=='user' || gReCfg.a=='me') && a.vid != 0 && a.subject){
				var flvUrl = '';
				if(a.pct == '14'){
					var flvUrl = this.flvUrl(a.EnId,a.pct,a.vid,a.did);
				}else{	
					var flvUrl = this.flvUrl(a.EnId,a.pct,a.vid);
				}
				if(flvUrl){
					a.dataNote='在<a href="'+flvUrl+'" target="_blank"><span class="comSub">'+a.subject+'</span></a>';
				}
			}
			if(gReCfg.userFace)a.UserImg='<div class="UserImg"><a href="http://'+a.comment_userid+'.56.com/" target="_blank"><img src="'+usr.photo(a.comment_userid)+'"></a></div>';
			a.commentTime=this.time(a.dateTime);
			a.safeIp=a.ip.slice(0,a.ip.lastIndexOf('.')+1)+"*";
			a.userSpace='http://'+a.comment_userid+'.v.56.com';
			//a.userName=a.name_id == '56com'?'游客':a.name_id;//_.substr(a.name.replace(/<(.*)>.*<\/\1>/g,''));
			a.name = unescape(a.name);
			a.userName =_.substr(a.name.replace(/<(.*)>.*<\/\1>| /g,''));
			a.userName2=a.comment_userid == '56com'?gReCfg.guestName:a.userName;
			a.userName =a.comment_userid == '56com'?'<a href="'+gReCfg.guestLink+'"  target="_blank">'+gReCfg.guestName+'</a>':'<a href="http://'+a.comment_userid+'.56.com"  target="_blank">'+a.userName+'</a>';
			a.sendMsg='_c.sendSms(\''+a.comment_userid+'\')';
			
			/*if(usr.user_id()=="melon_huang"){
				alert(a.name);
			}*/
			
			var ipStr = a.comment_userid != b ? '<span class="ope">[' + a.safeIp + ']</span>' : '';
			var giftStr = a.comment_userid != b ? '<span class="ope4"><a href="http://app.56.com/gift/index.php?action=gift&do=send&to=' + a.comment_userid + '" target="_blank">送礼</a>' : '';
			giftStr = '';
			//a.commentOption=(a.name_id == b || a.date_user_id == b || _.get('r') == 'del' ? '<span class="ope">[' + a.safeIp + ']</span> | <span class="ope1"><a href="javascript:gReF.rev(' + a.id + ');">回复</a></span> | <span class="ope3"><a href="javascript:gReF.del(' + a.id + ');">删除</a></span> | <span class="ope4"><a href="http://app.56.com/gift/index.php?action=gift&do=send&to=' + a.name_id + '" target="_blank">送礼</a></span> '+(a.date_user_id==b?'| <span class="ope5"><a href="javascript:gReF.ban('+a.id+',\''+a.name_id+'\');">禁</a></span>':''):'<span class="ope1">'+(gReCfg["forceInsertOnFastReply"]?'<a href="javascript:gReF.rev('+a.id+','+a.dateTime+',\''+a.name_id+'\',\''+a.userName2+'\');">回复</a>':'<a href="#CommentPlace" onclick="gReF.quote('+a.id+','+a.dateTime+',\''+a.name_id+'\',\''+a.userName2+'\');">回复</a>') + (is_best_fan ? ' | <span class="ope3"><a href="javascript:gReF.del(' + a.id + ');">删除</a></span>' : '') + '</span>');
			
			a.commentOption = "";
			a.commentOption1 = "";
		
			if(a.ext){
				a.commentOption += '<span class="opt" ><a href="javascript:;" class="up" onmousedown="gReF.ding(' + a.id + ')" id="ding_btn_' + a.id + '">[顶<em class="red" id="ding_' + a.id + '" >'+(a.ext.ding > 0 ? a.ext.ding : 0)+'</em>]</a></span>';
			}
			
			if(a.type == "hot"){
				//a.commentOption += "<span class=\"ope0\"><em class=\"hot\" title=\"热门评论\">热</em></span>";
				var hotHtml = '<strong class="hot">[热]</strong>';
				a.content = hotHtml + a.content.replace(hotHtml,"");	
			}
			if(_.get('r') == 'del' || ((a.comment_userid == b || a.v_userid == b) && (b != '56com' && b != "" && a.comment_userid != ""))) { //版主
				
				a.commentOption +=  ' <span class="opt"><a href="javascript:gReF.rev('+a.id+','+a.dateTime+',\''+a.comment_userid+'\',\''+a.userName2+'\');">['+gReCfg.reName+']</a></span>';
				a.commentOption1 =  '<a href="javascript:gReF.del(' + a.id + ');" class="opt">删除此评论</a> ';
				/*if(a.v_userid == b && a.comment_userid != b) {	//如果数据里面的被留言用户Id等于b(登录用户Id)，那么就允许用户禁止
					a.commentOption += '<span class="ope5"><a href="javascript:gReF.ban('+a.id+',\''+a.comment_userid+'\');">禁</a></span>';
				}*/
			} else {
				a.commentOption += '<span class="opt">';
				if(gReCfg["forceInsertOnFastReply"]) {
					a.commentOption += '<a href="javascript:gReF.rev('+a.id+','+a.dateTime+',\''+a.comment_userid+'\',\''+a.userName2+'\');">['+gReCfg.reName+']</a>';
				} else {
					a.commentOption += '<a href="#play_action_extra" onclick="gReF.quote('+a.id+','+a.dateTime+',\''+a.comment_userid+'\',\''+a.userName2+'\');">['+gReCfg.reName+']</a>';
				}
				if(is_best_fan) {
					a.commentOption1  =  '<a href="javascript:gReF.del(' + a.id + ');" class="opt">删除此评论</a> ';
				}
				a.commentOption += '</span>';
			}
	
			commentIds.push(a.id);
			
			if(a.comment_userid==gReCfg.user_id){
				a.userName='<a href="http://'+a.comment_userid+'.56.com"  target="_blank"><span class="red">地主</span></a>|'+a.userName;
			}
			
			if(gReCfg.a=='me'){
				if(a.comment_userid==gReCfg.user_id){
					a.userName='<a href="http://'+a.comment_userid+'.56.com"  target="_blank"><span class="red">自己</span></a>';
				}else{
					a.userName='<a href="http://'+a.comment_userid+'.56.com"  target="_blank">你对：'+a.comment_userid+'</a>';
				}
			}
		}
		
		for(var i=0;i<delarray.length;i++)
			delete o.data[delarray[i]];
	
	 if(_.e("insertCmt")){//评论参与数
			
			if(o.touch == undefined){
			  o.touch = o.count ;	
			}
			_.e("insertCmt").innerHTML = '<div class="hd"><p>我要说两句<span class="stat">(参与:<strong class="red">'+o.touch+'</strong>人 评论:<strong class="red">'+o.count+'</strong>条)</span></p></div><div id="lAD_200x25" class="ad_img"></div>';
		}
		
		
		oTpl.tpl(e,o);
	 
	  if(o.data.length >= 1){
			_.e(gReCfg.commentsCorn).style.display = "none";
	  }
		if(this.getPg()>1 || this.isPost) {
			if(this.usingQuickPost === false){//非快捷回复
				window.location.hash = 'play_action_extra';
			}
			this.isPost = false;
		}
	  
	  if(o.data.length >= 1){
	  	_.e(gReCfg.lw_page).innerHTML =  this.subPg(o.count,o.thisPage,o.pageCount).join('&nbsp;<wbr />') + '<a href="javascript:;" onclick="gReF.focus();setStat(\'clifoc\');" class="btn_comments">我要评论</a>';
		  _.e(gReCfg.lw_page).style.display = "block";
		}	
		
		//IE6 下的评论显示删除功能
		if ($(".comment_item").html() && $.browser.msie&&($.browser.version == "6.0")&&!$.support.style){
			$('.comment_item').hover(function(){
						$(this).addClass('sfhover');
			}, function() {
				$(this).removeClass('sfhover')
			});
		}
		
		gReF.loadTimes = true;//加载成功
	},
	"setSupport" : function(data) {
		if(data) {
			for(key in data) {
				if(_.e('ding_' + key)) _.e('ding_' + key).innerHTML = data[key].ding;
				if(_.e('dao_' + key)) _.e('dao_' + key).innerHTML = data[key].dao;
				if(data[key].supported) {
					if(data[key].supported == 'ding') {
						_.e('ding_btn_' + key).className = 'up up_a';
					} else if(data[key].supported == 'dao') {
						_.e('dao_btn_' + key).className = 'down down_a';
					}
				}
			}
		}
	},
	"sortDing" : function(o){
		//需要排序的数组
		var list = o;//Array(23, 45, 18, 37, 92, 13, 24);
		//数组长度
		var n = list.length;
		//交换顺序的临时变量
		var tmp;//
		//交换标志
		var exchange;
		//最多做n-1趟排序
		for (var time = 0; time < n - 1; time++) {
			exchange = false;
			for (var i=n-1; i>time;i--) {
				if (list[i]["type"] == "hot" && list[i]["ext"]["ding"] > list[i - 1]["ext"]["ding"]) {
					exchange = true;
					tmp = list[i - 1];
					list[i - 1] = list[i];
					list[i] = tmp;
				}
			}
			//若本趟排序未发生交换，提前终止算法
			if (!exchange) {
				break;
			}
		}
		o = list;
		delete list;
	},
	"ding" : function(id) {
		this.stat('c_mid_ding');
		_.e('ding_btn_' + id).className = 'up up_a';
		fJson.include(this.getHost() + (gReCfg.insert.indexOf('?') >= 0 ? gReCfg.insert + '&' : gReCfg.insert + '?') + 'a=setSupport&type=ding&backFunc=gReF.setSupport&id=' + id + '&vid=' + gReCfg.id + '&pct=' + gReCfg.product + '&v_userid=' + gReCfg.user_id);
	},
	"dao" : function(id) {
		_.e('dao_btn_' + id).className = 'down down_a';
		fJson.include(this.getHost() + (gReCfg.insert.indexOf('?') >= 0 ? gReCfg.insert + '&' : gReCfg.insert + '?') + 'a=setSupport&type=dao&backFunc=gReF.setSupport&id=' + id + '&vid=' + gReCfg.id + '&pct=' + gReCfg.product + '&v_userid=' + gReCfg.user_id);
	},
	"gIsLogin":function(){
		var isLogin = '';
		if(usr.gIsLogin() && usr.gLoginUser().substr(0,5)!="guest"){
			gReCfg.auth_img = false;//登录用户默认不需要验证码
			isLogin = true;
		}else{
			gReCfg.auth_img = true;
			isLogin = false;
		}
		if(_.getCookie("auth_img_limit") > 0){
			gReCfg.auth_img = false;
		}
		return isLogin;
	},
	"subPg":function(all,ths,pgc,max){
		var rs=[],t={},i;
		all=all||0;
		ths=ths||1;
		pgc=pgc||1;
		t['t']=max||gReCfg.maxPage;
		t['m']=Math.ceil(t['t']/2);
		if(pgc>t['t']){
			t['C']=t['t'];
			t['Begin']=true;
			t['End']=true;
			if(ths<=t['m']){
				t['Start']=1;
				t['Begin']=false;
			}else if(pgc-ths<t['m']){
				t['Start']=pgc-t['t']+1;
				t['End']=false;
			}else{
				t['Start']=ths-t['m']+1;
			}
		}else{
			t['C']=pgc;
			t['Begin']=false;
			t['End']=false;
			t['Start']=1;
		}
		t['Next']=ths!=pgc&&pgc>1?true:false;
		t['Previous']=ths!=1&&pgc>1?true:false;
		
		if(pgc > 1) {
			rs.push('<div class="pn">');
			  rs.push('<span class="disabled">共'+all+'条评论</span>');
				rs.push('<span class="disabled">共'+pgc+'页</span>');
				if(ths > 1) {
					rs.push('<a href="javascript:gReF.pg(1)">首页</a>');
				}else{
					rs.push('<span class="disabled">首页</span>');
				}
				if(t['Previous']) {
					rs.push('<a href="javascript:gReF.pg('+(ths-1)+')">上页</a>');
				}else{
					rs.push('<span class="disabled">上页</span>');
				}
				for(i=t['Start'];i<(t['Start']+t['C']);	i++){
					if(i==ths){
						rs.push('<span class="current">'+ths+'</span>');
					}else{
						rs.push('<a href="javascript:gReF.pg('+i+')">'+i+'</a>');
					}
				}
				if(t['Next']) {
					rs.push('<a href="javascript:gReF.pg('+(ths+1)+')">下页</a>');
				}else{
					rs.push('<span class="disabled">下页</span>');
				}
				
				rs.push('<a href="javascript:gReF.pg('+pgc+')">尾页</a>');
						
			rs.push('</div>');
		} else if(all > 0){
				rs.push('<div class="pn">');
				rs.push('<span class="disabled">共'+all+'条评论</span>');
				rs.push('<span class="disabled">共1页</span>');
				rs.push('</div>');
		}
    
		t={};
		return rs;
	},
	"face":function(a,objForm){
		
		try{
		a=a||''; objForm=objForm||null;
		
		//this.auth();
		var f = objForm;
		var i = 0;
		if(f.tagName == 'IMG')this.stat('c_bot_face');
		while(f.tagName != "FORM" && i < 10){
			f = f.parentNode;
			i++;
		}
		if(a){
			
			gReF.contentFocus(document[f.name].content);
		 
			document[f.name].content.value+=a;
		}
		}catch(e){
		}
	},
	"auth":function(obj,evt){
	
		try{
		var obj=obj||null;
		var evt=window.event||evt;
		if(evt && evt.keyCode == 13){
			obj.form.submit();
		}else{
			var o=obj?_.e(obj.getAttribute('spanIdv')):_.e('auth_img_span_id_top');

			if(o){//this.userLoginYN==0
				if(!this.gIsLogin() && gReCfg.auth_img === true && o.innerHTML == ''){
					var comma = gReCfg.insert.indexOf("?")>-1 ? '&' : '?';
					o.innerHTML='<a href="javascript:gReF.changeAuth();"><img onclick="gReF.changeAuth()" id="authImg" alt="换一张" border="0" src="http://comment.56.com/new/review/' + gReCfg.insert + comma + 'a=getAuth" /></a> ';
					if(obj.form.name == gReCfg.from+'_quick'){
						_.e('auth_img_p_quick').style.display = '';
					}else if(obj.form.name == gReCfg.from+'_top_comment'){
						_.e('auth_img_p_top_comment').style.display = '';
					}else{
						_.e('auth_img_p_'+(this.openId ? '_'+this.openId : '')).style.display = '';
					}
				}
			}
		}
		}catch(e){
		}
	},
	"changeAuth":function(){
		if(_.e('authImg')){
			_.e('authImg').src='http://comment.56.com/new/review/insert.utf8.php?a=getAuth&sn=' + Math.random();
		}
	},
	"rand":function(){
		var a=new Date();
		var b=a.getTime();
		gReCfg.grand=b;
	},
	"getRand":function(){
		return gReCfg.grand?gReCfg.grand:_.getCookie(gReCfg.Cookie+'grand');
	},
	"getPg":function(){
		return gReCfg.pg!=1?gReCfg.pg:_.getCookie(gReCfg.Cookie+gReCfg.a+gReCfg.user_id+gReCfg.id);
	},
	"setPg":function(a){
		gReCfg.pg=a;
	},
	"getNav":function(){
		if(this.navigator==-1){
			if(navigator.appVersion.indexOf("MSIE")==-1){
				this.navigator=0;
			}else{
				this.navigator=1;
			}
		}
		return this.navigator;
	},
	"getHost":function(){
		return gReCfg.host;
	},
	"_setRepost":function(str,element_id){
		_.e(gReCfg.div).style.display='';		
		if(element_id != undefined && element_id =="qCommentsForm"){
	    _.e(gReCfg.Repost).innerHTML=str;
    }else if( _.e("content") != undefined ){
    	 _.e("content").value ="";
		}else{
		  this.pg_del();
		}
		//gReCfg.ContentMain
	},
	"alert":function(a){
		alert(a);
	},
	"set":function(vars){
			for(key in vars){
				gReCfg[key]=vars[key];
			}
	},
	"gFace":function(str){
		
		if(str.length>10)return false;
		var a=['表情','换表情','face','把表情换为'],str=str.toLowerCase().replace(/\s/ig,"");
		for(var k in gFace){
			k=k.toLowerCase();
			if(k.length==1){
				for(var i=0;i<a.length;	i++){
					if((a[i]+':'+k)==str){
						this.set({gFace:k});
						this.openForm();
						return true;
					}
				}
			}
		};
		return false;
	},
	"focus":function(){
		gReF.contentFocus(_.e('content'));
		location = '#play_action_extra';
		document.getElementById('content').focus();
   //setTimeout(function(){gReF.auth(_.e('content'));},1000);
		//setStat('clifoc');
	},
	"contentFocus":function(o){	
		if(o.form.name == gReCfg.from+'_quick'){
			if(o.value == gReCfg.quickPostDftCont){
				o.value = "";
				o.className = "inp_txt";
			}
			_.e(gReCfg.from+'_quick_fs').className = (gReCfg.auth_img === true ? "guess" : "");
			this.stat('c_top_input');
		}else{
			this.stat('c_bot_input');
		}
		this.auth(o);//pengkl@110407
		
		
		if(document.getElementById('suggest2')){
			document.getElementById('suggest2').style.display= "none";
		}
	},
	"flvUrl":function(EnId,pct,id,parentId)
	{
		if(pct == 7){//56看看 Melon`` @ 090805
			return "http://kankan.56.com/v/"+EnId+".html";
		}else if(pct == 8){
			return "http://kankan.56.com/live/" + EnId + ".html";
		}else if(pct == 10){
			return "http://photo.56.com/album/?do=Plist&did=" + id;
		}else if(pct == 14){
			if(id && parentId){
				return "http://photo.56.com/album/?do=Show&did="+parentId+"#pid="+id;
			}
		}else if(pct == 11){
			return "http://kankan.56.com/cpm/" + EnId + ".html";
		}else{
			return _.phost(id,pct)+'/v_'+EnId+'.html';
		}
		return false;
	}	
};


if(oTpl == undefined){
	var oTpl =
	{
		"html":false,
		"main":function(element,jsonUrl,html)
		{
			if(html)this.html =html;
			var obj = _.e(element);
			if (!obj) 
			{
				alert("指定的模版容器不"+element+"存在");
				return;
			}
			var oJsonSet =
			{
				"data":"n",
				"str":"oTpl.tpl('"+element+"',oJson."+element+".data)"
			};
			fJson.set(element,oJsonSet);
			fJson.main(jsonUrl,element);		
		},
		"tpl":function(element,jsonUrl,html)
		{
			
			if(html) this.html =html;
			var obj = _.e(element);
			var templetHTML;
			if (!obj) 
			{
				alert("指定的模版容器不"+element+"存在");
				return;
			}
			if(!obj.oldHTML)
			{
				obj.oldHTML = this.html || obj.innerHTML;
				obj.oldHTML = obj.oldHTML.replace(/\[%/g,"<%").replace(/%\]/g,"%>")
									 .replace(/\{%/g,"<%").replace(/%\}/g,"%>")
									 .replace(/<!--%/g,"<%").replace(/%-->/g,"%>")
									 .replace(/\/\*%/g,"<%").replace(/%\*\//g,"%>");
			}
			obj.innerHTML 		= this.doTpl(obj.oldHTML,jsonUrl,0).replace(/http:\/\/56com\.56\.com/g,gReCfg.guestLink);
			obj.style.display 	= "";
			//$(obj).find('#'+element+'_loading').hide('slow');
		},
		"doTpl":function(tpl,data,xLev)
		{
			var sRepeat			= "<%begin_"+xLev+"[^>]*%>((.|\\n)+?)<%end_"+xLev+"%>";
			var rKey_g			= new RegExp("<%begin_"+xLev+"\\s*key=\"([^\"]+)\"[^%]*%>","g");
			var rRepeat			= new RegExp(sRepeat);
			var rRepeat_g		= new RegExp(sRepeat,"g");
			var rVars			= new RegExp("<%=(.+?)%>","g");
			var tDate;
			// 是否有重复
			var aKey = tpl.match(rKey_g);
			if(aKey)
			{
				var aRepeat = tpl.match(rRepeat_g);
				for (var key=0;key<aKey.length;key++)
				{
					aKey[key] 		= aKey[key].replace(rKey_g,"$1");
					aRepeat[key] 	= aRepeat[key].replace(rRepeat,"$1");
					tDate			= data[aKey[key]];
					var html		= '';
					for(var key2 in tDate)
					{
						if(typeof tDate[key2] != "function" && typeof tDate[key2] != "undefined")html 		+=this.doTpl(aRepeat[key],tDate[key2],xLev+1);
					}
					tpl				= tpl.replace(rRepeat,html);
				}
			}
			var aVars = tpl.match(rVars);
			if(aVars)
			{
				for (key=0;key<aVars.length;key++)
				{
					aVars[key]		= aVars[key].replace(rVars,"$1");
					tDate 			= data[aVars[key]]?data[aVars[key]]:'';
					var rVars_		= new RegExp("<%="+aVars[key]+"%>");				
					tpl				= tpl.replace(rVars_,tDate);
				}
			}
			return tpl;
		}
	};
}

/**
 * @todo 轮换广告暂停开启处理
 * @author Melon 10-09
 */
var toggleAds = {
	startAds : function(){
		if(window.frames["over_video_info"] && window.frames["over_video_info"].__slide_lunch){
			window.frames["over_video_info"].__slide_lunch();//轮换广告一
		}
		/* if(window.frames["ADS_91_frame"] && window.frames["ADS_91_frame"].switch_ads_91){
			window.frames["ADS_91_frame"].switch_ads_91();//轮换广告二	
		} */
	},
	stopAds : function(){
		if(window.frames["over_video_info"] && window.frames["over_video_info"].__inner_stop){
			window.frames["over_video_info"].__inner_stop();//轮换广告一
		}
		if(window.frames["ADS_91_frame"] && window.frames["ADS_91_frame"].__inner_stop_91){
			window.frames["ADS_91_frame"].__inner_stop_91();//轮换广告二	
		}
		if(window.frames["ADS_41_frame"] && window.frames["ADS_41_frame"].__right_slider_0_clear_interval){
			window.frames["ADS_41_frame"].__right_slider_0_clear_interval();//轮换广告三
		}
		if(window.frames["rAD1_420x60"] && window.frames["rAD1_420x60"].__inner_stop_0){
			window.frames["rAD1_420x60"].__inner_stop_0();//轮换广告四
		}
	}
};

/**
 * @todo 键入监控。单独抽出来，提高性能
 * @author Melon 10-09
 */
function ctlent(objForm,event){
	//gReF.isTyping = true;//正在打字
	if(event.ctrlKey && event.keyCode==13){
		var formName=objForm.form.name;
		var a=document.forms[formName];
		if(gReF.submit(a)){
			a.submit();
		}
	}
}


/**
 *@todo 登录出错处理
 */

function login_box_error() {
 document.getElementById("warning").style.display = "";
}

function clearAuthValue(){
	document.getElementById('auth_img').value = "";
}

function hiddenQuickDiv(){
	gReF.closeRev();
	gReF.openId=0;	
	if(gReCfg['closeTopFormOnShowFastReplyForm'] && gReCfg['open'])gReF.openForm();
 //document.getElementById("quickFrom").style.display ="none";
}

function showComnentFaceBox(){
	document.getElementById('lw_face_box_comment').style.display = "block";
}

function showFaceBox(){
  document.getElementById('lw_face_box').style.display = "block";	
}