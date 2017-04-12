<?php
class zapRoute
{
    public $routes;
    public $params;
    public $controller;
    public $action;
    public $error;

    public function parse()
    {
        $this->routes = parse_ini_file(ROOT . 'core/settings/route.ini', true);

        if (strstr($_SERVER['REQUEST_URI'], '/index.php/')) {
            $route = explode('?', $_SERVER['REQUEST_URI']);
            $route = explode('index.php/', $route[0]);
            $route = explode('/', $route[1]);

            return $this->convertURL($route);
        } else if (empty($_GET['route'])) {
            $this->controller = 'index';
            $this->action     = 'index';
            $this->params     = array();

            return true;
        } else {
            $route = explode('/', $_GET['route']);

            return $this->convertURL($route);
        }

        return false;
    }

    public function convertURL($route)
    {
        foreach ($this->routes as $name => $config) {
            $pattern = explode('/', $config['pattern']);
            $match   = 0;

            if (count($pattern) != count($route))
                continue;

            foreach ($pattern as $key => $value) {
                if (substr($value, 0, 1) == ':') {
                    $i                = str_replace(':', '', $value);
                    $this->params[$i] = $route[$key];

                    $match++;
                } else if ($value == $route[$key]) {
                    $match++;
                }
            }

            if ($match == count($route)) {
                $this->controller = $config['controller'];
                $this->action     = $config['action'];

                return true;
            } else {
                $this->params = array();
            }
        }

        $controllerFile = ROOT . "application/controllers/{$route[0]}.controller.php";

        if (count($route) == 1) {
            if (file_exists($controllerFile)) {
                $this->controller = $route[0];
                $this->action     = 'index';
                $this->params     = array();

                return true;
            } else {
                $this->error = "<strong>{$route[0]}.controller.php</strong> does not exist.";
            }
        } else if (count($route) == 2) {
            if (file_exists($controllerFile)) {
                $this->controller = $route[0];
                $this->action     = $route[1];
                $this->params     = array();

                return true;
            } else {
                $this->error = "The file <strong>{$controllerFile}</strong> does not exist.";
            }
        } else {
            $this->error = "No routing scheme is defined for <strong>{$_GET['route']}</strong> in the route settings.";
        }

        return false;
    }
}
