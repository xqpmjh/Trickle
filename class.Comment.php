<?php
/**
 * @name Comment
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
     * @var $_dbAdapter
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
            throw new MongoException("Pleae provide database adapter!");
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
                'comment_tower' => '',
                'created_at' => @date('Y-m-d H:i:s'),
                'scoring' => 3,
                'ip' => $this->_getUserIp(),
                'v_name' => $data['v_name'],
                'content' => $data['content'] . rand(1, 2000),
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
                $comment = $this->getDbAdapter()->findOne('comment', array('_id' => $commentId));
                $info = array(
                    'vid' => $comment['vid'],
                    'pct' => $comment['pct'],
                    'v_userid' => $comment['v_userid'],
                    'comment_userid' => 'xqpmjh' . rand(2, 2222), // should be member
                    'to_userid' => $comment['comment_userid'],
                    'created_at' => @date('Y-m-d H:i:s'),
                    'scoring' => 3,
                    'ip' => $this->_getUserIp(),
                    'v_name' => $comment['v_name'],
                );
                $info['content'] = $data['content'] . rand(1, 2000);
                $info['comment_tower'] = $comment;
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
     * find all comments
     * 
     * @return array
     */
    public function findAll()
    {
        return $this->getDbAdapter()->findAll($this->getCollectionName());
    }

    /**
     * fetch all comments count number
     * 
     * @return int
     */
    public function count()
    {
        return (int)$this->getDbAdapter()->count($this->getCollectionName());
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