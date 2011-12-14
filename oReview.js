/**
 * sort out by kim
 * @author 56.com
 */

/**
 * global review configs
 */
var gReCfg = {
	"host": "",
};

/**
 * global review functions
 */
var gReF = {

	/**
	 * do validates
	 */
	checkSubmit: function(a) {
		if (!gToolF.gIsLogin() && a.auth_img_input && a.auth_img_input.value == '') {
			gToolF.alert("提示：您忘了输入验证码哦！");
			a.auth_img_input.focus();
			return false;
		} else if (!gToolF.gIsLogin() && a.auth_img_input && a.auth_img_input.value.length != 4) {
			gToolF.alert("您输入的验证码不足4位哦！");
			a.auth_img_input.focus();
			return false;
		} else {
	
			// disable submit button
			if (a.postSubmit) {
				a.postSubmit.value = '提交中..';
				a.postSubmit.disabled = true;
				setTimeout(function() {
					try {
						a.postSubmit.disabled = false;
						a.postSubmit.value = '提交';
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
		if (o && !gToolF.gIsLogin() && o.innerHTML == '') {
			o.innerHTML = this.generateAuthImg();
			_.e('auth_img_p_bottom_comment').style.display = '';
		}
	},

	/**
	 * generate the auth image
	 */
	generateAuthImg: function() {
		var img = '<a href="javascript:void(0);" onclick="gReF.changeAuth();">'
			    + '<img onclick="gReF.changeAuth()" id="authImg" alt="换一张" '
			    + 'border="0" src="' + gReCfg.host + 'api/comment.php?a=getAuth">'
			    + '</a>';
		return img;
	},

	/**
	 * change the auth image
	 */
	changeAuth: function() {
		if (_.e('authImg')) {
			_.e('authImg').src = gReCfg.host + 'api/comment.php?a=getAuth&sn=' + Math.random();
		} else {
			gToolF.alert('auth image does not exists!')
		}
	},

	/**
	 * clear the auth text value
	 */
	clearAuthValue: function() {
		if (_.e('auth_img_input')) {
			_.e('auth_img_input').value = '';
		} else {
			gToolF.alert('auth input field does not exists!');
		}
	}

};

/***********************************************************************/

var gToolF = {

	// for testing, only return false
	/**
	 * @todo check is user is logged in
	 */
	gIsLogin: function() {
		return false;
	},

	/**
	 * do alert
	 */
	alert: function(a) {
		alert(a);
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

}
