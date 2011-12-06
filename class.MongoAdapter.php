<?php
/**
 * @see MongoCursorWrapper
 */
include_once 'class.MongoCursorWrapper.php';

/**
 * class MongoAdapter
 *
 * Simple Mongo operation wrapper
 *
 * @example
 * $config = array(
 *           'host' => 'localhost',
 *           'port' => '27017',
 *           'database' => 'test',
 *           'username' => 'kim',
 *           'password' => 'kim'
 * );
 * $mongo = new MongoAdapter($config);
 * $commentList = $mongo->findAll('comment');
 *
 * @author kim
 * @since 2011-11-28
 * @version 1.0.0
 *
 */
final class MongoAdapter
{
    /**
     * connection object
     * 
     * @see http://www.php.net/manual/en/class.mongo.php
     * @var Mongo - instance of Mongo
     */
    protected $_connection = null;

    /**
     * db object
     *
     * @see http://www.php.net/manual/en/class.mongodb.php
     * @var MongoDB - instance of MongoDB
     */
    protected $_db = null;

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
     * @return Mongo
     */
    public function getConnection()
    {
        return $this->_connection;
    }

	/**
     * @param Mongo $connection
     */
    public function setConnection($connection)
    {
        $this->_connection = $connection;
    }

    /**
     * @return MongoDB
     */
    public function getDb()
    {
        return $this->_db;
    }

    /**
     * @param MongoDB $db
     */
    public function setDb($db)
    {
        $this->_db = $db;
    }

	/**
     * @return array
     */
    public function getConfig()
    {
        return $this->_config;
    }

	/**
     * @param array $config
     */
    public function setConfig($config)
    {
        $this->_config = $config;
    }

    /**
     * construct
     * only set configurations
     * should not do any connectting until real querying
     *
     * @param array $config
     * @return void
     */
    public function __construct($config)
    {
        if (!empty($config) and is_array($config)) {
            $this->setConfig($config);
        } else {
            throw new MongoException("Pleae provide connection configs!");
        }
    }

    /**
     * do connect store the Mongo instance
     * then select the database and store MongoDB instance
     * just before the first query
     * 
     * @return MongoDB - return Mongo database object
     */
    protected function _connect()
    {
        if (!$this->_db) {
            $config = $this->getConfig();
            if (!empty($config) and is_array($config)) {

                // connection informations
                $connectInfo = '';
                if (isset($config['host']) and !empty($config['host'])) {
                    $connectInfo .= (string)$config['host'];
                } else {
                    throw new MongoException("Host missing!");
                }
                if (isset($config['port']) and !empty($config['port'])) {
                    $connectInfo .= ':' . (int)$config['port'];
                }

                // get connection
                try {
                    $conn = new Mongo($connectInfo);
                    $this->setConnection($conn);
                } catch (MongoConnectionException $e) {
                    throw new MongoConnectionException("Fails to connect!");
                }
                
                // get database
                $db = $this->_getDatabaseInstance($conn);

            } else {
                throw new MongoException("Can not get database configs!");            
            }
        }

        return $this->getDb();
    }

    /**
     * get database instance based on dbs list
     * do auth if db exists
     * 
     * @param Mongo $connection
     * @return MongoDB
     */
    protected function _getDatabaseInstance($connection)
    {
        $config = $this->getConfig();
        if (is_array($config) and isset($config['database'])) {
            if ($connection instanceof Mongo) {

                // get db name list
                $dbs = $connection->listDBs();
                if (is_array($dbs) and !empty($dbs['databases'])) {
                    foreach ($dbs['databases'] as $d) {
                        $dbnames[] = $d['name'];
                    }
                } else {
                    throw new MongoException("Database list empty!");
                }

                // check whether database already exists
                $dbname = (string)$config['database'];
                if (in_array($dbname, $dbnames)) {
                    $db = $connection->selectDB($dbname);
                    if (!$db) {
                        throw new MongoException("Invalid database!");
                    }
                } else {
                    throw new MongoException("Unfound database : " . $dbname);
                }

                // do auth
                if (isset($config['username']) && isset($config['password'])) {
                    $authResult = $db->authenticate(
                        (string)$config['username'],
                        (string)$config['password']
                    );
                    if (!$authResult['ok']) {
                        throw new MongoException("Invalid user/password!");
                    }
                }
                
                $this->setDb($db);
                return $db;
            } else {
                throw new MongoConnectionException("Invalid connection!");
            }
        } else {
            throw new MongoException("Database configuration unfound!");
        }
    }
    
    /**
     * get collection object
     * 
     * @param string $collectionName
     * @return MongoCollection
     */
    protected function _getCollection($collectionName)
    {
        $db = $this->_connect();
        if (!empty($collectionName)) {
            $collection = $db->selectCollection($collectionName);
            if ($collection instanceof MongoCollection) {
                return $collection;
            } else {
                throw new MongoException("Unknow collection!");                
            }
        } else {
            throw new MongoException("Collection name missing!");
        }
    }

    /**
     * disconnect
     *
     * @return void
     */
    protected function _disconnect()
    {
        $conn = $this->getConnection();
        if ($conn instanceof Mongo) {
            $conn->close();
        }
    }
    
    /**
     * do insert
     * 
     * @param array $data
     * @param string $collectionName
     * @param boolean $safeInsert
     * @return void
     */
    public function insert($collectionName, $data, $safeInsert = true)
    {
        $collection = $this->_getCollection($collectionName);
        $collection->insert($data, $safeInsert);
    }

    /**
     * find one record
     * 
     * @param string $collectionName
     * @param array $conditions
     * @return MongoCursorWrapper
     */
    public function findOne($collectionName, $conditions)
    {
        $collection = $this->_getCollection($collectionName);
        $where = $this->_buildQueries($conditions);
        $result = $collection->findOne($where);
        $result = new MongoCursorWrapper($result);
        return $result;
    }
    
    /**
     * find all the records
     * 
     * @param string $collectionName
     * @return array - array of MongoCursorWrapper
     */
    public function findAll($collectionName)
    {
        $collection = $this->_getCollection($collectionName);
        $result = array();
        foreach ($collection->find() as $row) {
            $result[] = new MongoCursorWrapper($row);
        }
        return $result;
    }

    /**
     * get reference document
     * 
     * @param array $ref - the reference object
     * @return array
     */
    public function getDbRef($collectionName, $ref)
    {
        $result = array();
        if (!empty($ref) and MongoDBRef::isRef($ref)) {
            $collection = $this->_getCollection($collectionName);
            $result = $collection->getDBRef($ref);
            $result = new MongoCursorWrapper($result);
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
     * build queries by conditions array provided
     * results are in mongo format
     * 
     * @param array $conditions
     * @return array $where - queries in mongo format
     */
    protected function _buildQueries($conditions)
    {
        $where = array();
        if (!empty($conditions) and is_array($conditions)) {
            foreach ($conditions as $key => $value) {
                switch ($key) {
                    case '_id':
                        $where['_id'] = new MongoId($value);
                        break;
                }
            }
        }
        return $where;
    }

    /**
     * free the connection
     *
     * @return void
     */
    public function free()
    {
        $this->_disconnect();
        $this->_connection = null;
    }
    
} 

