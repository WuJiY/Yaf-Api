<?php
namespace app\library;

class Redis
{
    private static $_instance = null;
    private static $_redis = null;
    
    /**
     * @var string the hostname or ip address to use for connecting to the redis server. Defaults to 'localhost'.
     * If [[unixSocket]] is specified, hostname and port will be ignored.
     */
    public $hostname = 'localhost';
    /**
     * @var integer the port to use for connecting to the redis server. Default port is 6379.
     * If [[unixSocket]] is specified, hostname and port will be ignored.
     */
    public $port = 6379;
    /**
     * @var string the unix socket path (e.g. `/var/run/redis/redis.sock`) to use for connecting to the redis server.
     * This can be used instead of [[hostname]] and [[port]] to connect to the server using a unix socket.
     * If a unix socket path is specified, [[hostname]] and [[port]] will be ignored.
     * @since 2.0.1
     */
    public $unixSocket;
    /**
     * @var string the password for establishing DB connection. Defaults to null meaning no AUTH command is send.
     * See http://redis.io/commands/auth
     */
    public $requirepass;
    /**
     * @var integer the redis database to use. This is an integer value starting from 0. Defaults to 0.
     * Since version 2.0.6 you can disable the SELECT command sent after connection by setting this property to `null`.
     */
    public $database = 0;
    /**
     * @var float timeout to use for connection to redis. If not set the timeout set in php.ini will be used: ini_get("default_socket_timeout")
     */
    public $connectionTimeout = 2.5;
    /**
     * @var float timeout to use for redis socket when reading and writing data. If not set the php default value will be used.
     */
    
    public $connected = false;
    
    private function __construct($params)
    {
        self::$_redis = new \Redis();
        $this->hostname = $params['hostname'];
        $this->port = $params['port'];
        isset($params['timeout']) && $params['timeout'] && $this->connectionTimeout = $params['timeout'];
        isset($params['requirepass']) && $params['requirepass'] && $this->requirepass = $params['requirepass'];
    }
    
    private function __clone()
    {
        
    }
    
    static public function getInstance($params)
    {
        if (is_null(self::$_instance) || !isset(self::$_instance)) {
            self::$_instance = new self($params);
        }
        
        return self::$_instance;
    }
    
    public function __call($name, $params)
    {
        if (! $this->connected) {
            try {
                $connect = @self::$_redis->connect($this->hostname, $this->port, $this->connectionTimeout);
            } catch (\RedisException $e) {
                throw new \Exception($e->getMessage());
			}
			
			if ($connect && $this->requirepass) {
			    $auth = self::$_redis->auth($this->requirepass);
			    if (! $auth) {
			        throw new \Exception('Redis auth fail');
			    }
			}
			
			$this->connected = true;
        }
        
        return call_user_func_array([self::$_redis, $name], $params);
    } 
}