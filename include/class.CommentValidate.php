<?php
/**
 * class CommentValidate
 *
 * @author kim
 * @since 2011-12-15
 * @version 1.0.0
 *
 */
class CommentValidate
{
    /**
     * company ips which boundless limited
     * @var array
     */
    static protected $_companyIps = array(
        '119.145.139.227', '119.145.139.232', '121.9.215.27',
        '119.161.156.172', '119.161.156.173', '116.114.8.48',
        '119.32.28.114', '221.5.67.236', '121.8.37.170', '114.83.5.223',
    );

    /**
     * check if current user is guest or not
     * @return boolean
     */
    static public function isGuest()
    {
        $isGuest = true;
        if (defined('user_id') && user_id && substr(user_id, 0, 5) != 'guest') {
            $isGuest = false;
        }
        return $isGuest;
    }

    /**
     * do validate of the comment actions
     * 
     * @return $rs - array of validate result
     */
    static public function doInsertValid()
    {
        // start session
        $sess = new SessionHandle('56zvcode');
        $sess->Session_Start();

        /**
         * logged in user doesn't need image auth, else need to check auth code
         */
        if (!self::isGuest()) {
            $isAuthPassed = true;
        } else {
            $authImg = isset($_POST['auth_img_input']) ? strtolower($_POST['auth_img_input']) : '';
            $isAuthPassed = ($authImg and !empty($_SESSION['auth']) and ($authImg == strtolower($_SESSION['auth'])));
        }

        if ($isAuthPassed) {
            // filter content
            $content = $_POST['content'];
            $content = self::filterInvalidChars($content);
            
            // filter user nickname
            if (self::isGuest()) {
                $nickname = '游客';
                $commentUserId = '56com';
            } else {
                $nickname = u::info(user_id, 'LastName');
                $commentUserId = trim(user_id);
            }
            $nickname = self::filterInvalidChars($nickname);

            // the video's owner id
            $videoUserId = $_POST['vuid'];
            //$videoUserId = 'guest176453412'; // for testing
            //$videoUserId = 'a0530zb923897'; $commentUserId = 'dongyanj'; // for testing
            
            // user ip
            $ip = g::ip ();
            
            // 3 samples for testing, should be commentted
            //$ip = '218.87.190.84'; // ban ip
            //$commentUserId = 'wdvas999'; // ban user id
            //$ip = '127.87.190.22'; $commentUserId = 'dvspace';  // ip is ok but user id in black list

            // do validates
            if (isset($_SESSION['comment_last_time']) and ($_SESSION['comment_last_time'] + COOKIE_COMMENT_ON > time())) {
                $rs = array(false, '提示：你发表留言的速度太快，请稍后！', '', 'E_002');
                
            } else if (strlen($content) < MIN_CONTENT_LENGH) {
                $rs = array(false, '提示：留言内容不能为空，并不能少于' . MIN_CONTENT_LENGH . '个字符！', '', 'E_003');
                
            } else if (self::containBadWordA($content)) {
    			$rs = array(false, "提示：您的留言中含有敏感词语，不能发表！", "", "E_004" );
    			
            } else if (self::containBadWordA($nickname)) {
			    $rs = array(false, "提示：您的用户昵称中含有敏感词语，请在更改昵称，现在不能发表!". "\n" . "更改昵称方法：". "\n" . "“管理中心” -> 头像下方“修改资料”->“个人资料” -> 更改“呢称” -> 点“保存资料” .", "", "E_005" );
			    
		    } else if (self::exceedIpSendLimit($ip)) {
			    $rs = array(false, "提示：从您的最近留言记录，管理员发现您的留言速度太快，请先休息一会儿。", "", "E_016" );
			    
		    } else if (self::forbiddenBySystem($commentUserId, $ip)) {
			    $rs = array(false, "提示：对不起，您已经被系统禁止发言。", "", "E_007");
			    
		    } else if (self::forbiddenByUser($commentUserId, $videoUserId)) {
    			if (self::isGuest()) {
    				$rs = array(false, "提示：对不起，地主{$videoUserId}禁止“游客”对他评论! 请先登录。", "parent.gReF.loginForm();", "E_008");
    			} else {
    				$rs = array(false, "提示：对不起，地主{$videoUserId}禁止您对他(她)的作品进行评论及对他(她)的留言。", "", "E_009");
    			}
		    } else {
                $_SESSION['comment_last_time'] = time();
                $rs = array(true, '成功！', 'parent.gReF.replyOk();', 'E_000');
            }
            
        } else {
            $rs = array(false, '提示：请输入正确的验证码！', 'parent.gReF.changeAuth();', 'E_001');
        }
        return $rs;
    }

    /**
     * check if the current ip exceed the max sending limit
     * except ips from boundless limited ip list
     * 
     * @param string $ip
     * @return boolean
     */
    static protected function exceedIpSendLimit($ip)
    {
        $isExceed = false;
        $dc = new datacache("max_send|{$ip}" . date("Ymd"));
        $rs = (int)$dc->get();
        if ($rs >= IP_MAX_SEND_COUNT && !in_array($ip, self::$_companyIps)) {
            $isExceed = true;
        } else {
            $time = time();
            $rs = $rs + 1;
            $dc->put($rs, strtotime(date("Ymd", $time + 86400)) - $time);
        }
        return $isExceed;
    }
    
    /**
     * forbidden by system
     * 
     * @param string $curCommentUserId
     * @param string $ip
     * @return boolean
     */
    static protected function forbiddenBySystem($curCommentUserId, $ip)
    {
        $result = true;
        
        // get ban user ids
        $banUserIds = self::file("http://api.v.56.com/review/bad_user_id.ini");
        if (!is_array($banUserIds)) {
            $banUserIds = @explode(",", $banUserIds);
        } else {
            $banUserIds = array();
        }

        // get ban user ips
        $banIps = self::file("http://api.v.56.com/review/bad_ip.ini");
        if (!is_array($banIps)) {
            $banIps = @explode(",", $banIps);
        } else {
            $banIps = array();
        }

        if (in_array($curCommentUserId, $banUserIds) || in_array($ip, $banIps)) {
            $result = true;
        } else if (self::isGuest()) {
            $result = false;
        } else if (in_array($ip, self::$_companyIps)) {
            $result = false;
        } else {
            global $scfg;
            $db	= new db($scfg['db']);
            $rsArray = $db->rsArray("SELECT COUNT(*) AS cc FROM flv_comment_bad_user_list WHERE badname = '" . $curCommentUserId . "' ");
            if ($rsArray['cc'] >= BAD_URS2SYSTEM) { //10次处于黑名单即被系统禁言
                $result = true;
            } else {
                $result = false;
            }
        }
        return $result;
    }
    
    /**
     * forbidden by user (usually the video's owner)
     * 
     * @param string $curCommentUserId
     * @param string $dataUserId
     * @return boolean
     */
    static protected function forbiddenByUser($curCommentUserId, $dataUserId)
    {
        $result = false;
        
        if (self::isGuest()) {
            $forbidGuestUsers = self::file ( "http://api.v.56.com/review/no_guest_user.ini" );
            $forbidGuestUsers = !is_array($forbidGuestUsers) ? @explode(",", $forbidGuestUsers) : array();
            if (is_array($forbidGuestUsers) && in_array($dataUserId, $forbidGuestUsers)) {
                $result = true;
            }
        } else {
            $sql = array('myname' => $dataUserId, 'badname' => $curCommentUserId);
            global $scfg;
            $db	= new db($scfg['db']);
            $rsCount = $db->rsArray("SELECT badname FROM flv_comment_bad_user_list WHERE myname=:myname AND badname=:badname ", $sql);
            if ($rsCount) {
                return true;
            }
        }
        return $result;
    }
    
    /**
     * check current comment's status, mainly check whether contain
     * bad words of B level
     *
     * @param string $content - the comment content
     * @param int $vid - the video's id
     * @param string $pct - the video's category
     * @return string - comment status
     */
    static public function checkCommentStatus($content, $vid, $pct)
    {
        if (self::containBadWordB($content)) {
            $status = Comment::STATUS_BADWORDS;
        } else {
            $status = Comment::STATUS_PENDING;
        }

        if ($vid && $pct == 1) {
            $vid = (int)$vid;
            $homeVids = self::getHomeVid();
            // home page vedio's comment always need verify
            if (in_array($vid, $homeVids)) {
                $status = Comment::STATUS_BADWORDS;
            }
        }
        return $status;
    }

    /**
     * @todo 获取首页视频地址
     * @author Melon`` @ 2010
     * 
     * @return array
     */
    public static function getHomeVid()
    {
        $data = array();
        $dc = new datacache ('homePageVids');
        $data = $dc->get ();
        if ($data === false || $_GET ['i'] == 'c') {
            $ids1 = $ids2 = array();
            $page = file_get_contents("http://www.56.com/");
            if ($page) {
                $count = preg_match_all("/http:\/\/www\.56\.com\/u\d{2}\/v_(\w+)\.html/im", $page, $match);
                if ($count) {
                    $ids1 = array_values(array_unique($match[1]));
                }
                $count = preg_match_all("/http:\/\/www\.56\.com\/w\d{2}\/play_album-aid-\d+_vid-(\w+)\.html/im", $page, $match);
                if ($count) {
                    $ids2 = array_values(array_unique($match [1]));
                }
            }
            $data = array_merge($ids1, $ids2);
            if ($data && is_array($data) && count($data) > 30) {
                foreach ($data as & $v) {
                    $v = g::flvDeId ($v);
                }
                $dc->put ($data, 900);
            }
        }
        return $data;
    }

    /**
     * filter invalid chars
     * 
     * @param string $string
     * @return string
     */
    static public function filterInvalidChars($string)
    {
        $string = strtr(urldecode($string), array(
            '１' => '1', '２' => '2', '３' => '3', '４' => '4', '５' => '5',
            '６' => '6', '７' => '7', '８' => '8', '９' => '9', '０' => '0',
            'ａ' => 'a', 'ｂ' => 'b', 'ｃ' => 'c', 'ｄ' => 'd', 'ｅ' => 'e',
            'ｆ' => 'f', 'ｇ' => 'g', 'ｈ' => 'h', 'ｉ' => 'i', 'ｊ' => 'j',
            'ｋ' => 'k', 'ｌ' => 'l', 'ｍ' => 'm', 'ｎ' => 'n', 'ｏ' => 'o', 
            'ｐ' => 'p', 'ｑ' => 'q', 'ｒ' => 'r', 'ｓ' => 's', 'ｔ' => 't',
            'ｕ' => 'u', 'ｖ' => 'v', 'ｗ' => 'w', 'ｘ' => 'x', 'ｙ' => 'y',
            'ｚ' => 'z', 'Ａ' => 'A', 'Ｂ' => 'B', 'Ｃ' => 'C', 'Ｄ' => 'D',
            'Ｅ' => 'E', 'Ｆ' => 'F', 'Ｇ' => 'G', 'Ｈ' => 'H', 'Ｉ' => 'I',
            'Ｊ' => 'J', 'Ｋ' => 'K', 'Ｌ' => 'L', 'Ｍ' => 'M', 'Ｎ' => 'N',
            'Ｏ' => 'O', 'Ｐ' => 'P', 'Ｑ' => 'Q', 'Ｒ' => 'R', 'Ｓ' => 'S', 
            'Ｔ' => 'T', 'Ｕ' => 'U', 'Ｖ' => 'V', 'Ｗ' => 'W', 'Ｘ' => 'X', 
            'Ｙ' => 'Y', 'Ｚ' => 'Z', '　' => ' ', '，' => ',', '。' => '.', 
            '？' => '?', '＜' => '<', '＞' => '>', '［' => '[', '］' => ']', 
            '＊' => '*', '＆' => '&', '＾' => '^', '％' => '%', '＃' => '#', 
            '＠' => '@', '！' => '!', '（' => '(', '）' => ')', '＋' => '+', 
            '－' => '-', '｜' => '|', '：' => ':', '；' => ':', '点' => '.', 
            '｛' => '{', '｝' => '}', '／' => '/', '＂' => '"', '＝' => '=', 
            '×' => '', '．' => '.', '零' => '0', '一' => '1', '二' => '2', 
            '三' => '3', '四' => '4', '五' => '5', '六' => '6', '七' => '7', 
            '八' => '8', '九' => '9', '壹' => '1', '贰' => '2', '叁' => '3',
            '肆' => '4', '伍' => '5', '陆' => '6', '柒' => '7', '捌' => '8',
            '玖' => '9',
            /*'ⅰ' => '1', 'ⅱ' => '2', 'ⅲ' => '3', 'ⅳ' => '4', 'ⅴ' => '5',
            'ⅵ' => '6', 'ⅶ' => '7', 'ⅷ' => '8', 'ⅸ' => '9', 'ⅹ' => '10',*/
            '⒈' => '1', '⒉' => '2', '⒊' => '3', '⒋' => '4', '⒌' => '5',
            '⒍' => '6', '⒎' => '7', '⒏' => '8', '⒐' => '9', '⒑' => '10',
            '⒒' => '11', '⒓' => '12', '⒔' => '13', '⒕' => '14', 
            '⒖' => '15', '⒗' => '16', '⒘' => '17', '⒙' => '18',
            '⒚' => '19', '⒛' => '20', '⑴' => '1', '⑵' => '2', '⑶' => '3',
            '⑷' => '4', '⑸' => '5', '⑹' => '6', '⑺' => '7', '⑻' => '8', 
            '⑼' => '9', '⑽' => '10', '⑾' => '11', '⑿' => '12', '⒀' => '13',
            '⒁' => '14', '⒂' => '15', '⒃' => '16', '⒄' => '17', '⒅' => '18', 
            '⒆' => '19', '⒇' => '20', '①' => '1', '②' => '2', '③' => '3',
            '④' => '4', '⑤' => '5', '⑥' => '6', '⑦' => '7', '⑧' => '8', 
            '⑨' => '9', '⑩' => '10', '㈠' => '1', '㈡' => '2', '㈢' => '3',
            '㈣' => '4', '㈤' => '5', '㈥' => '6', '㈦' => '7', '㈧' => '8',
            '㈨' => '9', '㈩' => '10',
            /*'Ⅰ' => '1', 'Ⅱ' => '2', 'Ⅲ' => '3', 'Ⅳ' => '4', 'Ⅴ' => '5',
            'Ⅵ' => '6', 'Ⅶ' => '7', 'Ⅷ' => '8', 'Ⅸ' => '9', 'Ⅹ' => '10',
            'Ⅺ' => '11', 'Ⅻ' => '12',*/
        ));
        return preg_replace(array("/\s/", "/\[.+?\]/", //把[]之类的去除
            //"/[^0-9a-z" . chr(0xa1) . "-" . chr(0xff) . "]/" ,
            //"/　|、|。|·|ˉ|ˇ|¨|〃|々|～|‖|…|〔|〕|〈|〉|《|》|「|」|『|』|〖|〗|【|】|±|×|÷|∶|∧|∨|∑|∏|∪|∩|∈|∷|√|⊥|∥|∠|⌒|⊙|∫|∮|≡|≌|≈|∽|∝|≠|≮|≯|≤|≥|∞|∵|∴|♂|♀|°|′|″|℃|＄|¤|￠|￡|‰|§|№|※|→|←|↑|↓|ⅰ|ⅱ|ⅲ|ⅳ|ⅴ|ⅵ|ⅶ|ⅷ|ⅸ|ⅹ|⒈|⒉|⒊|⒋|⒌|⒍|⒎|⒏|⒐|⒑|⒒|⒓|⒔|⒕|⒖|⒗|⒘|⒙|⒚|⒛|⑴|⑵|⑶|⑷|⑸|⑹|⑺|⑻|⑼|⑽|⑾|⑿|⒀|⒁|⒂|⒃|⒄|⒅|⒆|⒇|①|②|③|④|⑤|⑥|⑦|⑧|⑨|⑩|㈠|㈡|㈢|㈣|㈤|㈥|㈦|㈧|㈨|㈩|Ⅰ|Ⅱ|Ⅲ|Ⅳ|Ⅴ|Ⅵ|Ⅶ|Ⅷ|Ⅸ|Ⅹ|Ⅺ|Ⅻ|！|＂|＃|￥|％|＆|＇|（|）|＊|＋|，|－|．|／|０|１|２|３|４|５|６|７|８|９|：|；|＜|＝|＞|？|＠|Ａ|Ｂ|Ｃ|Ｄ|Ｅ|Ｆ|Ｇ|Ｈ|Ｉ|Ｊ|Ｋ|Ｌ|Ｍ|Ｎ|Ｏ|Ｐ|Ｑ|Ｒ|Ｓ|Ｔ|Ｕ|Ｖ|Ｗ|Ｘ|Ｙ|Ｚ|［|＼|］|＾|＿|｀|ａ|ｂ|ｃ|ｄ|ｅ|ｆ|ｇ|ｈ|ｉ|ｊ|ｋ|ｌ|ｍ|ｎ|ｏ|ｐ|ｑ|ｒ|ｓ|ｔ|ｕ|ｖ|ｗ|ｘ|ｙ|ｚ|｛|｜|｝|￣|Α|Β|Γ|Δ|Ε|Ζ|Η|Θ|Ι|Κ|Λ|Μ|Ν|Ξ|Ο|Π|Ρ|Σ|Τ|Υ|Φ|Χ|Ψ|Ω|α|β|γ|δ|ε|ζ|η|θ|ι|κ|λ|μ|ν|ξ|ο|π|ρ|σ|τ|υ|φ|χ|ψ|ω|ā|á|ǎ|à|ē|é|ě|è|ī|í|ǐ|ì|ō|ó|ǒ|ò|ū|ú|ǔ|ù|ǖ|ǘ|ǚ|ǜ|ü|ê|ㄅ|ㄆ|ㄇ|ㄈ|ㄉ|ㄊ|ㄋ|ㄌ|ㄍ|ㄎ|ㄏ|ㄐ|ㄑ|ㄒ|ㄓ|ㄔ|ㄕ|ㄖ|ㄗ|ㄘ|ㄙ|ㄚ|ㄛ|ㄜ|ㄝ|ㄞ|ㄟ|ㄠ|ㄡ|ㄢ|ㄣ|ㄤ|ㄥ|ㄦ|ㄧ|ㄨ|ㄩ/" ,
            "/ +(la|sh|ac|io|ws|us|tm|cc|tv|vc|ag|bz|in|mn|sc|me|com|net|biz|org|info|hk|name|travel|mobi|tw|idv|jobs|asiaat|be|ca|ch|cn|de|dk|es|eu|fr|hn|it|li|md|nu|se|co|uk)([^a-z])?/", "/ +([a-z]{2,3})(i|o|e|vel)?([^a-z])?/" )//"/([0-9a-z])[" . chr(0xa1) . "-" . chr(0xff) . "]{2,4}([0-9a-z])/" ,
            //"/ /" ,
            /*"/([" . chr(0xa1) . "-" . chr(0xff) . "])(\w{1,2})([" . chr(0xa1) . "-" . chr(0xff) . "])/e"*/
            , array(" ", "|" , /*" " , " " ,*/".\\1\\2", ".\\1\\2\\3", /*"\\1 \\2" , "" , "self::format_un('\\1','\\2','\\3')"*/), strtolower ( $string ) . " "
        );
    }
    
    /**
     * check if the string has bad words
     * A means : first level of bad words
     * 
     * @param string $string
     * @return boolean - whether the string contain bad words
     */
    static public function containBadWordA($string)
    {
        $count = 0;
        $url = "http://api.v.56.com/API/black_word_lib.php?type=for_reviewA";
        $lines = self::file($url);

        // if no bad words
        if (!$lines) return false;

        if (!is_array($lines) || strpos($lines, ',')) {
            $lines = explode(',', $lines);
        }

        //var_dump($lines);die;
        
        $comCount = substr_count($string, "56.com");
        $httpCount = substr_count($string, "http");
        //echo "str:".$str;
        $str = array ();
        $str[] = str_replace($lines, '', $string, $count);

        if ($count) {
            $result = true;
        } else if (substr_count($string, ".com") != $comCount) {
            $result = true;
        } else if (($httpCount != $comCount) and $httpCount) {
            $result = true;
        } else {
            $result = false;
        }
        return $result;
    }

    /**
     * check if the string has bad words
     * B means : first level of bad words
     * 
     * @param string $string
     * @return boolean - whether the string contain bad words
     */
    static public function containBadWordB($string)
    {
        $count = 0;
        $url = "http://api.v.56.com/API/black_word_lib.php?type=for_reviewB";
        $lines = self::file($url);

        $result = true;
        // if no bad words
        if (!$lines) return false;

        if (!is_array($lines) || strpos($lines, ',')) {
            $lines = explode(',', $lines);
        }
        //var_dump($lines);

        $str = array();
        $str[] = str_replace($lines, '', $string, $count);
        if (!$count) {
            $result = false;
        }
        return $result;
    }
    
    /**
     * get file from url
     * 
     * @param string $url
     * @return string|array
     */
    static public function file($url)
    {
        $dc = new datacache ( md5($url) );
        $rs = $dc->get ();
        $dc_timeset_key  = new datacache ( md5($url.'@_time') ); // 时间
        $rs_timeset = $dc_timeset_key->get();
        if (  $_GET['i']=='c' || empty ( $rs) || !$rs_timeset) { // 取不到数据 或者 已经过期
            //@header( md5($url) .':cache');
            if(!$rs_timeset || $_GET['i']=='c'){ // 过期了
    
                $str = g::file_get_contents ( $url );
                $str = str_replace(array('。',"\n"),',',$str);  // 有的词库以 换行来分割
                if($_GET['dg']=='ml'){
                    var_dump($url,$str);
                }
                $rs1 = @explode ( ',', $str );
                if (is_array ( $rs1 ) && count ( $rs1 ) > 1) {
                    $dc->put ( $str, 3600*24 ); // 取到正确的数据缓存久一点
                    $rs  = $str;
                }else{
                    $str = g::file_get_contents ( $url."?c=del" );
                    $rs1 = @explode ( ',', $str );
                    //$rs1 = @explode ( ',', g::file_get_contents ( $url."?c=del" ) ); // 正常取不到3/19/2011 清缓存的方式取一次
    
                    if (is_array ( $rs1 ) && count ( $rs1 ) > 1) {
                        $dc->put ( $str, 3600*24 ); // 取到正确的数据缓存久一点
                        $rs  = $str;
                    }
                }
                unset($rs1);
                $dc_timeset_key->put(1,600); // 这个是更新的开关  无论对错都记录曾经请求过 10分钟之后再更新吧
            }else{ // 未过期 数据又不合法 由他去吧 20分钟之后再尝试
                if(empty ( $rs )){
                    $rs = '';
                }
            }
        }else{
            @header('dd:cache');
        }
        //		echo "/* {$url}\n ";
        //		print_r($rs);
        //		echo "*/ ";
        return $rs;
    }
    
    
    
}
