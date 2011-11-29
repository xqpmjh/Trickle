<?php
/**
 * @author kim
 * @since 2011-11-28
 * @version 1.0.0
 *
 */
class MongoAdapter
{
    /**
     * connection object
     * 
     * @var Mongo
     */
    protected $_connection = null;

    /**
     * database configurations
     * 
     * @var $_config 
     */
    protected $_config = array(
        'host'        => '127.0.0.1',
        'port'        => '27017',
        'database'    => null,
        'username'    => null,
        'password'    => null,
    );

    /**
     * @return Mongo $_connection
     */
    public function getConnection ()
    {
        return $this->_connection;
    }

	/**
     * @param Mongo $_connection
     */
    public function setConnection ($_connection)
    {
        $this->_connection = $_connection;
    }

	/**
     * @return array $_config
     */
    public function getConfig ()
    {
        return $this->_config;
    }

	/**
     * @param array $_config
     */
    public function setConfig ($_config)
    {
        $this->_config = $_config;
    }

    /**
     * do connect
     * 
     * @return Mongo
     */
    protected function _connect()
    {
        if (!$this->_connection) {
            $config = $this->getConfig();
            if (!empty($config) and is_array($config)) {
                $connectInfo = '';
                if (isset($config['host']) and !empty($config['host'])) {
                    $connectInfo .= (string)$config['host'];
                } else {
                    throw new MongoException("Host missing!");
                }

                if (isset($config['port']) and !empty($config['port'])) {
                    $connectInfo .= ':' . (string)$config['port'];
                }
                
                $conn = new Mongo($connectInfo);
                if (isset($config['username']) and isset($config['password'])) {
                    $conn->authenticate($username, $password);
                }

                if (isset($config['database'])) {
                    $conn->selectDB((string)$config['database']);
                } else {
                    throw new MongoException("Please select a database!");
                }
                
                $this->setConnection($conn);
            } else {
                throw new MongoException("Can not get database configurations!");            
            }
        }
        return $this->_connection;
    }

    /**
     * disconnect
     */
    protected function _disconnect()
    {
        $conn = $this->getConnection();
        if ($conn instanceof Mongo) {
            $conn->close();
        }
    }

	/**
	 * construct
	 * 
	 * @param array $config
	 */
	public function __construct($config)
    {
        if (!empty($config) and is_array($config)) {
            $this->setConfig($config);
        } else {
            throw new MongoException("Pleae provide connection configurations!");
        }
    }

    /**
     * get collection object
     * 
     * @return MongoCollection
     */
    protected function _getCollection($collectionName)
    {
        $conn = $this->_connect();
        if (!empty($collectionName)) {
            $collection = $conn->selectCollection($conn, $collectionName);
            if ($collection instanceof MongoCollection) {
                return $collection;
            } else {
                throw new MongoConnectionException("Unknow collection!");                
            }
        } else {
            throw new MongoException("Collection name missing!");
        }
    }
    
    /**
     * do insert
     * 
     * @param string $collectionName
     * @param array $data
     * @param boolean $safeInsert
     */
    public function insert($data, $collectionName, $safeInsert = true)
    {
        $collection = $this->_getCollection($collectionName);
        $collection->insert($data, $safeInsert);
    }
    
    /**
     * find all the records
     * 
     * @param string $collectionName
     * @return array
     */
    public function findAll($collectionName)
    {
        $collection = $this->_getCollection($collectionName);
        $result = array();
        foreach ($collection->find() as $row) {
            $result[] = $row;
        }
        return $result;
    }
    
    /**
     * count number of collections
     * 
     * @param string $collectionName
     * @return integer
     */
    public function count($collectionName)
    {
        $collection = $this->_getCollection($collectionName);
        $result = $collection->count();
        return $result;
    }

    /**
     * drop the collection
     * 
     * @param string $collectionName
     * @return void
     */
    public function drop($collectionName)
    {
        $collection = $this->_getCollection($collectionName);
        $collection->drop();        
    }
    
    /**
     * free resultset and connection
     * 
     * @return void
     */
    public function free()
    {
        $this->_disconnect();
        $this->_connection = null;
    }
    
} 

