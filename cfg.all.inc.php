<?php
//*
$scfg["memcache"] = array(
	array('172.16.208.76', 22222,TRUE,75),
	array('172.16.208.77', 22222,TRUE,75),
	array('172.16.245.40', 22222,TRUE,75),
	array('172.16.245.41', 22222,TRUE,75),
	array('172.16.245.34', 22222,TRUE,75),
	array('172.16.245.35', 22222,TRUE,75),
	array('172.16.245.37', 22222,TRUE,75),
	array('172.16.245.38', 22222,TRUE,75), 
);

$scfg["memcached"] = array(
	array('172.16.208.76', 22222,TRUE,75),
	array('172.16.208.77', 22222,TRUE,75),
	array('172.16.245.40', 22222,TRUE,75),
	array('172.16.245.41', 22222,TRUE,75),
	array('172.16.245.34', 22222,TRUE,75),
	array('172.16.245.35', 22222,TRUE,75),
	array('172.16.245.37', 22222,TRUE,75),
	array('172.16.245.38', 22222,TRUE,75), 
);

/** tt servers configure */
//$scfg['tt_server'] = array(
//	array('host' => '172.16.16.250', 'port' => 20000, 'weight' => 10),
//	array('host' => '172.16.16.250', 'port' => 20001, 'weight' => 10),
//	array('host' => '172.16.16.250', 'port' => 20002, 'weight' => 10),
//	array('host' => '172.16.16.250', 'port' => 20003, 'weight' => 10),
//	array('host' => '172.16.16.250', 'port' => 20004, 'weight' => 10),
//	array('host' => '172.16.16.250', 'port' => 20005, 'weight' => 10),
//	array('host' => '172.16.16.250', 'port' => 20006, 'weight' => 10),
//	array('host' => '172.16.16.251', 'port' => 20007, 'weight' => 10),
//	array('host' => '172.16.16.251', 'port' => 20008, 'weight' => 10),
//	array('host' => '172.16.16.251', 'port' => 20009, 'weight' => 10),
//	array('host' => '172.16.16.251', 'port' => 20010, 'weight' => 10),
//	array('host' => '172.16.16.251', 'port' => 20011, 'weight' => 10),
//	array('host' => '172.16.16.251', 'port' => 20012, 'weight' => 10),
//	array('host' => '172.16.16.251', 'port' => 20013, 'weight' => 10),
//	array('host' => '172.16.16.251', 'port' => 20014, 'weight' => 10),
//);
$scfg['tt_server'] = array(
	array('host' => '172.16.16.247', 'port' => 20000, 'weight' => 10),
	array('host' => '172.16.16.247', 'port' => 20001, 'weight' => 10),
	array('host' => '172.16.16.247', 'port' => 20002, 'weight' => 10),
	array('host' => '172.16.16.247', 'port' => 20003, 'weight' => 10),
	array('host' => '172.16.16.247', 'port' => 20004, 'weight' => 10),
	array('host' => '172.16.16.247', 'port' => 20005, 'weight' => 10),
	array('host' => '172.16.16.249', 'port' => 20000, 'weight' => 10),
	array('host' => '172.16.16.249', 'port' => 20001, 'weight' => 10),
	array('host' => '172.16.16.249', 'port' => 20002, 'weight' => 10),
	array('host' => '172.16.16.249', 'port' => 20003, 'weight' => 10),
	array('host' => '172.16.16.249', 'port' => 20004, 'weight' => 10),
	array('host' => '172.16.16.249', 'port' => 20005, 'weight' => 10)
);
/*/
$scfg["memcache"] = array(
						array('127.0.0.1', 11211,TRUE,75),
					);
//*/

# 每页最多show出多少个
define("LIST_ONE_MAX_SHOW",25);
# 每个IP最近24内，最多只能发多少条评论
define("IP_MAX_SEND_COUNT",5000);
# 每个IP最近24内，最多对同一个视频发多少评论
define("IP_MAX_FLV_SEND_COUNT",1000);
# 每个用户，最多对同一个视频连继发多少个评论（游客除外）
define("IP_MORE_FLV_SEND_COUNT",15);
# 每个IP最近24内，最多只能发表多少条相似度在一定值评论 #提示
define("IP_SIMILAR_NOTE",6);
# 每个IP最近24内，最多只能发表多少条相似度在一定值评论 #IP封锁
define("IP_SIMILAR_CUT",12);
# 屏蔽多少条相似度评论 后把IP封锁
define("IP_MAX_SIMILAR",3);
# 相似度取值域
define("MIN_SIMILAR",50);
define("MAX_SIMILAR",80);


# 每页最多show出多少个
define("LIST_ONE_MAX_SHOW",25);
# 两次评论之间的间隔 单位：s
define("COOKIE_COMMENT_ON",2);
# 评论的最少长度
define("MIN_CONTENT_LENGH",2);
# 评论的最大长度
define("MAX_CONTENT_LENGH",1200);
# 评论的回复最大长度
define("MAX_CONTENT_RE_LENGH",300);
# 多少次处于黑名单即被系统禁言
define("BAD_URS2SYSTEM",10);
# 最多可以有多少个无意思字符 
define("MAX_INTERPUNCTION",10);

# JSON_TMP_VARS_NAME 注:这个设定完后就不能改动
define("JSON_TMP_VARS_NAME","_XYP");
# IE缓存时间过期时间
define("CACHED_OUT_TIME",60);
# 文件cache 开关
define("FILECACHE_ON_OFF",FALSE);//不要了Melon 100331 都改用mm
define("MEMCACHED_ON_OFF",true); 

# JPG文件名扩展 :  
$scfg['config']['com_jpg_id'] = "i56olo56i56.com_";
