<?php
/**
 * Creates the instance of the application
 */
class zapController
{
    /**
     * @var string $view Name of current view in use
     */
    public $view;
    /**
     * @var string $action Name of current action in use
     */
    public $action;
    /**
     *
     * @var string $controller Name of current controller in use
     */
    public $controller;
    /**
     *
     * @var string $template Name of current template in use
     */
    public $template;
    /**
     *
     * @var array $config Instance of the application settings
     */
    public $config;
    /**
     *
     * @var string $headTitle Name of the application
     */
    public $headTitle;
    public $headStyles;
    public $headScripts;
    public $favicon;
    /**
     *
     * @var array $error Contains the error message details
     */
    public $error;

    /**
     * Constructs the view by applying the set template and passing the view variables from the action
     * @return boolean Returns TRUE when the view is successfully created
     */
    public function createView()
    {
        //check if the view file exists
        if (!file_exists(ROOT . "application/views/{$this->controller}/{$this->action}.view.php")) {
            $this->error = array(
                'Error'   => 'View not found',
                'Details' => "View for for <strong>{$this->action}Action</strong> in the <em>{$this->controller}Controller</em> does not exist."
            );

            return false;
        }

        //apply templating
        if (empty($this->template)) {
            $this->zapView();
        } else {
            if (file_exists(ROOT . 'application/templates/' . $this->template)) {
                require_once(ROOT . 'application/templates/' . $this->template);
            } else {
                $this->error = array(
                    'Error'   => 'Template file not found',
                    'Details' => "<strong>{$this->template}</strong> does not exist."
                );

                return false;
            }
        }

        return true;
    }

    /**
     * Creates the instance of the view
     */
    public function zapView()
    {
        //create all the variables passed from the action
        if (!empty($this->view)) {
            foreach ($this->view as $key => $value) {
                $$key = $value;
            }
        }

        require_once(ROOT . "application/views/{$this->controller}/{$this->action}.view.php");
    }

    /**
     * Loads a partial block that can be inserted anywhere in the view
     * @param string $partial Partial name
     */
    public function zapPartial($partial)
    {
        //create all the variables passed from the action
        if (!empty($this->view)) {
            foreach ($this->view as $key => $value) {
                $$key = $value;
            }
        }

        require_once(ROOT . 'application/partials/' . strtolower($partial) . '.partial.php');
    }

    /**
     * Override the default with the specified template file
     * @param string $template Template name
     */
    public function zapTemplate($template = '')
    {
        if (empty($template)) {
            $this->template = '';
        } else {
            $this->template = $template . '.template.php';
        }
    }

    /**
     * Specifies a view to be used instead of the corresponding action view
     * @param type $view View name
     */
    public function useView($view)
    {
        $this->action = $view;
    }

    public function loadLibraryClass($className)
    {
        if (array_key_exists($className, $this->config['library'])) {
            require_once(ROOT . 'library/' . $this->config['library'][$className]);
            return true;
        } else {
            $this->error = array(
                'Error'   => 'Library class not found',
                'Details' => "The library class <strong>{$className}</strong> does not exist."
            );
            return false;
        }
    }
}
