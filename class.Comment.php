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
     * the collection name
     * 
     * @var string
     */
    protected $_collectionName = 'comment';
    
    /**
     * database adapter
     *
     * @var MongoAdapter
     */
    protected $_dbAdapter = null;

    /**
     * @return string $_collectionName
     */
    public function getCollectionName ()
    {
        return $this->_collectionName;
    }

	/**
     * @param string $_collectionName
     */
    public function setCollectionName ($_collectionName)
    {
        $this->_collectionName = $_collectionName;
    }

	/**
     * @return MongoAdapter $_dbAdapter
     */
    public function getDbAdapter ()
    {
        return $this->_dbAdapter;
    }

	/**
     * @param MongoAdapter $_dbAdapter
     */
    public function setDbAdapter ($_dbAdapter)
    {
        $this->_dbAdapter = $_dbAdapter;
    }

	/**
     * construct
     *
     * @param object $dbAdapter
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
                $comment = $this->getDbAdapter()
                    ->findOne('comment', array('_id' => $commentId));
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
                //$info['comment_tower'] = $comment;
                
                // add ref comment
                $info['comment_ref'] = array(MongoDBRef::create(
                        "comment", $comment['_id']
                ));

                //echo '<pre>'; var_dump($info);echo '</pre>';
            }
            //die;
            $this->getDbAdapter()->insert('comment', $info);
        }
    }

    /**
     * find one comment by id
     *
     * @return array
     */
    public function findOne($id)
    {
        $result = $this->getDbAdapter()->findOne(
            $this->getCollectionName(),
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
        $commentList = $this->getDbAdapter()
                            ->findAll($this->getCollectionName());
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
            isset($comment['comment_ref'][0])) {
            $commentRef = $this->getDbAdapter()->getDbRef(
                'comment', $comment['comment_ref'][0]
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
    public function count()
    {
        $result = (int)$this->getDbAdapter()->count($this->getCollectionName());
        return $result;
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

