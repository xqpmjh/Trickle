<?php
/*
 * ���ú����
 * ����:2006.10.10
 * ����:��һƽ
 */
class g
{
	/*
	 * ����:�����Ի���
	 * ����:$msg		��ʾ��Ϣ
	 * 		$script	ͷβ�Ƿ����script 1:�� 0:����
	 * ����:string
	 */
	static public function alert($msg, $script = 1)
	{
		$search  = array('\\', "\n", "\t", "\r", "\b", "\f", "'");
        $replace = array('\\\\', '\\n', '\\t', '\\r', '\\b', '\\f', "\'");
        $msg  	 = str_replace($search, $replace, $msg);
        $msg  	 = str_replace(array(chr(0x08), chr(0x0C)), array('\b', '\f'), $msg);
		$rs 	 = "alert('" . $msg . "');\n";
		if($script)
		{
			$rs = "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n<script type=\"text/javascript\">\n" . $rs . "\n</script>";
		}
		return $rs;
	}
	static public function msg($msg,$script = 1)
	{
		return self :: alert($msg,$script); 
	}
	/*
	 * ����:�õ��ļ���
	 * ����:$file		�ļ�·��
	 * 		$ex			��չ�� 0:������չ�� 1:����չ�� 2:��������
	 * ����:string �ļ���
	 */
	static public function basename($file,$ex=0)
	{
		$rs 			= pathinfo($file);
		$rs['name_ex'] = $rs['basename'];
		if(strstr(substr($rs['basename'],-8),'.'))
		{
			$rs['ex'] 	= $rs['extension'];
		}else
		{
			$rs['ex'] 	= '';
		}
		$rs['name']   	=$rs['ex']?substr($rs["basename"],0,strlen($rs["basename"]) - (strlen($rs["ex"]) + 1) ):$rs["basename"];
		if(empty($ex))
		{	//���� ������չ��
			return $rs['name'];
		}else if($ex == 1)
		{	//���� ����չ��
			return $rs['name_ex'];
		}else
		{	//�������� 
			$rs['dir'] 	= dirname($file).'/';
			return $rs;
		}
	}
	/*
	 * ����:��URL�е�domain
	 */
	static public function replaceDomain($url,$domain)
	{
		$parse = parse_url($url);
		return str_replace($parse['host'],$domain,$url);
	}
	
	/*
	 * ����:�õ�flvIMG
	 * ����:$rsArray
	 * ����:string URL
	 */
	static public function flvImg($rsArray)
	{
		global $scfg;
		if($rsArray['exercise'] == 'p')
		{
			$imageURL = $rsArray['URL_host'];
		}else if($rsArray['exercise'] == 'y')
		{
			$imageURL = "img/mp3.gif";
		}else
		{
			if(substr($rsArray['URL_host'],0,7) == 'http://')
			{
				$imageURL = $rsArray['URL_host'];
			}else
			{
//				if (in_array($rsArray['img_host'],$scfg['config']['img_cached_host'] )) $rsArray['img_host'] = $rsArray['img_host'];
				preg_match("/v(\d+)\.56\.com/i",$rsArray['img_host'],$pattern);
				if((int)$pattern[1]>16){
					$rsArray['img_host'] = 'img.'.$rsArray['img_host'];
				}
				$imageURL = 'http://'.$rsArray['img_host'].'/images/'.$rsArray['URL_pURL']."/".$rsArray['URL_sURL']."/".$rsArray['user_id'].$scfg['config']['com_jpg_id'].$rsArray['URL_URLid'].".jpg";
				
			}
		}
		return $imageURL;
	}
	/*
	 * ����:�õ�?
	 * ����:string URL
	 */
	static public function getVars($rsArray)
	{
	   return "img_host=".$rsArray['img_host']."&host=".$rsArray['URL_host']."&pURL=".$rsArray['URL_pURL']."&sURL=".$rsArray['URL_sURL']."&user=".$rsArray['user_id']."&URLid=".$rsArray['URL_URLid']."&totaltimes=".$rsArray['totaltime'].((strlen($rsArray['effectID'])>3)?($rsArray['effectID']):"&effectID=".$rsArray['effectID'])."&flvid=".$rsArray['id'];
	}
	/*
	 * ����:���û�Ŀ¼ 
	 */
	static public function userDir($user_id,$c=30)
	{
		$a1 = 0;
		$a2 = 0;	
		for ($i = 0; $i<strlen($user_id); $i++)
		{
			$a1 += (ord($user_id{$i}))*$i;  //a charCodeAt(a)
			$a2 += (ord($user_id{$i}))*($i*2+1);   
		}
		$a1 %= $c;	//��һ��·��
		$a2 %= $c;	//�ڶ���·��
		return array('URL_pURL'=>$a1,'URL_sURL'=>$a2,'p'=>$a1,'s'=>$a2);
	}
	/*
	 * ����:�õ�flvURL
	 * ����:$id			FLVID
	 * 		$product	վ�㻹��space
	 * ����:string URL
	 */
	static public function flvUrl($id,$pct=1,$site=true)
	{
		$host 	= self::phost($id,$pct);
		return $site
				?$host."/v_" . self::flvEnId($id).".html"
				:$host."/spaceDisplay.php?id=" . self::flvEnId($id);
	}
	/*
	 * ���ܣ�ȡ���û����URL
	 * ����$user_id �û�ID
	 * 		$product ��Ʒ
	 * 		$site   վ�㻹�� space Ĭ��Ϊվ��
	 * ���أ�string URL
	 */
	static public function userUrl($user_id,$product = 'v',$site = 0)
	{
		$t 		= (preg_match("/(\.)+$/",$user_id))?0:1;
		$host 	= self::phost($user_id);
		switch($product)
		{
			case 'v';
				//*/
				$url = empty($site)
							? ($t
							 	?'http://'.$user_id.'.v.56.com/'
								:$host.'/u_'.$user_id.'.html'
							 )
							//? $host.'/show_user.php?user_id='.$user_id
							: $host.'/spaceListUser.php?user_id='.$user_id;												 
				/*/
				$url = (empty($site)
							?($t
							 	?'http://'.$user_id.'.v.56.com/'
								:'http://v2.56.com/u_'.$user_id.'.html'
							 )
							:(($site != 'space')
								?'http://v2.56.com/u_'.$user_id.'.html'
								:'http://v2.56.com/spaceListUser.php?user_id='.$user_id							
							  )					 
						);
				//*/
			break;
			case 'space';
				$url = ($t
							?'http://'.$user_id.'.56.com/'
							:'http://space.56.com/'.$user_id
						);
			break;
			case 'k';
				$url = (empty($site)
							?($t
							 	?'http://'.$user_id.'.k.56.com/'
								:'http://k.56.com/s_ListUser.php?user_id='.$user_id
							 )
							:(($site != 'space')
								?'http://k.56.com/s_ListUser.php?user_id='.$user_id
								:'http://k.56.com/spaceListUser.php?user_id='.$user_id						
							  )					 
						);
			break;
			case 'photo';
				$url = (empty($site)
							?($t
							 	?'http://'.$user_id.'.photo.56.com/'
								:'http://photo.56.com/myphoto.php?u='.$user_id
							 )
							:'http://photo.56.com/myphoto.php?u='.$user_id				 
						);
			break;
			case 'tuya';
				$url = 'http://tuya.56.com/mylist.php?u='.$user_id;
			break;
			case 'tt';
				$url = (empty($site)
							?($t
							 	?'http://'.$user_id.'.tt.56.com/'
								:'http://tt.56.com/spaceListUser.php?user_id='.$user_id
							 )
							:'http://tt.56.com/spaceListUser.php?user_id='.$user_id			 
						);
			break;
			case 'blog';
				$url = (empty($site)
							?($t
							 	?'http://'.$user_id.'.blog.56.com/'
								:'http://blog.56.com/?u='.$user_id
							 )
							:'http://blog.56.com/?u='.$user_id		 
						);
				return 'http://blog.56.com/?u='.$user_id;
			break;
		}
		return $url;
	}
	/*
	 * ����: ����flvid
	 * ����: $id	FLVID
	 * ����: string BASE64
	 */
	static public function flvEnId($id)
	{
		if(is_numeric($id))
		{
			return str_replace('=', '', base64_encode($id));
		}else
		{
			return $id;
		}
	}
	/*
	 * ���ܣ�PHP�Ӻ���JSȥ��
	 */
	static public function phpEnCode($str)
	{
		$key ='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		$h   =$key[mt_rand(0,strlen($key))];
		$h_l =ord($h);
		$b1_64 = base64_encode($str);
		$t     = substr($b1_64,mt_rand(0,strlen($b1_64)-$h_l),$h_l);
		return $h.base64_encode($t.$h.$b1_64);
	}
	/*
	 * ����: ����flvid
	 * ����: string BASE64
	 * ����: $id    FLVID
	 */
	static public function flvDeId($id)
	{		
		if(is_numeric($id))
		{
			return $id;
		}else
		{			
			return (int)base64_decode($id);
		}
	}
	/*
	 * ����:��������������EnId
	 */
	static public function addEnId(&$data,$pct=0)
	{
		if($data['id']) 
		{
			$data['pct'] = $pct
							?$pct
							:(
								$data['from'] == 'p' 
									? 3 
									: ($data['exercise'] == 'l' ? 2 : 1)
							  );
			$data['EnId'] = self::flvEnId($data['id']);
		}else
		{
			foreach($data as &$v)
			{
				$v['pct'] = $pct
							  ?$pct
							  :(
							  	$v['from'] == 'p' 
									? 3 
									: ($v['exercise'] == 'l' ? 2 : 1)
								);
				$v['EnId'] = self::flvEnId($v['id']);
			}
		}
		return $data;
	}
	
	/*
	 * ����:���������������û��س�
	 */
	static public function addUserName(&$data)
	{
		if($data['id']) 
		{
			$data['user_name'] = u::info($data['user_id'],'name');
		}else
		{
			foreach($data as &$v)
			{
				$v['user_name'] = u::info($v['user_id'],'name');
			}
		}
		return $data;
	}
	
	/*
	 * ����:��������������������
	 */
	static public function addReview(&$data)
	{
		global $scfg;
		$db = new db($scfg['review_db']);
		if($data['id']) 
		{
			$tmp =$db->rsArray('select count(*) as review from flv_comment_on where date_id=?',$data['id']);
			$data['review'] =  $tmp['review'];
		}else
		{
			foreach($data as &$v)
			{
				$tmp =$db->rsArray('select count(*) as review from flv_comment_on where date_id=?',$v['id']);
				$v['review'] = $tmp['review'];
			}
		}
		return $data;
	}
	/*
	 * ����:���������������û��س�
	 */
	static public function formatTag(&$data)
	{
		if($data['id']) 
		{
			$tmp =array();
			for($i=0;$i<5;$i++)
			{
				if($data['tags'.$i])$tmp[] = $data['tags'.$i]; 
			}
			$data['tag'] = implode(',',$tmp);
		}else
		{
			foreach($data as &$v)
			{
				$tmp =array();
				for($i=0;$i<5;$i++)
				{
					if($v['tags'.$i])$tmp[] = $v['tags'.$i]; 
				}
				$v['tag'] = implode(',',$tmp);
			}
		}
		return $data;
	}
		/*
	 * ����:��url�õ�ID
	 */
	static public function getUrlId($url)
	{
		if(!strstr($url,'http'))
		{
			$id =self::flvDeId($url);
		}else
		{
			if(strstr($url,'v='))
			{
				$id  =explode('v=',trim($url));
				$id  =str_replace('.html','',$id[1]);
				$id  = self::flvDeId($id);
			}elseif(strstr($url,'v_'))
			{
				$id  =explode('v_',trim($url));
				$id  =str_replace('.html','',$id[1]);
				$id  = self::flvDeId($id);
			}elseif(strstr($url,'.html'))
			{
				$id  =explode('/id',trim($url));
				$id  =str_replace('.html','',$id[1]);
			}else
			{
				$id  =explode('id=',trim($url));
				$id  =explode('&',$id[1]);
				$id  =$id[0];
			}
		}
		return $id;
	}
	
	static public function getTypeId($url)
	{
		if(strstr($url,'/p'))
		{
			return 2;
		
		}
		else if(strstr($url,'/l'))
		{
			return 3;	
		}
		else
		{
			return 1;
		}
	}
	/*
	 * ����:��cookie
	 */
	static public function setcookie($name,$vars,$hour=1)
	{
		setcookie($name, $vars, time()+3600*$hour, '/', '56.com');
	}
	/*
	 * ����:д�ļ�
	 * ����:$fileName	�ļ���
	 * 		$content	����
	 * ����:�ɹ�:true ʧ��:false 
	 */
	static public function write($fileName, $content, $type = "w")
	{
		$fd = fopen($fileName, $type);
		if ($fd)
		{
			fwrite($fd, $content);
			fclose($fd);
			return true;
		} else
		{
			return false;
		}
	}
	/*
	* ����:���Ŀ¼�Ƿ����,û���½�
	* ����:$dir 	Ŀ¼���·��
	* ����:ture:�ɹ� flase:ʧ�� 
	*/
	static public function dir($dir,$recursive=false)
	{
		if (!file_exists($dir))
		{
			@ mkdir($dir, 0777,$recursive);
			if (file_exists($dir))
			{
				return true;
			} else
			{
				return false;
			}
		}else 
		{
			return true;
		}
	}
	/*
	* ����:ɾ��һ��Ŀ¼
	* ����:$dir 	Ŀ¼���·��
	* ����:ture:�ɹ� flase:ʧ�� 
	*/
	static public function rmdir($path)
	{
		if (!is_dir($path)) return false;
		$handle=@opendir($path);
		while($val=@readdir($handle))
		{
			if ($val=='.' || $val=='..') continue;
			$value=$path."/".$val;
			if (is_dir($value))
			{
				self::rmdir($value);
			}else if (is_file($value))
			{
				unlink($value);
			}
		}
		@closedir($handle);
		rmdir($path);
		return true;
	}
	static public function deldir($path)
	{
		return self::rmdir($path);
	}
	/*
	 * ����:ҳ����ת
	 * ����:$link		��תURL
	 * 		$top		�Ƿ�top
	 * ����:�� 
	 */
	static public function gowin($link, $top = '',$note='') //err
	{
		if ($top || $note)
		{
			$replace = "\t".($top?"window.top.":"")."location.href='" . $link . "';\n";
			echo "<script type=\"text/javascript\">\n";
			echo $note?"if(window.confirm('".$note."')){\n".$replace." }":$replace;
			echo "</script>\n";
		} else
		{
			if (headers_sent())
			{
				echo "<meta http-equiv=\"refresh\" content=\"0;url=" . $link . "\">\r\n";
			} else
			{
				header("Location: " . $link);
			}
		}
		exit();
	}
	/*
	 * ����:���ʼ�
	 */
	static public function file($file)
	{	
		global $db;
		$sql = array(
			'file' 		=> $file,
			'save_time'	=> time()
		);
		$db->insert('waiting_del_files',$sql);
	}
	/*
	 * ����:���Ľظ�
	 */
	static public function substr($string, $sublen = 20)
	{
		if ($sublen >= strlen($string))
		{
			return $string;
		}
		for ($i = 0; $i < $sublen -2; $i++)
		{
			if (ord($string {
				$i }) < 127)
			{
				$s .= $string {
					$i };
				continue;
			} else
			{
				if ($i < $sublen -3)
				{
					$s .= $string {
						$i }
					 . $string {
						++ $i };
					continue;
				}
			}
		}
		return $s . '..';
	}
	static public function ip()
	{
		if ($_REQUEST['HTTP_CLIENT_SELF_IP']){
			$hdr_ip = preg_replace("/[^.\d]*/",'',$_REQUEST['HTTP_CLIENT_SELF_IP']);
		}else{
			if (isset($_SERVER['HTTP_CLIENT_IP'])) 
			{
	    		$hdr_ip = stripslashes($_SERVER['HTTP_CLIENT_IP']);
			} else 
			{
				if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) 
				{
					$hdr_ip = stripslashes($_SERVER['HTTP_X_FORWARDED_FOR']);
				} else 
				{
					$hdr_ip = stripslashes($_SERVER['REMOTE_ADDR']);
				}
			}
		}
		return $hdr_ip;
	}
	/*
	 * ����:ת��ʱ��
	 * ����:$t		1970�������
	 * 		$a		1:����,2:���� ʱ��
	 * ����:�� 
	 */
	static public function time($t, $a = 2)
	{
		if ($a < 2)
		{
			return date("Y-m-d", $t);
		} else
		{
			return date("Y-m-d H:i:s", $t);
		}
	}
	/*
	 * ����:���ʼ�
	 */
	static public function mail($email,$subject,$msg,$sender_email='������ͷ���<cs@corp.56.com>')
	{	
		//$headers = "From: $sender_email\nContent-Type: text/plain; charset=gb2312\nContent-Transfer-Encoding: 8bit";
		$headers = "From: ".$sender_email;
		//$config = $sender_email;
		mail($email,$subject,$msg,$headers);
	}
	/*
	* ����:�ֽ�ת��
	* ����:$size 	�ֽ���
	* ����:��Ӧ��λ 
	*/
	static public function size($size)
	{
		if ($size < 1024)
		{
			return round($size, 2) . 'Bit';
		}elseif ($size < 1024 * 1024 )
		{
			$size = $size / 1024;
			return round($size, 2) . 'K';
		}elseif ($size < 1024 * 1024 * 1024 )
		{
			$size = $size / 1024 / 1024;
			return round($size, 2) . 'M';
		}elseif ($size < 1024 * 1024 * 1024 * 1024 )
		{
			$size = $size / 1024 / 1024 / 1024;
			return round($size, 2) . 'G';
		}elseif ($size < 1024 * 1024 * 1024 * 1024 * 1204)
		{
			$size = $size / 1024 / 1024 / 1024 /1024;
			return round($size, 2) . 'T';
		}else
		{
			$size = $size / 1024 / 1024 / 1024 / 1024 /1024;
			return round($size, 2) . 'P';
		}
	}
	/*
	 * �õ�����
	 * 	$pct ��Ʒid û�в�ƷIDʱȡ�û�
	 */
	static public function phost(&$str,$pct=false)
	{
		if($pct===false)
		{
			$len = strlen($str);
			$rs  = 0;
			for($i=0;$i<$len;$i++)$rs +=ord($str[$i]);
			$host 	= "http://www.56.com/w".($rs%88+11);
		}else
		{
			$id=self::flvDeId($str);
			$pct    = self::pct($pct);
			$host 	= "http://www.56.com/$pct".($id%88+11);
		}
		return $host;
	}
	/*
	 * ��Ʒ
	 * 	$mode = id������ID string
	 * 	$mode = name������name  string
	 * 	$mode = id,name||�����id��name��Array
	 */
	static public function pct($pct,$mode='id')
	{
		$pctArray = array(1=>'u',2=>'l',3=>'p');
		$pctName  = array('u'=>'�ϴ�','l'=>'¼��','p'=>'�����Ƶ');
		if(is_numeric($pct))$pct = $pctArray[$pct];
		
		if($mode == 'id')
		{
			return $pct;
		}else if($mode == 'name')
		{
			return $pctName[$pct];
		}else
		{
			return array(
				'id'	=>$pct,
				'name'	=>$pctName[$pct]
			);
		}
	}
	/*
	 *  �첽  	
	 */
	static public function file_get_contents($url)
    {
        //return file_get_contents($url);
		$info= parse_url($url);
        $info['port']= $info['port'] ? $info['port'] : 80;
        $info['path'] = ltrim($info['path'],'/'); //Melon ȥ�����б��
        return self :: async_file_get_contents($info['host'], $info['path'] . ($info['query'] ? '?' . $info['query'] : ''), $info['port']);
    }
	static public function async_file_get_contents($site,$pos,$port=80,$timeout=5)
	{
		$fp = fsockopen($site, $port, $errno, $errstr, 5);
		
		if (!$fp) 
		{
			return false;		
		}else 
		{
			$stream = "GET /$pos HTTP/1.0\r\n";
			$stream .= "Host: $site\r\n";
			$stream .= "Connection: Close\r\n\r\n";
			fwrite($fp, $stream);
			usleep(10);
			stream_set_timeout($fp, $timeout);
			$res = stream_get_contents($fp);
			$info = stream_get_meta_data($fp);
			fclose($fp);
			if ($info['timed_out']) 
			{
				return false;    	
			}else 
			{
				return substr(strstr($res, "\r\n\r\n"),4);
			}
		}
	}
	/*
	 * format time length to 00:00:00
	 * $timeLength 90ms to 01:30
	 * add by jk 2007-04-23
	 */
	static public function formatTime($timeLenght){
		date_default_timezone_set('UTC');
		$t = date('H:i:s',ceil($timeLenght/1000));
		if (substr($t,0,2)=='00') $t = substr($t,3);
		return $t;
	}
	/*
	 * ת��
	 */
	static public function mb(&$string,$to_encoding="UTF-8",$from="GB2312")
	{
		return mb_convert_encoding($string, $to_encoding,$from);
	}
	
	/**
	 * ������Ƶ��������ַ
	 * @param string $player ѡ�����ֲ�������Ĭ��n������ǻ�� huodong ,�Զ��������� huodong2
	 * @return mixed string|false
	 */
	 static public  function swf_out_link($vars, $player='n'){
		if(is_array($vars)){
			return 'http://www.56.com/'.$player.'_' . str_replace(':88', '', str_replace('.56.com', '', $vars['img_host'])) .
					'_/' . str_replace('.56.com', '', $vars['URL_host']) .
					'_/' . $vars['URL_pURL'] .
					'_/' . $vars['URL_sURL'] .
					'_/' . $vars['user_id'] .
					'_/' . $vars['URL_URLid'] .
					'_/' . $vars['totaltime'] .
					'_/' . $vars['effectID'] .
					'_/' . $vars['id'] . ".swf";
		}
		return FALSE;
	}
	 
	/**
	   * @todo ͨ����Ƶid��ȡ��Ƶ��Ϣ 
	   * @author Melon`` @ 1010
	   */
	static public function getVideoInfo($ids,$fli='',$fle='',$dy=''){
		$data = Http::Get('info.v.56.com',"?ids={$ids}&fli={$fli}&fle={$fle}&dy={$dy}");
		if ($data){
			$data = json_decode($data,TRUE);
			return is_array($data) ? $data : array();
		}else{
			return array();
		}
	}
	
	/**
	 * �Ե�ַ���һ����Ƶ �����Ƶ��ר��
	 */
	static public function dis_video($url){
		if (empty($url)) {
			return false;
		}
		$sp = '/play_album|p[0-9]+|u[0-9]+/i';
		preg_match($sp,$url,$out);
		$sp = '/album/';
		preg_match($sp,$out['0'],$out2);
		if (preg_match($sp,$out['0'],$out2)){
			return $out2['0'];
		}else{
			$sp = '/[p|u]/';
			preg_match($sp,$out['0'],$out2);
			return $out2['0'];
		}
	}
	
	/**
	 * @todo ȡ�����Ƶ��Ϣ
	 * @param mixed $ids
	 * @param string $fli
	 * @author pengkl @2011-10
	 */
	static public function getPhotoAlbumVideoInfo($ids,$fli='Subject'){
		//http://p.56.com/API/vInfo.php?ids=MTA0ODcwMjg2,104478204&fli=id,times,Subject,URL_host
		$pos = sprintf('API/vInfo.php?ids=%s&fli=%s',$ids,$fli);
		$data = Http::Get('p.56.com', $pos,80,3);
		if ($data){
			$data = json_decode($data,TRUE);
			return is_array($data) ? $data : array();
		}else{
			return array();
		}
	}
}
