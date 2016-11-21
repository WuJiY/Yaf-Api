<?php
use app\library\Controller;
use app\library\helpers\Utils;
use app\models\OrderComment;
use app\library\HttpException;
use Respect\Validation\Validator as v;

/**
 * @name OrderCommentController
 * @author chenming
 * @desc 用户评价
 */
class OrderCommentController extends Controller
{
    protected function verbs()
    {
        return [
            'getList' => ['GET'],
            'add' => ['POST'],
        ];
    }

    public function getListAction()
    {
        $limit = 15;
        $page = (int) $this->request->getQuery('page') ? : 1;
        $offset = ($page - 1) * $limit;
        
        $model = new OrderComment();
        
        $data = $model->getList($offset, $limit);
        
        $this->response->setContent($data);
    }
    
    public function addAction()
    {
        $return = [];
        
        $post = $this->request->getPost();
        $post['comment'] = trim($post['comment']);
        if (empty($post['comment'])) {
            $this->response->setContent($return, 400, 'comment is empty');
        }
        
        foreach (['pointHome', 'pointAttitude', 'pointQuality'] as $point) {
            if (false == (v::intVal()->between(1, 5)->validate($post[$point]))) {
                $this->response->setContent($return, 400,  $point . ' is out of range');
            }
        }

        $model = new OrderComment();
        $return = $model->addComment(
            (int) $post['orderId'], 
            (int) $post['mid'],
            $post['comment'],
            (int) $post['pointHome'],
            (int) $post['pointAttitude'],
            (int) $post['pointQuality']
        );
        
        $this->response->setContent($return);
    }
}
