<?php
namespace app\library;

/**
 * Model is the base class for data models.
 *
 * @author chen ming
 * @since 1.0
 */
class Model extends Object
{
    public $db = null;
    
    public $redis = null;

    public function __construct()
    {
        $this->db = \Yaf\Registry::get('db');
        $this->redis = \Yaf\Registry::get('redis');
    }
    
    public static function getSecert($appId)
    {
        //TODO 从配置中读 OR 后台生成、配置、管理
        
    }
}