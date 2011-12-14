<?php
/**
 * 制作一个验证码和一个生成验证码的key
 *
 * @param Int $width  验证码图片的宽(1-200)
 * @param Int $height 验证码图片的高(1-200)
 * @param Int $length 验证码长度(1-9)
 * @param String $type 验证码类型（char：纯字母，num：纯数字，both：两种都有）
 * @param boolean $sess_start 是否打开session
 * @return “验证码/生成验证码图片的key” 例如："abcd/75|20|002|472912a3502cdbd0b9c5c2fe9776fe271b6f0EWzl"
 * 			如果把$sess_start选项打开，直接返回加密串，用程序跳转一下就可以了，如：http://code.auth.56.com/index.php?key=75|20|002|472912a3502cdbd0b9c5c2fe9776fe271b6f0EWzl
 * @example 
 * 生成一个宽80，高30，验证码长度为5，字母与数字组合的验证码：$valid_key = Auth :: MakeAuth(75, 20, 4, 'both', true, '56zvcode');
 * 假如需要打开session，可以把$sess_start参数设为true，session name 和session id也可以根据实际情况定义
 */
class Auth {
	public static function MakeAuth($width = 75, $height = 20, $length = 4, $type = 'char', $sess_start = false, $sess_name = false, $sess_id = false) {
		if($width < 1 || $width > 200) $width = 75;
		if($height < 1 || $height > 200) $width = 20;
		if($length < 1 || $length > 9) $length = 4;
		$handle = fopen("/dev/shm/secrectkey.56", "r");
		$valid_secret_keys = array();
		if ($handle) {
		    while (!feof($handle)) {
		        $buffer = trim(fgets($handle, 4096));
		        if (empty($buffer) || substr_compare($buffer,"#",0,1)==0) continue;
		        list($k,$v) = split(" ",$buffer,2);
				$valid_secret_keys["$k"] = $v;
		    }
		    fclose($handle);
		}
		
		switch ($type) {
			case 'char':
				$rand_string = self :: _rand_string($length, '0');
				break;
			case 'num':
				$rand_string = self :: _rand_string($length, '1');
				break;
			default:
				$rand_string = self :: _rand_string($length, '3');
				break;
		}
		
		$encodeStr = self :: _wheel($rand_string);
		$encodeArr = explode('|', $encodeStr);
		$encodeStr = sprintf('%s' . $length . '%s' . $length, $encodeArr[0], $encodeArr[1]);
		
		$valid_rand_key = array_rand($valid_secret_keys);
		$valid_key = $valid_secret_keys[$valid_rand_key];
		
		$secret_key = sprintf('%s|%s|%s|%s%s%s%s', $width, $height, $valid_rand_key, $length, $encodeArr[1], md5($valid_key . $encodeArr[0] . $width . $height . $length), $encodeArr[0]);
		
		/**
		 这里的Session用自定义的方式写，一般可以不用这个机制，实例化时就是$sess = new SessionHandle($sess_name, $sess_id, '');
		 **/
		if($sess_start) {
			$sess = new SessionHandle($sess_name, $sess_id);
			$sess -> Session_Start();
			$_SESSION['auth'] = $rand_string;
			return $secret_key;
		}
		
		return $rand_string . '/' . $secret_key;
	}
	
	protected static function _rand_string($len = 5, $type = '2', $addChars = '') { 
		$str = '';
		switch($type) { 
			case '0':
		    	$chars = "ABCDEFGHIJKLMNPQRSTUVWXYZabcdefghijklmnpqrstuvwxyz" . $addChars; 
		    	break;
		    case '1':
		      	$chars = "123456789"; 
		      	break;
			case '2':
			 	$chars = "abcdefghijklmnpqrstuvwxyz123456789";
			  	break;
			case '3':
				$chars = "ABCDEFGHIJKLMNPQRSTUVWXYZ123456789" . $addChars; 
				break;
		    default :
		      	$chars = "ABCDEFGHIJKLMNPQRSTUVWXYZabcdefghijklmnpqrstuvwxyz123456789" . $addChars; 
		      	break;
		}
		$chars = str_shuffle($chars);
		$str = substr($chars, 1, $len);
		return $str;
	}
	
	protected static function _wheel($str, $moves = '') {		//转轮机	
		$encode = '';
		$m = strlen($str);
		if($m > 1) {
			$moveStr = '';
			for($i = 0;$i < $m;$i++) {
				$moves = rand(1, 9);
				$moveStr .= $moves;
				$after_wheel = self :: _wheel($str{$i}, $moves);
				$encode .= chr($after_wheel);
			}
			return $encode . '|' . $moveStr;
		} else {
			$ascii = ord($str);
			$after_wheel = $moves + $ascii;
			if($ascii >= 48 && $ascii <= 57 && $after_wheel > 57) { //在0-9的区间，但是转轮后超出这个区间，那么就放在A-Z的区间内
				$after_wheel = $after_wheel - 57 + 64;
			} elseif($ascii >= 65 && $ascii <= 90 && $after_wheel > 90) {//在A-Z的区间，但是转轮后超出这个区间，那么就放在a-z的区间内
				$after_wheel = $after_wheel - 90 + 96;
			} elseif($ascii >= 97 && $ascii <= 122 && $after_wheel > 122) {//在a-z的区间，但是转轮后超出这个区间，那么就放在0-9的区间内
				$after_wheel = $after_wheel - 122 + 47;
			}
			return $after_wheel;
		}
	}
}
?>