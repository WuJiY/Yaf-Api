<?php
namespace app\models;

use app\library\Model;
use app\library\helpers\Utils;
use app\library\helpers\Json;
use app\models\Order;
use app\library\Logger;

class OrderComment extends Model
{
    public static function tableName()
    {
        return 'order_comment';
    }
    
    public function getList($offset = 0, $limit = 15)
    {
        $return = [];
        
        $cacheKey = Utils::buildKey([__FUNCTION__, $offset, $limit]);
        $data = $this->redis->hget(CACHE_KEY_PREFIX . __CLASS__, $cacheKey);
        if (false === $data) {
            //fetch from DB, 获取审核通过(status = 1)的评论信息
            $data = $this->db->select(self::tableName(), '*', ['status' => 1,'LIMIT' => [$offset, $limit]]);
            if (($error = $this->db->error()) && !empty($error[1])) {
                throw new \ErrorException($error[2], $error[1], E_USER_ERROR);
            }
            
            foreach ($data as $item) {
                $order = Order::find($item['order_no']);
                if (!isset($order[0])) {
                    //查不到订单信息?
                    \Yaf\Registry::get('logger')->log("Order(". $item['order_no'] . ", commentId(". $item['ocid'] .")) can not found", Logger::LEVEL_WARNING);
                    continue;
                }
                $orderInfo = $order[0];
                $return[] = [
                    'orderId' => $item['order_no'],
                    'phone' => $orderInfo['client_phone'],
                    'avatar' => $item['order_no'], //TODO 获取用户头像地址
                    'comment' => $item['comment'], //TODO 视情况转义, 非网页似乎不用转义
                    'review' => $item['review'], //TODO 视情况转义, 非网页似乎不用转义
                    'createAt' => date('Y-m-d H:i', $item['create_at']),
                    'point' => floor(($item['point_home'] + $item['point_attitude'] + $item['point_quality']) / 3),
                    'mobile' => $orderInfo['mobile_id'], //TODO 机型ID转成具体名称
                    'fault' => $orderInfo['fault_remark'],
                ];
            }
            
            //wirte into cache
            $this->redis->hset(CACHE_KEY_PREFIX . __CLASS__, $cacheKey, Json::encode($return));
        } else {
            $return = Json::decode($data);
        }
       
        return $return;
    }
    
    public function addComment($orderId, $mid, $comment, $pointHome, $pointAttitude, $pointQuality)
    {
        $comment = Utils::byteSubstr($comment, 0, 250);
        
        $result = $this->db->insert(self::tableName(), [
            'order_no' => $orderId,
            'comment_by' => $mid,
            'comment' => $comment,
            'status' => 0,
            'create_at' => time(),
            'point_home' => $pointHome,
            'point_attitude' => $pointAttitude,
            'point_quality' => $pointQuality
        ]);
        
        if ($result == 0) {
            throw new \ErrorException('fail to insert new comment, Query: ' . $this->db->last_query());
        }
        
        return $result;
    }
    
}