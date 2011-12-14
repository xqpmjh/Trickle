<?php
if (isset($_GET['dg']) and $_GET['dg'] == 'ml'){
	error_reporting(E_ALL ^ E_NOTICE);
	ini_set ("display_errors", "On");
}else{
	error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
	ini_set ("display_errors", 0);
}
//<meta http-equiv="Content-Type" content="text/html; charset=gb2312">
header ( "Content-type: text/html; charset=UTF-8" );
# ��Ŀ¼
define("ROOT_DIR", str_replace("\\", "/", dirname(__FILE__)) . "/");
# ģ��
//define("TPL_DIR",ROOT_DIR."tpl/");
# cacheĿ¼
define("CACHE_DIR",ROOT_DIR."review/data_cache_dir/");
# ���cache
define("DATACACHE_DIR",CACHE_DIR."DATACACHE_DIR/");
# �ļ�cache
define("FILECACHE_DIR",CACHE_DIR."FILECACHE_DIR/"); 
# HTMLcache
define("C_HTML",CACHE_DIR."C_HTML/");

//�Ƿ��ͳ�� 
define("STAT3_ON",FALSE);
# ��
require ROOT_DIR . "cfg.all.inc.php";
require ROOT_DIR . "cfg.local.inc.php";
require ROOT_DIR . "inc_mongo.php";

# Autoload Function
function __autoload($class)
{
	$class= explode('_', $class);
	$i= count($class) - 1;
	$class[$i]= 'class.' . $class[$i];
	$class= implode('/', $class);
	include ROOT_DIR . 'include/' . $class . '.php';
}
# ����:���뷽��·��
function route($class, $method)
{
	class_exists($class) 
		|| trigger_error("NO_CLASS: <b>$class</b> ", E_USER_ERROR);
	$Object= new $class ();
	method_exists($Object, $method) 
		|| trigger_error("THIS_CLASS: <b>$class</b>  NO_METHOD: <b>$method</b> ", E_USER_ERROR);
	$Object-> $method ();
	return true;
}
# PHP��ݺ���
function & O(& $o)
{
	return $o;
};
function & A(& $a, $k)
{
	return $a[$k];
};
/**
* ����:�õ�ǰ�û���
* ����:��
* ����:�û���(��@56.com)
**/
function u_get_username()
{
	$cookie_name = 'pass_hex';
	$cookie_value = &$_COOKIE['pass_hex'];
	
	//get user_id
	if($cookie_value) {
		list($user_id) = explode("@", $_COOKIE['member_id']);
		$user_id = trim(strtolower($user_id));
	}
	
	if(strlen($cookie_value) == 32 && date("Ymd") < 20090128) {
		$member_id 		= isset($_COOKIE['member_id']) ? $_COOKIE['member_id'] : null;
		$pass_hex 		= isset($_COOKIE['pass_hex']) ? $_COOKIE['pass_hex'] : null;
		$member_login 	= isset($_COOKIE['member_login']) ? $_COOKIE['member_login'] : null;
	
		if( md5( substr(base64_encode($member_id . "|" . $pass_hex), 0, 20)) == $member_login ) {
			return $user_id;
		} else {
			return false;
		}
	}
	
	if(empty($cookie_value) || strlen($cookie_value) != 40) return false;
	
	$checksum = $random_key = $secret_key = $key_version = "";
	$tmp_key = $md5_key = "";
	$handle = fopen("/dev/shm/secrectkey.56", "r");
	$valid_secret_keys = array();
	if ($handle) {
	    while (!feof($handle)) {
	        $buffer = trim(fgets($handle, 4096));
	        if (empty($buffer) || substr_compare($buffer,"#",0,1)==0) continue;
	        list($k,$v) = explode(" ",$buffer,2);
			$valid_secret_keys["$k"] = $v;
	    }
	    fclose($handle);
	}
	//check key format & get checksum, tmp_key
	if(sscanf($cookie_value, "%39s%u", $tmp_key, $checksum) != 2 ) return false;
	
	//checksum
	if(substr(sprintf("%u", crc32($tmp_key)), -1) != $checksum) return false;
	
	//get $key_version, $random_key, $key
	list($key_version, $random_key, $md5_key) = sscanf($tmp_key, "%3s%4s%s");
	
	//check version of secret key
	if(!array_key_exists($key_version, $valid_secret_keys)) return false;
	
	//check md5_key
	if($md5_key != md5(sprintf("%s|%s|%s", $user_id, $valid_secret_keys[$key_version], $random_key))) return false;
		
	return $user_id;
}
/**
* ����:�û�ID
* ����:$username �û���(��@56.com)
* ����:�û�ID(����@56.com)
*/
function u_get_user_id($username= null)
{
	$username= $username ? $username : u_get_username();
	if ($username)
	{
		$username_no_suffix= explode('@', $username);
		$username_no_suffix= $username_no_suffix[0];
		return $username_no_suffix;
	}
	return false;
}
# ����Ƿ��¼
$username = u_get_username();
$user_id  = u_get_user_id($username);
$username_no_suffix= $user_id;
#���峣��
define("username", $username);
define("user_id", $user_id);

$dd_pct            = intval($pct[$_REQUEST['pct']]?$pct[$_REQUEST['pct']]:$_REQUEST['pct']);
$dd_vid		= g::flvDeId($_REQUEST['id']);

function mCallback($data,$script = FALSE,$appendScript = ''){
	$append = array('reload'=>$_REQUEST['reload'],'hide'=>$_REQUEST['hide']);
	$data = array_merge($data,$append);
	$data = json_encode($data);
	if ($script === TRUE){
		echo "<script type=\"text/javascript\">\n";
		echo "try{document.domain = \"56.com\";}catch(e){};\n";
	}
	if ($_REQUEST['callback']){
		echo sprintf("%s(%s);\n", $_REQUEST['callback'], $data);
	}
	if ($appendScript){
		echo $appendScript . "\n";
	}
	if ($script === TRUE){
		echo "</script>\n";
	}
}

