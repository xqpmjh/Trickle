<?php
/**
 * @todo 把session数据记录在memcache上
 * @author Tingo 2009.4.17
 **/

class SessionHandle {
	protected static $_lift_time = 1440;	//session 生命周期
	protected static $_name = '';	//session名
	protected static $_id = ''; //session ID
	protected static $_type = '';
	protected static $_key_pre = '56_validate_code_';	//session 的memcache key前缀
	protected $mem;
	
	public function __construct($name = '', $sessId = '', $type = 'memcache') {
		self :: $_name = $name;
		self :: $_id = $sessId;
		self :: $_type = $type;
	}
	
	public function Session_Start() {
		switch(self :: $_type) {
			case 'memcache':
				session_set_save_handler(
					array(&$this, "open"),
					array(&$this, "close"),
					array(&$this, "read"),
					array(&$this, "write"),
					array(&$this, "destroy"),
					array(&$this, "gc")
				);
				break;
			/*
			case 'mysql':
				....
				break;
			*/
		}
		
		session_set_cookie_params(0, '/', '56.com');
		
		if(self :: $_name) session_name(self :: $_name);
		if(self :: $_id) session_id(self :: $_id);
		
		session_start();
	}
	
	public function Session_Destroy() {
		session_destroy();
	}
	
	function open($savePath, $sessName) {
		return true;
	}
   
    function close() {
		return true;
	}
     
    function read($key) {
    	$this -> mem = new datacache(self :: $_key_pre . $key);
		$value = $this -> mem -> get();
		if($value) return $value;
		else return false;
	}
	
	function write($key, $val) {
		$this -> mem = new datacache(self :: $_key_pre . $key);
		$this -> mem -> put($val, $this -> _lift_time);
	}
	
	function destroy($key) {
		$this -> mem = new datacache(self :: $_key_pre . $key);
		$this -> mem -> put(serialize(false), $this -> _lift_time);
	}

	function gc($maxlifetime) {
		return true;
	}
}
?>