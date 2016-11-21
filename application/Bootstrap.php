<?php
use app\library\Redis;
use app\library\DB;
use app\library\Logger;

/**
 * @name Bootstrap
 * @author root
 * @desc 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,
 * 这些方法, 都接受一个参数:Yaf_Dispatcher $dispatcher
 * 调用的次序, 和申明的次序相同
 */
class Bootstrap extends Yaf\Bootstrap_Abstract
{

    public function _initConfig()
    {
        $this->baseConfig = Yaf\Application::app()->getConfig();
        Yaf\Registry::set('config', $this->baseConfig);
    }
    
    // 载入数据库
    public function _initDatabase()
    {
        $params = [
            'database_type' => $this->baseConfig->database->database_type,
            'database_name' => $this->baseConfig->database->database_name,
            'server' => $this->baseConfig->database->server,
            'username' => $this->baseConfig->database->username,
            'password' => $this->baseConfig->database->password,
            'charset' => $this->baseConfig->database->charset,
        
            // [optional]
            'port' => $this->baseConfig->database->port,
        
            // [optional] Table prefix
            'prefix' => $this->baseConfig->database->prefix,
        
            // driver_option for connection, read more from http://www.php.net/manual/en/pdo.setattribute.php
            'option' => [PDO::ATTR_CASE => PDO::CASE_NATURAL]
        ];
        
        Yaf\Registry::set('db', DB::getInstance($params));
        //var_dump(Yaf\Registry::get('db')->info());
    }
    
    // 载入缓存类
    public function _initRedis()
    {
        $params = [
            'hostname' => $this->baseConfig->redis->hostname,
            'port' => $this->baseConfig->redis->port,
            'timeout' => $this->baseConfig->redis->timeout,
            'requirepass' => $this->baseConfig->redis->requirepass
        ];
        
        Yaf\Registry::set('redis', Redis::getInstance($params));
        /*
        Yaf\Registry::get('redis')->set('redis_test', 'my turn');
        var_dump(Yaf\Registry::get('redis')->get('redis_test'));die;
        */
    }

    /*public function _initPlugin(Yaf_Dispatcher $dispatcher)
    {
        // 注册一个插件
    }*/
    
    public function _initLogger()
    {
        Yaf\Registry::set('logger', new Logger([
            'db' => Yaf\Registry::get('db'),
        ]));
        //throw new \Exception('test');
    }
    
    public function _initRoute(Yaf\Dispatcher $dispatcher)
    {
        /*
        $routePage  = new Yaf\Route\Rewrite("/page/:pageNum",
            [
                "controller" => "index",
                "action"     => "index",
            ] 
        );
        $routeBlog  = new Yaf\Route\Rewrite("/blog/:blogId",
            [
                "controller" => "blog",
                "action"     => "detail",
            ] 
        );
        
        $router = Yaf\Dispatcher::getInstance()->getRouter();
        $router->addRoute("page", $routePage);
        $router->addRoute("blog", $routeBlog);
        */
    }
}
