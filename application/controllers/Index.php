<?php
use app\library\Controller;

/**
 * @name IndexController
 * @author chenming
 * @desc default controller
 */
class IndexController extends Controller
{
    protected function verbs()
    {
        return [
            'index' => ['GET', 'HEAD'],
            'view' => ['GET', 'HEAD'],
            'create' => ['POST'],
            'update' => ['PUT', 'PATCH'],
            'delete' => ['DELETE'],
        ];
    }
    
    public function indexAction()
    {
        $this->response->setContent("hello, world");
    }
}
