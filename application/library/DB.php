<?php
namespace app\library;

use medoo;

class DB
{
    private static $_instance = null;
    private static $_db = null;

    public $dbConf = [];
    
    public $connected = false;
    
    private function __construct($params)
    {
        $this->dbConf = $params;
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
            self::$_db = new medoo($this->dbConf);
            $this->connected = true;
        }
        
        return call_user_func_array([self::$_db, $name], $params);
    } 
}