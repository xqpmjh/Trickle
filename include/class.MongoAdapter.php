<?php
/**
 * @author kim (happy life, happy PHP, lol)
 * @since 2011-11-28
 * @version 1.0.0
 */

/**
 * class MongoAdapter
 *
 * Simple Mongo operation wrapper
 *
 * @example
 * $config = array(
 *           'servers'  => 'localhost:27017',
 *           'database' => 'test',
 *           'username' => 'kim',
 *           'password' => 'kim'
 * );
 * $mongo = new MongoAdapter($config);
 * $commentList = $mongo->findAll('comment');
 *
 * @tutorial
 * it's recommended to use the adapter together with your model classes:
 * $mongo = new MongoAdapter($config);
 * $foo = new foo($mongo);
 *
 * @todo
 * # collectionExists(), see MongoDB::listCollections()
 *                      check if collection unexists?
 * # MongoDB::setProfilingLevel() - for profiling under development/testing?
 * # MongoDB::command - sending command to mongodb?
 * # MongoCollection::ensureIndex - enable user to adding indexes?
 * # MongoDB::execute - interface of executing javascript functions?
 * # Should MongoPool::setSize() - to limit the pool size?
 * # MongoGridFS - for file upload cases?
 * # MongoTimestamp - and auto-sharding?
 * # MongoMinKey / MongoMaxKey - let some records be always popular on top?
 *
 */
final class MongoAdapter
{
    /**
     * log file path
     *
     * @var string
     */
    static public $logFile = '';

    /**
     * connection object
     *
     * @link http://www.php.net/manual/en/class.mongo.php
     * @var Mongo - instance of Mongo
     */
    protected $_connection = null;

    /**
     * db object
     *
     * @link http://www.php.net/manual/en/class.mongodb.php
     * @var MongoDB - instance of MongoDB
     */
    protected $_db = null;

    /**
     * database configurations
     *
     * @var array $_config
     */
    protected $_config = array(
        'servers'     => array('127.0.0.1:27017'),
        'database'    => null,
        'username'    => null,
        'password'    => null,
    );

    /**
     * the last insert object id
     *
     * @var MongoId
     */
    protected $_lastInsertId = null;

    /**
     * Mongo options
     * @link http://www.php.net/manual/en/mongo.connecting.php
     *
     * "Persistent connections are highly recommended and should always
     *  be used in production unless there is a compelling reason not to."
     *
     * "If you are using a replica set...
     *  the driver can automatically route reads to slaves."
     *
     * @deprecated persist is not relevant for 1.2.0+. In 1.2.0+
     * @var array $_options
     */
    protected $_options = array(
        'persist' => 'x',
        'replicaSet' => false,
    );

    /**
     * @return the $_lastInsertId
     */
    public function getLastInsertId ()
    {
        return $this->_lastInsertId;
    }

	/**
     * @param MongoId $_lastInsertId
     */
    public function setLastInsertId ($_lastInsertId)
    {
        $this->_lastInsertId = $_lastInsertId;
    }

	/**
     * @return array $_options
     */
    public function getOptions ()
    {
        return $this->_options;
    }

    /**
     * @param array $_options
     */
    public function setOptions ($_options)
    {
        $this->_options = $_options;
    }

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
     * @param mixed $options
     * @return void
     */
    public function __construct($config, $options = null)
    {
        // set mongo options
        if (is_array($options) and !empty($options)) {
            $this->setOptions($options);
        }

        // set mongo connection configs
        if (is_array($config) and !empty($config)) {
            $this->setConfig($config);
        } else {
            throw new MongoException("Please provide connection configs!");
        }
    }

    /**
     * do connect store the Mongo instance
     * then select the database and store MongoDB instance
     * just before the first query
     *
     * @link http://www.php.net/manual/en/mongo.connecting.php
     * we are new using the URI format
     * because it will auto reauthenticate after reconnect
     *
     * @return MongoDB - return Mongo database object
     */
    protected function _connect()
    {
        if (!$this->_db) {
            $config = $this->getConfig();
            if (!empty($config) and is_array($config)) {

                // init connection informations, using URI format
                $connectInfo = 'mongodb://';

                // server informations
                if (!empty($config['servers'])) {
                    if (is_array($config['servers'])) {
                        $connectInfo .= implode(',', $config['servers']);
                    } else {
                        $connectInfo .= (string)$config['servers'];
                    }

                    // is multiple servers then it should be replica set
                    if (count($config['servers']) > 1) {
                        if (isset($config['replicaSet']) and
                            !empty($config['replicaSet'])) {
                            $options = $this->getOptions();
                            $options['replicaSet'] = $config['replicaSet'];
                            $this->setOptions($options);
                        }
                    }
                } else {
                    throw new MongoException("Server configs missing!");
                }

                // if timeout is set
                if (isset($config['timeout']) and !empty($config['timeout'])) {
                    $options = $this->getOptions();
                    $options['timeout'] = $config['timeout'];
                    $this->setOptions($options);
                }

                /**
                 * get connection
                 * @todo do logs?
                 */
                try {
                    $options = $this->getOptions();
                    //header('cinfo: ' . $connectInfo . ' - ' . json_encode($options));
                    $conn = new Mongo($connectInfo, $options);

                    // for testing replica sets
                    //echo 'Debug: <br />'; var_dump($conn->getHosts());

                } catch (Exception $e) {
                    try {
                        unset($conn);
                        $conn = new Mongo($connectInfo, $options);
                    } catch (Exception $e) {
                        throw new MongoConnectionException(
                                "Fails to connect : " . $e->getMessage());
                    }
                }

                // pass queries to slaves by default
                //$conn->setSlaveOkay(true);
                $this->setConnection($conn);

                /**
                 * get database and try to ping
                 *
                 * by the time when this package is under development,
                 * the small funny "bug" is still not fixed by the author
                 * @link https://bugs.php.net/bug.php?id=60508
                 */

                // database name
                if (!empty($config['database'])) {
                    $dbname = (string)$config['database'];
                    //$connectInfo .= '/' . $dbname;
                    $db = $conn->selectDB($dbname);
                    if (!$db or !($db instanceof MongoDB)) {
                        throw new MongoException("Unable to select database!");
                    }
                } else {
                    throw new MongoException("Database configs not found!");
                }

                /**
                 * auth information
                 * @todo what if a username got ':' at the end?
                 * @todo what if a password got '@' at the end?
                 */
                if (isset($config['username']) && isset($config['password'])) {
                    /*$connectInfo .= (string)$config['username'] . ':'
                                  . (string)$config['password'] . '@';*/
                    $authResult = $db->authenticate(
                        (string)$config['username'],
                        (string)$config['password']
                    );
                    if (!isset($authResult['ok']) or $authResult['ok'] != 1) {
                        $authResult = $db->authenticate(
                            (string)$config['username'],
                            (string)$config['password']
                        );
                    }
                }

                try {
                    //$db->command(array("ping" => 1));
                } catch (MongoCursorException $e) {
                    throw new MongoConnectionException(
                            "Fails to connect db : " . $e->getMessage());
                }

                //$db->setSlaveOkay(true);
                $this->setDb($db);
            } else {
                throw new MongoException("Invalid configurations!");
            }
        }

        return $this->getDb();
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
     * Disconnect : if you are connected to a replica set,
     * close() will only close the connection to the primary.
     *
     * @return bool - Returns if the connection was successfully closed.
     */
    protected function _disconnect()
    {
        $result = false;
        $conn = $this->getConnection();
        if ($conn instanceof Mongo) {
            $result = $conn->close();
        }
        return $result;
    }

    /**
     * do insert, save the last record id
     *
     * @link http://www.php.net/manual/en/mongo.writes.php
     * "To get a response from the database, use the safe option,
     *  available for all types of writes. This option will make sure that
     *  the database has the write before returning success."
     *
     * @param string $collectionName
     * @param array $data
     * @param boolean $nbSafeInsert - number of slaves that should get the copy
     * @return boolean
     */
    public function insert($collectionName, $data, $nbSafeInsert = 1)
    {
        $collection = $this->_getCollection($collectionName);
        $result = $collection->insert($data, array('safe' => $nbSafeInsert));
        if (isset($data['_id'])) {
            $this->setLastInsertId($data['_id']);
        } else {
            $this->setLastInsertId(null);
        }
        if ($result === true or (isset($result['ok']) and $result['ok'] == 1)) {
            return true;
        }
        return false;
    }

    /**
     * do multiple insert
     *
     * @todo there is a bug with batchInsert that wait to fix before using it.
     * E11000 duplicate key error index: ...
     *
     * @param string $collectionName
     * @param array $data - An array of arrays
     * @param boolean $nbSafeInsert - number of slaves that should get the copy
     * @return boolean
     */
    public function batchInsert($collectionName, $data, $nbSafeInsert = 1)
    {
        $collection = $this->_getCollection($collectionName);
        $result = $collection->batchInsert($data, array('safe' => $nbSafeInsert));
        if (isset($result['ok']) and $result['ok'] == 1) {
            return true;
        }
        return false;
    }

    /**
     * do update, could be multiple
     *
     * @param string $collectionName
     * @param array $criteria
     * @param array $newobj
     * @param array $options - "multiple" is false by default which means
     *                         we can update only one doc on one time.
     *                         "safe" is true which means we need to wait for
     *                         update result, instead of always returning true.
     * @return boolean - true on successed, false on failed
     */
    public function update($collectionName, $criteria, $newobj,
        $options = array('multiple' => false, 'safe' => true))
    {
        $collection = $this->_getCollection($collectionName);
        $result = $collection->update($criteria, $newobj, $options);
        if (true === $result or
            (true == $result['updatedExisting'] and 1 == $result['ok'])) {
            return true;
        }
        return false;
    }

    /**
     * do update with @set operation
     *
     * @param string $collectionName
     * @param array $conditions
     * @param array $newDoc - the new document for update
     * @param array $options - see update()
     * @return boolean - true on successed, false on failed
     */
    public function updateOpSet($collectionName, $conditions, $newDoc,
        $options = array('multiple' => false, 'safe' => true))
    {
        $newobj = array('$set' => (array)$newDoc);
        $result = $this->update($collectionName, $conditions,
                                $newobj, $options);
        return $result;
    }

    /**
     * do save, for only one document
     *
     * @param string $collectionName
     * @param array $criteria
     * @param array $fields - the fields and values to save
     * @return bool
     */
    public function save($collectionName, $criteria, $fields)
    {
        $result = false;
        $collection = $this->_getCollection($collectionName);
        $row = $collection->findOne($criteria);
        if ($row) {
            foreach ($fields as $field => $value) {
                $row[$field] = $value;
            }
            $result = $collection->save($row) ? true : false;
        }
        return $result;
    }

    /**
     * do remove, for only one record by default, in safe mode
     *
     * @link http://www.php.net/manual/en/mongocollection.remove.php
     *
     * @param array $criteria
     * @param array $options - 1) one record every time
     *                         2) safe mode: wait for respond from MongoDB
     * @return bool
     */
    public function remove($collectionName, $criteria = array(),
        $options = array('justOne' => true, 'safe' => true))
    {
        $collection = $this->_getCollection($collectionName);
        $result = $collection->remove($criteria, $options);
        //var_dump($result);
        if (isset($result['ok']) and $result['ok'] and
            isset($result['n']) and $result['n'] > 0 and !$result['err']) {
            return true;
        }
        return false;
    }

    /**
     * find one record
     *
     * @param string $collectionName
     * @param array $criteria
     * @param array $fields
     * @return array|null - return value is defined at:
     *         http://www.php.net/manual/en/mongocollection.findone.php
     */
    public function findOne($collectionName, $criteria, $fields = array())
    {
        $collection = $this->_getCollection($collectionName);
        $result = $collection->findOne($criteria, $fields);
        return $result;
    }

    /**
     * find all the records
     *
     * @todo
     * MongoCursor::addOption()
     * MongoCollection::group()
     * MongoCursor::limit() / MongoCursor::skip()
     *
     * @param string $collectionName
     * @param array $fields - Fields of the results to return
     * @param array $query - The fields for which to search
     * @param array $operations
     * @return MongoCursor
     */
    public function findAll($collectionName, $fields = array(),
            $query = array(), $operations = null, $timeout = 3999)
    {
        $result = array();
        try {
            $collection = $this->_getCollection($collectionName);
            $entities = $collection->find($query, $fields);

            // if some operations need to apply
            if (is_array($operations) and !empty($operations)) {
                if (isset($operations['sort']) and is_array($operations['sort'])) {
                    $entities = $entities->sort($operations['sort']);
                }
                if (isset($operations['skip'])) {
                    $entities = $entities->skip((int)$operations['skip']);
                }
                if (isset($operations['limit'])) {
                    $entities = $entities->limit((int)$operations['limit']);
                }
            }
            $entities->timeout($timeout);

            foreach ($entities as $e) {
                $result[] = $e;
            }
            return $result;
        } catch (Exception $e) {
            $this->mgLog($e, $query, $operations);
            throw new MongoCursorException(
                            "Fails find all : " . $e->getMessage());
        }
    }

    /**
     * do a "group by" liked operation
     *
     * @param string $collectionName
     * @param array $keys - Fields to group by
     * @param array $initial - Initial value of the aggregation counter object
     * @param MongoCode|string $reduce - function that takes two arguments and
     *                            does the aggregation.
     * @param array $options
     * @return array - the group result
     */
    public function group($collectionName, $keys = array(), $initial = array(),
                          $reduce, $options = array())
    {
        if (!($reduce instanceof MongoCode)) {
            $reduce = new MongoCode($reduce);
        }
        $collection = $this->_getCollection($collectionName);
        $result = $collection->group($keys, $initial, $reduce, $options);
        return $result;
    }

    /**
     * count number of collections
     *
     * @param string $collectionName
     * @return integer
     */
    public function count($collectionName, $query = array(),
                          $limit = 0, $skip = 0)
    {
        try {
            $collection = $this->_getCollection($collectionName);
            $result = $collection->count($query, $limit, $skip);
            return $result;
        } catch (Exception $e) {
            $this->mgLog($e, $query, array('limit' => $limit, 'skip' => $skip));
            throw new MongoCursorException(
                            "Failed counting : " . $e->getMessage());
        }
    }

    /**
     * get reference document
     *
     * @param string $collectionName
     * @param array $ref - the reference object
     * @return array - return value is defined at:
     *         http://www.php.net/manual/en/mongocollection.getdbref.php
     */
    public function getDbRef($collectionName, $ref)
    {
        $result = array();
        if (!empty($ref) and MongoDBRef::isRef($ref)) {
            $collection = $this->_getCollection($collectionName);
            $result = $collection->getDBRef($ref);
        }
        return $result;
    }

    /**
     * create reference document by id
     * we now transform the string id (which is invalid for reference creation)
     * to MongoId by default
     *
     * @param string $collectionName - the collection name
     * @param mixed $objId - the document id
     * @return array
     */
    public function createRef($collectionName, $objId)
    {
        if (!empty($objId)) {
            $collection = $this->_getCollection($collectionName);
            $ref = $collection->createDBRef($objId);
            return $ref;
        } else {
            throw new MongoException("Invalid object id!");
        }
    }

    /**
     * get list of collections of current database
     * @link http://www.php.net/manual/en/mongodb.listcollections.php
     *
     * @param bool $isReturnNames - true: return names
     *                              false: return original objects
     * @return array - array of collection objects or names
     */
    public function listCollections($isReturnNames = true)
    {
        $result = array();
        $this->_connect();
        $collections = $this->getDb()->listCollections();
        if ($isReturnNames) {
            $needle = (string)$this->getDb() . '.';
            foreach ($collections as $c) {
                $cName = (string)$c;
                $pos = strpos($cName, $needle) + strlen($needle);
                $result[] = substr($cName, $pos);
            }
        } else {
            $result = $collections;
        }
        return $result;
    }

    /**
     * get indexes information
     *
     * @param string $collectionName - the collection name
     * @return array
     */
    public function getIndexInfo($collectionName)
    {
        $collection = $this->_getCollection($collectionName);
        $result = $collection->getIndexInfo();
        return $result;
    }

    /**
     * drop the collection, example of response:
     *
     * success:
     * array(4) { ["nIndexesWas"]=> float(1)
     *            ["msg"]=> string(30) "indexes dropped for collection"
     *            ["ns"]=> string(12) "test.comment"
     *            ["ok"]=> float(1) }
     *
     * fails:
     * array(2) { ["errmsg"]=> string(12) "ns not found"
     *            ["ok"]=> float(0) }
     *
     * @param string $collectionName
     * @param string|false $confirm - "I_KNOW_WHAT_I_AM_DOING" :
     *                              only when you know what you are doing!
     * @return true
     */
    public function drop($collectionName, $confirm = false)
    {
        if ("I_KNOW_WHAT_I_AM_DOING" === $confirm) {
            $cList = $this->listCollections(true);
            if (in_array($collectionName, $cList)) {
                $collection = $this->_getCollection($collectionName);
                $result = $collection->drop();
                if (isset($result['ok']) and !$result['ok']) {
                    throw new MongoException(
                        "Unable to drop collection : " . $collectionName);
                }
            }
            return true;
        }
    }

    /**
     * build criterias by conditions array provided
     *
     * @deprecated
     *
     * @param array $conditions
     * @return array $criteria - queries in mongo format
     */
    /*protected function _buildCriteria($conditions)
    {
        $criteria = array();
        if (!empty($conditions) and is_array($conditions)) {
            foreach ($conditions as $key => $value) {
                switch ($key) {
                    case '_id':
                        if (!($value instanceof MongoId)) {
                            $criteria['_id'] = new MongoId($value);
                        }
                        break;
                    default:
                        $criteria[$key] = $value;
                        break;
                }
            }
        }
        return $criteria;
    }*/

    /**
     * free the connection
     *
     * @return bool
     */
    public function free()
    {
        $result = $this->_disconnect();
        $this->_db = null;
        $this->_connection = null;
        return $result;
    }

    /**
     * do log
     *
     * need ext-defined log path: self::$logFile
     *
     * @param Exception $e
     * @param array|false $criteria
     * @param array|false $operations
     * @return void
     */
    public function mgLog($e, $criteria = false, $operations = false)
    {
        if (is_dir(dirname(self::$logFile))) {
            $lastErr['err'] = '';
            if ($this->_db instanceof Mongo) {
                $lastErr = $this->_db->lastError();
            }
        	$logData = "\n" . date('Y-m-d H:i:s') . "\n"
        	     . 'httphost : ' . 'http://' . $_SERVER ['HTTP_HOST']
        	     . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'] . "\n"
    	         . 'curError : ' . $e->getMessage() . "\n"
    	         . (!empty($lastErr['err'])
    	           ? ('lastError : ' . $lastErr['err'] . "\n") : '')
    	         . 'host : ' . implode(',', $this->_config['servers']) . "\n"
    	         . 'database : ' . $this->_config['database'] . "\n";
        	if (!empty($criteria)) {
        	    $logData .= 'criteria : ' . json_encode($criteria) . "\n";
        	}
        	if (!empty($operations)) {
        	    $logData .= 'operations : ' . json_encode($operations) . "\n";
        	}
            file_put_contents(self::$logFile, $logData, FILE_APPEND);
        }
    }

}

