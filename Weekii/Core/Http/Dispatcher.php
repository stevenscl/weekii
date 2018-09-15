<?php
namespace Weekii\Core\Http;

class Dispatcher
{
    // 控制器命名空间前缀
    private $nameSpacePrefix;

    public function __construct($controllerNameSpace)
    {
        $this->nameSpacePrefix = trim($controllerNameSpace, '\\');
    }

    /**
     * 调度
     */
    public function dispatch(Request $request, Response $response)
    {
        $router = new Router($request->getMethod(), $request->getPathInfo());

        $routeInfo = $router->dispatch();
        switch ($routeInfo['status']) {
            case RouteRule::NOT_FOUND:
                $request->setControllerNamespace($this->nameSpacePrefix . $routeInfo['target']);
                $list = explode('/', $routeInfo['target']);
                $controllerNamespace = $this->nameSpacePrefix;
                for ($i = 0; $i < count($list) - 1; $i++) {
                    $controllerNamespace = $controllerNamespace . '\\' . ucfirst($list[$i]);
                }
                $request->setControllerNamespace($controllerNamespace . 'Controller');
                $request->setActionName($list[$i]);
                var_dump('not found');
                break;
            case RouteRule::FOUND:
                $params = $request->get();
                // key相同的情况下，路由变量优先
                $request->setRequestParams($routeInfo['args'] + $params);

                if (is_callable($routeInfo['target'])) {
                    call_user_func_array($routeInfo['target'], [$request, $response]);
                } elseif (is_string($routeInfo['target'])) {
                    $list = explode('@', $routeInfo['target']);
                    $request->setControllerNamespace($list[0]);
                    $request->setActionName($list[1]);
                }
                break;
        }

        $this->runAction($request, $response);
    }

    public function runAction(Request $request, Response $response)
    {
        $controllerNamespace = $request->getControllerNamespace();
        if (class_exists($controllerNamespace)) {
            $obj = new $controllerNamespace($request, $response);
            $actionName = $request->getActionName();
            if (method_exists($obj, $actionName)) {
                $obj->$actionName();
            } else {
                $obj->actionNotFound();
            }
        } else {
            // 返回404
            $response->withStatus(404);
            $response->write('<h1>我是404</h1>');
        }
    }
}