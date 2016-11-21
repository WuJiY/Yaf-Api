<?php
namespace app\models;

use app\library\Model;
use app\library\helpers\Json;

class Order extends Model
{
    
    const ORDER_INFO_CACHE_PREFIX = CACHE_KEY_PREFIX . 'orderId:';
    
    public static function tableName()
    {
        return 'order';
    }
    
    /**
     * find order by id or id array
     * @param integer|array $order the order Id or id array
     * @return array
     * @throws \Exception if there is any error
     */
    public static function find($order)
    {
        $return = [];
        ! is_array($order) && $order = [(int) $order];
        
        $cacheKeys = array_map(function($item){return self::ORDER_INFO_CACHE_PREFIX . $item;}, $order);
        $redis = \Yaf\Registry::get('redis');
        $data = $redis->mGet($cacheKeys);
        $fetch = false;
        foreach ($data as $key => $value) {
            if (false === $value) {
                $fetch = true;
                $redis->delete($cacheKeys);
                break;
            }
        }
        
        if ($fetch) {
            $db = \Yaf\Registry::get('db');
            $return = $db->select(self::tableName(), '*', ['no' => $order]);
            if (($error = $db->error()) && !empty($error[1])) {
                throw new \ErrorException($error[2], $error[1], E_USER_ERROR);
            }
            
            //wirte into cache
            foreach ($return as $one) {
                $redis->set(self::ORDER_INFO_CACHE_PREFIX . $one['no'], Json::encode($one), CACHE_KEY_EXPIRE);
            }
        } else {
            foreach ($data as &$item) {
                $item = Json::decode($item);
            }
            $return = $data;
        }

        return $return;
    }
}