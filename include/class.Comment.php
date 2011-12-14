<?php
/**
 * class Comment
 *
 * @author kim
 * @since 2011-11-28
 * @version 1.0.0
 *
 */
class Comment
{
    const STATUS_PENDING = '1';
    const STATUS_ACTIVED = '2';
    const STATUS_DELETED = '3';
    
    /**
     * the table name
     *
     * @var string
     */
    protected $_tableName = 'comment';
    
    /**
     * database adapter
     *
     * @var mixed
     */
    protected $_dbAdapter = null;

    /**
     * @return string $_tableName
     */
    public function getTableName ()
    {
        return $this->_tableName;
    }

	/**
     * @param string $_tableName
     */
    public function setTableName ($_tableName)
    {
        $this->_tableName = $_tableName;
    }

	/**
     * @return mixed $_dbAdapter
     */
    public function getDbAdapter ()
    {
        return $this->_dbAdapter;
    }

	/**
     * @param mixed $_dbAdapter
     */
    public function setDbAdapter ($_dbAdapter)
    {
        $this->_dbAdapter = $_dbAdapter;
    }

	/**
     * construct
     *
     * @param object|mixed $dbAdapter
     * @return void
     */
    public function __construct($dbAdapter)
    {
        if (!$this->_tableName) {
            throw new exception("Please set the table name!");
        }
        
        if (is_object($dbAdapter)) {
            $this->setDbAdapter($dbAdapter);
        } else {
            throw new exception("Please provide database adapter!");
        }
    }

    /**
     * save new comment
     * 
     * @param array $data
     */
    public function saveNew($data)
    {
        if (!empty($data['content'])) {
            $ip = $this->_getUserIp();
            $info = array(
                'vid' => $data['vid'],
                'pct' => $data['pct'],
                'v_userid' => $data['v_userid'],
                'comment_userid' => $data['comment_userid'],
                'to_userid' => $data['to_userid'],
                'scoring' => 3,
                'nb_insist' => 0,
                'ip' => $ip,
                'locate' => $this->_getLocate($ip),
                'v_name' => $data['v_name'],
                'content' => $data['content'],
                'status' => self::STATUS_PENDING,
                'created_at' => @date('Y-m-d H:i:s'),
                'comment_ref' => array(),
            );
            //var_dump($info);die;
        }
        $this->getDbAdapter()->insert($this->getTableName(), $info);
    }

    /**
     * save reply
     *
     * @param array $data
     */
    public function saveReply($data)
    {
        if (!empty($data['content']) and !empty($data['comment_id'])) {
            $commentId = $data['comment_id'] ? : false;
            if ($commentId) {
                $comment = $this->getDbAdapter()->findOne(
                    $this->getTableName(), array('_id' => $commentId)
                );
                $ip = $this->_getUserIp();
                $info = array(
                    'vid' => $comment['vid'],
                    'pct' => $comment['pct'],
                    'v_userid' => $comment['v_userid'],
                    'comment_userid' => 'xqpmjh' . rand(2, 2222),
                    'to_userid' => $comment['comment_userid'],
                    'scoring' => 3,
                    'nb_insist' => 0,
                    'ip' => $ip,
                    'locate' => $this->_getLocate($ip),
                    'v_name' => $comment['v_name'],
                    'content' => $data['content'],
                    'status' => self::STATUS_PENDING,
                    'created_at' => @date('Y-m-d H:i:s'),
                );
                // add ref comment
                $info['comment_ref'] = $this->getDbAdapter()->createRef(
                    $this->getTableName(), $commentId
                );
                //echo '<pre>'; var_dump($info);echo '</pre>';die;
            }
            $this->getDbAdapter()->insert('comment', $info);
        }
    }

    /**
     * find one comment by id
     *
     * @param string $id - the document id
     * @return mixed
     */
    public function findOne($id)
    {
        $result = $this->getDbAdapter()->findOne(
            $this->getTableName(),
            array('_id' => $commentId)
        );
        return $result;
    }

    /**
     * find all comments and their replies list
     *
     * @see _getRefComments()
     * @param int $page
     * @param int $limit
     * @return array - the comments list
     */
    public function findAll($page = 1, $limit = 10)
    {
        $result = array();
        $commentList = $this->getDbAdapter()->findAll(
            $this->getTableName(),
            array('status' => array('$ne' => self::STATUS_DELETED)),
            array(
                'limit' => $limit,
                'skip' => ($page - 1) * $limit,
                'sort' => array('created_at' => -1),
            )
        );
        foreach ($commentList as $comment) {
            $comment = $this->_getRefComments($comment);
            $result[] = $comment;
        }
        return $result;
    }

    /**
     * get reference comments list
     * 
     * 'comment_ref_ins' - store the instance of another comment
     * the key of recursive comments chain
     * 
     * @param array $comment
     * @return array - the comment with replies
     */
    protected function _getRefComments($comment)
    {
        $comment['comment_ref_ins'] = array();
        if (!empty($comment['comment_ref']) and
            isset($comment['comment_ref'])) {
            $commentRef = $this->getDbAdapter()->getDbRef(
                'comment', $comment['comment_ref']
            );
            $comment['comment_ref_ins'] = $this->_getRefComments($commentRef);
        }
        return $comment;
    }

    /**
     * delete the comment - not really delete but just set status = DELETED
     * 
     * @param string $id
     */
    public function delete($id)
    {
        $result = $this->getDbAdapter()->save(
            $this->getTableName(),
            array('_id' => $id),
            array('status' => self::STATUS_DELETED)
        );
        return $result;
    }

    /**
     * increase the number of insist on
     * 
     * @param string $id
     * @return bool
     */
    public function insist($id)
    {
        $result = $this->getDbAdapter()->update(
            $this->getTableName(),
            array('_id' => $id),
            array('$inc' => array("nb_insist" => 1))
        );
        return $result;
    }
    
    /**
     * fetch all comments count number
     * 
     * @return int
     */
    public function total()
    {
        $result = (int)$this->getDbAdapter()->count(
            $this->getTableName(),
            array('status' => array('$ne' => self::STATUS_DELETED))
        );
        return $result;
    }

    /**
     * drop the comment collection
     *
     * @return boolean
     */
    public function drop()
    {
        $collectionName = $this->getTableName();
        return $this->getDbAdapter()->drop($collectionName);
    }
    
    /**
     * get current visitor's ip address
     *
     * @return string - ip string
     */
    protected function _getUserIp()
    {
        // only for testing
        return $this->_randomip();
        
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    /**
     * get a random ip4 address
     * only for testing!!
     * 
     * @return string
     */
    protected function _randomip()
    {
        $ipAddrs[] = '58';
        for ($a = 0; $a < 3; $a++) {
            $ipAddrs[] = mt_rand(33, 255);
        }
        $randomIp = implode('.', $ipAddrs);
        return $randomIp;
    }

    /**
     * get user location by ip
     */
    public function _getLocate($ip)
    {
        $addr = $this->_convertip($ip, PATH_QQWRY);
        return $addr;
    }
    
    /**
     * convert ip address
     */
    protected function _convertip($ip, $ipdatafile, $onlyCity = true)
    {
        if(!$fd = @fopen($ipdatafile, 'rb')) {
            return '- Invalid IP data file';
        }

        $ip = explode('.', $ip);
        $ipNum = $ip[0] * 16777216 + $ip[1] * 65536 + $ip[2] * 256 + $ip[3];
    
        if(!($DataBegin = fread($fd, 4)) || !($DataEnd = fread($fd, 4)) ) return;
        @$ipbegin = implode('', unpack('L', $DataBegin));
        if($ipbegin < 0) $ipbegin += pow(2, 32);
        @$ipend = implode('', unpack('L', $DataEnd));
        if($ipend < 0) $ipend += pow(2, 32);
        $ipAllNum = ($ipend - $ipbegin) / 7 + 1;
    
        $BeginNum = $ip2num = $ip1num = 0;
        $ipAddr1 = $ipAddr2 = '';
        $EndNum = $ipAllNum;
    
        while($ip1num > $ipNum || $ip2num < $ipNum) {
            $Middle= intval(($EndNum + $BeginNum) / 2);
    
            fseek($fd, $ipbegin + 7 * $Middle);
            $ipData1 = fread($fd, 4);
            if(strlen($ipData1) < 4) {
                fclose($fd);
                return '- System Error';
            }
            $ip1num = implode('', unpack('L', $ipData1));
            if($ip1num < 0) $ip1num += pow(2, 32);
    
            if($ip1num > $ipNum) {
                $EndNum = $Middle;
                continue;
            }
    
            $DataSeek = fread($fd, 3);
            if(strlen($DataSeek) < 3) {
                fclose($fd);
                return '- System Error';
            }
            $DataSeek = implode('', unpack('L', $DataSeek.chr(0)));
            fseek($fd, $DataSeek);
            $ipData2 = fread($fd, 4);
            if(strlen($ipData2) < 4) {
                fclose($fd);
                return '- System Error';
            }
            $ip2num = implode('', unpack('L', $ipData2));
            if($ip2num < 0) $ip2num += pow(2, 32);
    
            if($ip2num < $ipNum) {
                if($Middle == $BeginNum) {
                    fclose($fd);
                    return '- Unknown';
                }
                $BeginNum = $Middle;
            }
        }
    
        $ipFlag = fread($fd, 1);
        if($ipFlag == chr(1)) {
            $ipSeek = fread($fd, 3);
            if(strlen($ipSeek) < 3) {
                fclose($fd);
                return '- System Error';
            }
            $ipSeek = implode('', unpack('L', $ipSeek.chr(0)));
            fseek($fd, $ipSeek);
            $ipFlag = fread($fd, 1);
        }
    
        if($ipFlag == chr(2)) {
            $AddrSeek = fread($fd, 3);
            if(strlen($AddrSeek) < 3) {
                fclose($fd);
                return '- System Error';
            }
            $ipFlag = fread($fd, 1);
            if($ipFlag == chr(2)) {
                $AddrSeek2 = fread($fd, 3);
                if(strlen($AddrSeek2) < 3) {
                    fclose($fd);
                    return '- System Error';
                }
                $AddrSeek2 = implode('', unpack('L', $AddrSeek2.chr(0)));
                fseek($fd, $AddrSeek2);
            } else {
                fseek($fd, -1, SEEK_CUR);
            }
    
            while(($char = fread($fd, 1)) != chr(0))
                $ipAddr2 .= $char;
    
            $AddrSeek = implode('', unpack('L', $AddrSeek.chr(0)));
            fseek($fd, $AddrSeek);
    
            while(($char = fread($fd, 1)) != chr(0))
                $ipAddr1 .= $char;
        } else {
            fseek($fd, -1, SEEK_CUR);
            while(($char = fread($fd, 1)) != chr(0))
                $ipAddr1 .= $char;
    
            $ipFlag = fread($fd, 1);
            if($ipFlag == chr(2)) {
                $AddrSeek2 = fread($fd, 3);
                if(strlen($AddrSeek2) < 3) {
                    fclose($fd);
                    return '- System Error';
                }
                $AddrSeek2 = implode('', unpack('L', $AddrSeek2.chr(0)));
                fseek($fd, $AddrSeek2);
            } else {
                fseek($fd, -1, SEEK_CUR);
            }
            while(($char = fread($fd, 1)) != chr(0))
                $ipAddr2 .= $char;
        }
        fclose($fd);
    
        if ($onlyCity) {
            $result = $ipAddr1;
        } else {
            if(preg_match('/http/i', $ipAddr2)) {
                $ipAddr2 = '';
            }
            $ipaddr = "$ipAddr1 $ipAddr2";
            $ipaddr = preg_replace('/CZ88\.NET/is', '', $ipaddr);
            $ipaddr = preg_replace('/^\s*/is', '', $ipaddr);
            $ipaddr = preg_replace('/\s*$/is', '', $ipaddr);
            if(preg_match('/http/i', $ipaddr) || $ipaddr == '') {
                $ipaddr = '- Unknown';
            }
        
            $result = '- '.$ipaddr;
        }
        
        return mb_convert_encoding($result, "utf-8", "gb2312");
    }
    
    
}

