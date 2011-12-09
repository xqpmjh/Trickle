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
        if (is_object($dbAdapter)) {
            $this->setDbAdapter($dbAdapter);
        } else {
            throw new exception("Pleae provide database adapter!");
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
            $info = array(
                'vid' => $data['vid'],
                'pct' => $data['pct'],
                'v_userid' => $data['v_userid'],
                'comment_userid' => $data['comment_userid'],
                'to_userid' => $data['to_userid'],
                'scoring' => 3,
                'ip' => $this->_getUserIp(),
                'v_name' => $data['v_name'],
                'content' => $data['content'],
                'comment_ref' => array(),
                'created_at' => @date('Y-m-d H:i:s'),
            );
            //var_dump($info);die;
        }
        $this->getDbAdapter()->insert('comment', $info);
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
                    'comment', array('_id' => $commentId)
                );
                $info = array(
                    'vid' => $comment['vid'],
                    'pct' => $comment['pct'],
                    'v_userid' => $comment['v_userid'],
                    'comment_userid' => 'xqpmjh' . rand(2, 2222),
                    'to_userid' => $comment['comment_userid'],
                    'scoring' => 3,
                    'ip' => $this->_getUserIp(),
                    'v_name' => $comment['v_name'],
                    'content' => $data['content'],
                    'created_at' => @date('Y-m-d H:i:s'),
                );

                // add ref comment
                $info['comment_ref'] = $this->getDbAdapter()->createRef(
                        "comment", $commentId
                );

                //echo '<pre>'; var_dump($info);echo '</pre>';
            }
            //die;
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
     * @return array - the comments list
     */
    public function findAll()
    {
        $result = array();
        $commentList = $this->getDbAdapter()->findAll(
            $this->getTableName(),
            array('sort' => array('created_at' => -1))
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
     * fetch all comments count number
     * 
     * @return int
     */
    public function total()
    {
        $result = (int)$this->getDbAdapter()->count($this->getTableName());
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
    function _getUserIp()
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

}

