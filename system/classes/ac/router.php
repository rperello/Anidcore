<?php

class Ac_Router {

    public $controllerName = null;

    /**
     *
     * @var Ac_Controller 
     */
    public $controllerInstance = null;
    public $action = null;
    public $controllerUrl = null;
    public $actionUrl = null;
    public $resource = null;
    public $params = null; //remaining resource from action
    public $virtualBaseUrl;
    public $actionUrlParts = array();

    public function resource() {
        $this->resolve();
        return $this->resource;
    }

    public function controller() {
        $this->resolve();
        return $this->controllerName;
    }

    public function action() {
        $this->resolve();
        return $this->action;
    }

    public function params() {
        $this->resolve();
        return $this->params;
    }

    protected function virtualUrl() {
        return trim(implode("/", $this->actionUrlParts), "/") . "/";
    }

    public function resolve() {
        //Yet initialized
        if ($this->controllerName != null)
            return;

        $request = Ac::hookApply(Ac::HOOK_BEFORE_ROUTER_RESOLVE, array('directoryUrl' => Ac::request()->directoryUrl(), 'resource' => Ac::request()->resource()));
        $this->virtualBaseUrl = $request["directoryUrl"];

        if (empty($this->actionUrlParts)) {
            $this->actionUrlParts = array(trim($this->virtualBaseUrl, ' /'));
        }

        $this->resource = $rs = empty($request["resource"]) ? array() : explode("/", trim($request["resource"], " /"));

        //Defaults
        $default_app_controller = ucfirst(Ac::config("router.default_controller", "index"));
        $this->controllerName = ucfirst($default_app_controller);
        $this->action = "__default";


        if (empty($rs)) {
            $this->controllerUrl = $this->actionUrl = $this->virtualUrl();
        } else {

            //CONTROLLER
            $part = strtolower(ac_str_slug($rs[0], "_"));
            if ($part == $default_app_controller) {
                //prevent access to main app controller using /$default_app_controller/ segment
                $this->action = "__handle";
                $this->params = $rs;
                Ac::hookApply(Ac::HOOK_ON_ROUTER_RESOLVE, $this);
                return;
            }

            $controllerName = ucfirst($part); //Example: Pages
            $controller_exists = $this->loadClass($this->controllerClassName($controllerName));

            if ($controller_exists) {
                $this->controllerName = $controllerName;
                if (!empty($part)) {
                    $this->actionUrlParts[] = $part;
                }
                array_shift($rs);
            }

            $this->controllerUrl = $this->actionUrl = $this->virtualUrl();

            //ACTION
            if (!$controller_exists) {
                //is action of $default_app_controller controller?
                if (!$this->loadClass($this->controllerClassName($default_app_controller))) {
                    Ac::logger()->fatal($default_app_controller, "The controller cannot be loaded", __FILE__, __LINE__);
                } else {
                    if (method_exists($this->controllerClassName($this->controllerName), "action_{$part}")) {
                        $this->action = "action_{$part}";
                        $this->actionUrlParts[] = $part;
                        array_shift($rs);
                    } else {
                        if (empty($part))
                            $this->action = "__default";
                        else
                            $this->action = "__handle";
                    }
                }
                $this->actionUrl = $this->virtualUrl();
            }elseif (!empty($rs)) {
                // Controller exists, check action in next part:
                $part = strtolower(ac_str_slug($rs[0], "_"));
                if (method_exists($this->controllerClassName($controllerName), "action_{$part}")) {
                    $this->action = "action_{$part}";
                    $this->actionUrlParts[] = $part;
                    array_shift($rs);
                } else {
                    if (empty($part))
                        $this->action = "__default";
                    else
                        $this->action = "__handle";
                }
                $this->actionUrl = $this->virtualUrl();
            }
        }

        //PARAMS
        $this->params = $rs;

        Ac::hookApply(Ac::HOOK_ON_ROUTER_RESOLVE, $this);
    }

    public function controllerClassName($controllerName) {
        return "Controller_" . $controllerName;
    }

    public function call() {
        Ac::hookApply(Ac::HOOK_BEFORE_ROUTER_CALL, $this);
        $klass = $this->controllerClassName($this->controllerName);
        $fn = $this->action;
        $result = null;

        if ($fn == "__default")
            $validate_fn = "validate_default";
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
            if ($this->controllerInstance->$validate_fn($fn)) {
                $is_valid = true;
                $result = $this->controllerInstance->$fn();
            } else {
                $result = $this->controllerInstance->__handle($fn);
            }
        } else {
            if ($this->controllerInstance->__validate($fn)) {
                $is_valid = true;
                $result = $this->controllerInstance->$fn();
            } else {
                $result = $this->controllerInstance->__handle($fn);
            }
        }
        $result = Ac::hookApply(Ac::HOOK_ON_ROUTER_CALL, $result);
        return $result;
    }

    public function controllerUrl() {
        $this->resolve();
        return $this->controllerUrl;
    }

    public function actionUrl() {
        $this->resolve();
        return $this->actionUrl;
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
            $this->current_module_name = $result->name;
            return true;
        }

        // class not found
        return false;
    }

}