<?php

class Ac_Router {

    /**
     * Full resource parts
     * @var array 
     */
    protected $resource = array();

    /**
     * Controller class name
     * @var string 
     */
    protected $controller = null;

    /**
     * Controller instance
     * @var Ac_Controller 
     */
    protected $controllerInstance = null;

    /**
     * Action function name
     * @var string 
     */
    protected $action = null;

    /**
     * Remaining resource from action
     * @var array 
     */
    protected $params = array();

    /**
     * Controller URL
     * @var string 
     */
    protected $controllerUrl = null;

    /**
     * Action URL
     * @var string 
     */
    protected $actionUrl = null;

    /**
     * Router directory URL
     * @var string 
     */
    protected $directoryUrl;

    /**
     * Router resource segments
     * @return array 
     */
    public function resource($index = null) {
        if ($index === null) {
            return $this->resource;
        } elseif (isset($this->resource[$index])) {
            return $this->resource[$index];
        }
        return null;
    }

    /**
     * Controller class name
     * @return string 
     */
    public function controller() {
        return $this->controller;
    }

    /**
     * Action function name
     * @return string 
     */
    public function action() {
        return $this->action;
    }

    /**
     * Parameters after the action segment
     * @param int $index
     * @return mixed 
     */
    public function params($index = null) {
        if ($index === null) {
            return $this->params;
        } elseif (isset($this->params[$index])) {
            return $this->params[$index];
        }
        return null;
    }

    public function controllerUrl() {
        return $this->controllerUrl;
    }

    public function actionUrl() {
        return $this->actionUrl;
    }

    public function directoryUrl() {
        return $this->directoryUrl;
    }

    /**
     * Autoloads a class and changes the current module
     * if the class belongs to a module
     * 
     * @param string $class_name
     * @return boolean 
     */
    protected function loadClass($class_name) {
        $result = Ac::loader()->autoload($class_name);
        // class loaded from /system
        if ($result === true) {
            return true;
        } elseif ($result instanceof Ac_Module) {
            // class loaded from /app or /modules/*
            Ac::loader()->setActiveModule($result->name(), false);
            return true;
        }

        // class not found
        return false;
    }

    public function resolve() {
        //Yet initialized
        if ($this->controller != null)
            return true;

        $request = Ac::trigger(__CLASS__ . "_before_" . __FUNCTION__, array('directoryUrl' => Ac::request()->directoryUrl(), 'resource' => Ac::request()->resource()));
        $this->directoryUrl = trim($request["directoryUrl"], ' /') . "/";

        if ($request["resource"] == "/")
            $request["resource"] = "";
        $this->resource = $rs = empty($request["resource"]) ? array() : explode("/", $request["resource"]);

        // Prevent? to access duplicated content when accessing '/', '/index' and '/index/index'
        $r = strtolower(implode("/", $this->resource));
        if (($r == "index") || ($r == "index/index")) {
            $this->resource = Ac::config("router.on_index") ? array(Ac::config("router.on_index")) : $this->resource;
        }

        $this->findController();
        $this->findAction();

        //die(print_r($this, true));

        Ac::trigger(__CLASS__ . "_on_" . __FUNCTION__, $this);
    }

    protected function findController() {
        $resource = $this->resource;
        if (!is_array($resource))
            $resource = explode("/", $resource);
        $params = array();
        $controller = "Controller_" . ucfirst(Ac::config("router.default_controller", "index"));
        if (count($resource) > 0) {
            while (count($resource) > 0) {
                $klass = "Controller_" . strtolower(ac_str_slug(implode(" ", $resource), "_"));
                if ($this->loadClass($klass)) {
                    $this->controllerUrl = $this->directoryUrl . implode('/', $resource) . "/";
                    $controller = $klass;
                    break;
                } else {
                    $params[] = array_pop($resource);
                }
            }
        }
        $this->params = array_reverse($params);
        $this->controller = $controller;
        return $controller;
    }

    protected function findAction() {
        $controller = $this->controller;
        $resource = $this->params;

        if (!is_array($resource))
            $resource = explode("/", $resource);

        if (count($resource) > 0) {
            $action = "__handle";
            $part = $resource[0];
            $fn = "action_" . strtolower(ac_str_slug($part, "_"));

            if (($fn == "action_index") && Ac::config("router.on_index")) {
                $fn = Ac::config("router.on_index");
            }
            if (method_exists($controller, $fn)) {
                $this->actionUrl = $this->controllerUrl . $part . "/";
                $action = $fn;
                array_shift($this->params);
            }
        } else {
            $action = "__index";
        }

        $this->action = $action;
        return $action;
    }

    public function call() {
        Ac::trigger(__CLASS__ . "_before_" . __FUNCTION__, $this);
        $klass = $this->controller;
        $fn = $this->action;
        $result = null;

        if ($fn == "__index")
            $validate_fn = "validate_index";
        elseif ($fn == "__handle")
            $validate_fn = "validate_handle";
        else
            $validate_fn = str_replace("action_", "validate_", $this->action);

        $this->controllerInstance = new $klass();
        $is_valid = false;

        if (!is_callable(array($this->controllerInstance, $fn))) {
            $fn = "__handle";
        }

        if (method_exists($klass, $validate_fn)) {
            if ($this->controllerInstance->$validate_fn($this->action)) {
                $is_valid = true;
                $result = $this->controllerInstance->$fn();
            } else {
                $result = $this->controllerInstance->__handle();
            }
        } else {
            if ($this->controllerInstance->__validate($this->action)) {
                $is_valid = true;
                $result = $this->controllerInstance->$fn();
            } else {
                $result = $this->controllerInstance->__handle();
            }
        }
        $result = Ac::trigger(__CLASS__ . "_on_" . __FUNCTION__, $result);
        return $result;
    }

}