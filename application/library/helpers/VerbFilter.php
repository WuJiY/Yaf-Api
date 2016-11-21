<?php
namespace app\library\helpers;

use app\library\Object;
use app\library\MethodNotAllowedHttpException;

/**
 * VerbFilter is an action filter that filters by HTTP request methods.
 *
 * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.7
 * @author chen ming
 * @since 1.0
 */
class VerbFilter extends Object
{
    /**
     * @var array this property defines the allowed request methods for each action.
     * For each action that should only support limited set of request methods
     * you add an entry with the action id as array key and an array of
     * allowed methods (e.g. GET, HEAD, PUT) as the value.
     * If an action is not listed all request methods are considered allowed.
     *
     * You can use '*' to stand for all actions. When an action is explicitly
     * specified, it takes precedence over the specification given by '*'.
     *
     * For example,
     *
     * ~~~
     * [
     *   'create' => ['get', 'post'],
     *   'update' => ['get', 'put', 'post'],
     *   'delete' => ['post', 'delete'],
     *   '*' => ['get'],
     * ]
     * ~~~
     */
    public $actions = [];

    /**
     * @param Action
     * @return boolean
     * @throws MethodNotAllowedHttpException when the request method is not allowed.
     */
    public function beforeAction($request)
    {
        $action = $request->getActionName();
        if (isset($this->actions[$action])) {
            $verbs = $this->actions[$action];
        } elseif (isset($this->actions['*'])) {
            $verbs = $this->actions['*'];
        } else {
            return false;
        }

        $verb = $request->getMethod();
        $allowed = array_map('strtoupper', $verbs);
        if (!in_array($verb, $allowed)) {
            // http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.7
            \Yaf\Registry::get('response')->getHeaders()->set('Allow', implode(', ', $allowed));
            \Yaf\Registry::get('response')->setContent('', 405, 'Method Not Allowed. This url can only handle the following request methods: ' . implode(', ', $allowed) . '.');
        }

        return true;
    }
}
