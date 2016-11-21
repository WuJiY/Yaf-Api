<?php
namespace app\library;

use app\library\helpers\VerbFilter;
use app\library\HttpException;
use app\library\Model;

/**
 * Controller is the base class for classes containing controller logic.
 *
 * @author chen ming
 * @since 1.0
 */
abstract class Controller extends \Yaf\Controller_Abstract
{
    public $request = null;
    
    public $response = null;
    
    public $redis = null;
    
    public function init()
    {
        \Yaf\Dispatcher::getInstance()->disableView();
        
        $this->response = \Yaf\Registry::get('response');
        $this->redis = \Yaf\Registry::get('redis');
        $this->request = $this->getRequest();
        
        //请求方身份验证、参数校验
        APP_ENV != 'dev' && $this->checkAcess();
        
        (new VerbFilter(['actions' => $this->verbs()]))->beforeAction($this->request);
    }
    
    /**
     * @inheritdoc
     */
    abstract protected function verbs();
    
    protected function checkAcess()
    {
        $params = [];
        if ($this->request->isPost()) {
            $params = $this->request->getPost();
        } elseif ($this->request->isGet()) {
            $params = $this->request->getQuery();
        }
        
        $sign = trim($params['sign'] ?? '');
        $app = isset($params['app']) ? (int) $params['app'] : 0;
        if (empty($sign) || empty($app)) {
            $this->response->setContent([], 400, 'params sign or app is empty');
        }
        
        $timestamp = (int) $params['timestamp'];
        if ((TIMESTAMP - $timestamp) > 300) {
            $this->response->setContent([], 408, 'Request Time-out');
        }
        
        unset($params['sign']);
        $params['app'] = $app;
        $params['timestamp'] = $timestamp;
        
        $token = '';
        sort($params, SORT_STRING);
        foreach ($params as $key => $value) {
            $token .= $key . $value;
        }
        $token = sha1(Model::getSecert($app) . $token);
        if ($token != $sign) {
            $this->response->setContent([], 400, 'sign error');
        }

        return true;
    }
  
}