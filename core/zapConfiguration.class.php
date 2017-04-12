<?php
/**
 * Builds the application based on the given route query
 */
class zapConfiguration
{
    /**
     * @var array $settings Contains the application settings from application.ini
     */
    public $settings;

    /**
     * Create the application
     */
    public function __construct()
    {
        //load app configuration
        $settings       = parse_ini_file(ROOT . 'core/settings/application.ini', true);
        $this->settings = $settings;

        //instantiate the library
        if (!empty($settings['toolkits'])) {
            foreach ($settings['toolkits'] as $library) {
                if (file_exists(ROOT . 'toolkits/' . $library)) {
                    require_once(ROOT . 'toolkits/' . $library);
                } else {
                    $this->displayError(array(
                        'Error'   => 'Missing toolkit class',
                        'Details' => "<strong>toolkits/{$library}</strong> does not exist."
                    ));

                    return false;
                }
            }
        }

        //load all Model classes
        require_once(ROOT . 'core/zapModel.class.php');

        if ($dir = opendir(ROOT . 'application/models')) {
            while (($file = readdir($dir)) !== false) {
                if (strpos($file, '.model.php'))
                    require_once(ROOT . 'application/models/' . $file);
            }

            closedir($dir);
        }

        //apply routing scheme
        require_once(ROOT . 'core/zapRoute.class.php');

        $route = new zapRoute();

        if ($route->parse()) {
            $params = $route->params;
            $url[0] = $route->controller;
            $url[1] = $route->action;
        } else {
            $this->displayError(array(
                'Error'   => 'Route undefined',
                'Details' => $route->error
            ));

            return false;
        }

        //load controller
        require_once(ROOT . 'core/zapController.class.php');
        require_once(ROOT . "application/controllers/{$url[0]}.controller.php");

        $controller = $url[0] . 'Controller';
        $app        = new $controller();
        $action     = $url[1] . 'Action';

        $app->controller = $url[0];
        $app->action     = $url[1];
        $app->config     = $settings;

        //set template
        if (empty($settings['application']['template'])) {
                $app->template = 'default.template.php';
        } else {
                $app->template = $settings['application']['template'];
        }

        //call action
        if (method_exists($app, $action)) {
            $app->$action($params);
        } else {
            $this->displayError(array(
                'Error'   => 'Action not found',
                'Details' => "<strong>{$action}</strong> does not exist in <em>{$controller}</em>."
            ));
            return false;
        }

        //init $view variable
        $app->view = new stdClass();

        //construct view
        if ($app->createView()) {
            return true;
        } else {
            $this->displayError($app->error);
            return false;
        }
    }

    /**
     * Error handling method for routes and missing files
     * @param array $error Information to be displayed on the error page
     */
    public function displayError($error)
    {
        if ($this->settings['application']['show_errors']) {
            echo '<pre>';
            print_r($error);
            echo '</pre>';
            echo '<small><b>Before you go live:</b> Turn off error messages in the <code>core/settings/application.ini</code> by setting <code>show_errors = false</code>.</small>';
        } else {
            echo '<h3>Oops!</h3>';
            echo '<p>Sorry but the page you are trying to reach does not exist.</p>';
            echo '<p>Error 404</p>';
        }

    }
}

//Lazy loading for automatically instantiating the toolkit classes
function __autoload($className)
{
    $dir  = ROOT . "toolkits/{$className}";
    $file = $dir . "/{$className}.class.php";

    if (is_dir($dir) && file_exists($file)) {
        require_once($file);
    }
}
