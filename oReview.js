/**
 * sort out by kim
 * @author 56.com
 */

/**
 * global review configs
 */
var gReCfg = {
	"host": ""
};

/**
 * global review functions
 */
var gReF = {

	/**
	 * do validates
	 */
	checkSubmit: function(frm) {
		
		// match faces
		var matchFaces = [];
		if(frm.content && frm.content.value){
			matchFaces = frm.content.value.match(/(\[`(a|x)_\w+`\])/g);
		}
		
		if (frm.content && (frm.content.value == '' || gToolF.chklengh(frm.content.value) < 2)) {
			gToolF.statMsg("RE_E_010", "#iframe_stat_call");
			gToolF.alert("提示：您忘了填写评论内容哦，至少1个表情或2个字符！");
			frm.content.focus();
			return false;
		} else if (matchFaces && matchFaces.length > 5) {
			gToolF.alert("提示：您添加表情太多了哦，最多为5个！");
			frm.content.focus();
			return false;
		} else if (!gToolF.gIsAuthed() && frm.auth_img_input && frm.auth_img_input.value == '') {
			gToolF.statMsg("RE_E_011", "#iframe_stat_call");
			gToolF.alert("提示：您忘了输入验证码哦！");
			frm.auth_img_input.focus();
			return false;
		} else if (!gToolF.gIsAuthed() && frm.auth_img_input && frm.auth_img_input.value.length != 4) {
			gToolF.statMsg("RE_E_011", "#iframe_stat_call");
			gToolF.alert("您输入的验证码不足4位哦！");
			frm.auth_img_input.focus();
			return false;
		} else {
	
			// disable submit button
			if (frm.postSubmit) {
				frm.postSubmit.value = '提交中..';
				frm.postSubmit.disabled = true;
				setTimeout(function() {
					try {
						frm.postSubmit.disabled = false;
						frm.postSubmit.value = '提交';
					} catch(e) {}},
					500
				);
			}
			return true;
		}
	},

	/**
	 * when user focus on the reply textarea on top
	 */
	focusReplyTop: function(o) {
		this.openAuthBottom(o);
	},

	/**
	 * when user focus on the reply textarea on bottom
	 */
	focusReplyBottom: function(o) {
		this.openAuthBottom(o);
	},

	/**
	 * open the auth field at the bottom reply area
	 */
	openAuthBottom: function(obj) {
		var o = _.e('auth_img_span_id_top');
		if (o && !gToolF.gIsAuthed() && o.innerHTML == '') {
			o.innerHTML = this.generateAuthImg();
			_.e('auth_img_p_bottom_comment').style.display = '';
		}
	},

	/**
	 * generate the auth image
	 */
	generateAuthImg: function() {
		var img = '<a href="javascript:void(0);" onclick="gReF.changeAuth();">'
			    + '<img onclick="gReF.changeAuth()" id="authImg" name="authImg" alt="换一张" '
			    + 'border="0" src="' + gReCfg.host + 'api/commentApi.php?a=getAuth">'
			    + '</a>';
		return img;
	},

	/**
	 * change the auth image
	 * name="authImg"
	 * attach random numbers to prevent caching
	 */
	changeAuth: function() {
		if (_.e('authImg')) {
			_.e('authImg').src = gReCfg.host + 'api/commentApi.php?a=getAuth&sn=' + Math.random();
		} else {
			gToolF.alert('auth image does not exists!')
		}
	},

	/**
	 * clear the auth text value
	 * name="auth_img_input"
	 */
	clearAuthValue: function() {
		if (_.e('auth_img_input')) {
			_.e('auth_img_input').value = '';
		} else {
			gToolF.alert('auth input field does not exists!');
		}
	},

	/**
	 * when reply submitted and saved
	 */
	replyOk: function() {
		parent.location.reload();
	},

	/**
	 * get some face
	 */
	getFace: function(face, obj) {
		try {
			face = face || '';
			if (obj) {
				// find the form object
				var i = 0;
				while (obj.tagName != "FORM" && i < 10) {
					obj = obj.parentNode;
					i++;
				}
				if (obj) {
					//gReF.contentFocus(document[obj.name].content);
			 		document[obj.name].content.value += face;
				}
			}
		} catch(e) {
		}
	}

};

/***********************************************************************/

var gToolF = {

	// for testing, only return false
	/**
	 * @todo check is user is logged in
	 */
	gIsAuthed: function() {
		var isAuthed = false;
		if (usr.gIsLogin() && usr.gLoginUser().substr(0,5) != "guest") {
			//gReCfg.auth_img = false;
			isAuthed = true;
		}
	    // even guest can be authed until he/she filled the auth field.
		if(_.getCookie("auth_img_limit") > 0){
			//gReCfg.auth_img = false;
			isAuthed = true;
		}
		return isAuthed;
	},

	/**
	 * show the login form is unlogin
	 * @todo to show the latest version
	 */
	showLoginForm: function() {
		if (!this.gIsAuthed()) {
			show_login();
		}
	},
	
	/**
	 * do alert
	 */
	alert: function(a) {
		alert(a);
	},

	/**
	 * do stat
	 */
	statMsg: function(note) {
		note = note || 'comment';
		setStat(note,3000);
	},
	
	/**
	 * check string length
	 * will first replace all chars which are not ASCII to '00'
	 * cause one chinese character gets 2 chars length
	 */
	chklengh: function(str) {
		return str.replace(/[^\x00-\xff]/g, "00").length;
	},

	/**
	 * get the user key press code
	 */
	getKeyCode: function(event) {
		event = event || window.event;
		var charCode = event.which || event.keyCode;
		return charCode;
	},

	/**
	 * monitor user input key, ctrl+enter
	 */
	ctrlEnter: function(objForm, event) {
		if (event.ctrlKey && this.getKeyCode(event) == 13) {
			var formName = objForm.form.name;
			if (formName) {
				var a = document.forms[formName];
				if (gReF.checkSubmit(a)) {
					a.submit();
				}
			}
		}
	}

};

