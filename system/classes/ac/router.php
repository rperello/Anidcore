<?php

class Ac_Router{

    protected $controller_name = null;

    /**
     *
     * @var Ac_Controller 
     */
    protected $controller_instance = null;
    protected $action = null;
    protected $controller_url = null;
    protected $action_url = null;
    protected $resource = null;
    protected $params = null; //remaining resource from action
    protected $canonical_segments = array();

    public function info() {
        return array(
            "controller_name" => $this->controller_name,
            "action" => $this->action,
            "resource" => $this->resource,
            "params" => $this->params,
            "canonical_url" => $this->canonicalUrl(),
            "controller_url" => $this->controllerUrl(),
            "action_url" => $this->actionUrl()
        );
    }

    public function resource() {
        $this->resolve();
        return $this->resource;
    }

    public function controller() {
        $this->resolve();
        return $this->controller_name;
    }

    public function action() {
        $this->resolve();
        return $this->action;
    }

    public function params() {
        $this->resolve();
        return $this->params;
    }

    public function canonicalUrl() {
        return trim(implode("/", $this->canonical_segments), "/") . "/";
    }

    public function canonicalAdd($segment) {
        $this->canonical_segments[] = $segment;
    }

    public function segment($index, $default = false, $filter = null) {
        $this->resolve();
        return ac_arr_value($this->resource, $index, $default, $filter);
    }

    public function resolve() {
        //Yet initialized
        if ($this->controller_name != null)
            return;

        Ac::hookApply(Ac::HOOK_BEFORE_ROUTER_RESOLVE, $this);

        if (empty($this->canonical_segments)) {
            $this->canonical_segments = array(trim(Ac::request()->baseUri, " /"));
        }

        $rs = Ac::request()->resource;
        $this->resource = $rs = empty($rs) ? array() : explode("/", trim($rs, " /"));

        $rs = Ac::hookApply(Ac::HOOK_ON_ROUTER_RESOURCE, $rs);

        //Defaults
        $default_app_controller = ucfirst(Ac::config("router.default_controller", "index"));
        $this->controller_name = ucfirst($default_app_controller);
        $this->action = "__default";


        if (empty($rs)) {
            $this->controller_url = $this->action_url = $this->canonicalUrl();
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

            $controller_name = ucfirst($part); //Example: Pages
            $controller_exists = Ac::autoload($this->controllerClassName($controller_name));

            if ($controller_exists) {
                $this->controller_name = $controller_name;
                if (!empty($part)) {
                    $this->canonicalAdd($part);
                }
                array_shift($rs);
            }

            $this->controller_url = $this->action_url = $this->canonicalUrl();

            //ACTION
            if (!$controller_exists) {
                //is action of $default_app_controller controller?
                if (!Ac::autoload($this->controllerClassName($default_app_controller))) {
                    Ac::logger()->fatal($default_app_controller, "The controller cannot be loaded", __FILE__, __LINE__);
                } else {
                    if (method_exists($this->controllerClassName($this->controller_name), "action_{$part}")) {
                        $this->action = "action_{$part}";
                        $this->canonicalAdd($part);
                        array_shift($rs);
                    } else {
                        if (empty($part))
                            $this->action = "__default";
                        else
                            $this->action = "__handle";
                    }
                }
                $this->action_url = $this->canonicalUrl();
            }elseif (!empty($rs)) {
                // Controller exists, check action in next part:
                $part = strtolower(ac_str_slug($rs[0], "_"));
                if (method_exists($this->controllerClassName($controller_name), "action_{$part}")) {
                    $this->action = "action_{$part}";
                    $this->canonicalAdd($part);
                    array_shift($rs);
                } else {
                    if (empty($part))
                        $this->action = "__default";
                    else
                        $this->action = "__handle";
                }
                $this->action_url = $this->canonicalUrl();
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
        $klass = $this->controllerClassName($this->controller_name);
        $fn = $this->action;
        $result = null;

        if ($fn == "__default")
            $validate_fn = "validate_default";
        elseif ($fn == "__handle")
            $validate_fn = "validate_handle";
        else
            $validate_fn = str_replace("action_", "validate_", $this->action);

        $this->controller_instance = new $klass();
        $is_valid = false;

        if (!is_callable(array($this->controller_instance, $fn))) {
            $fn = "__handle";
        }

        if (method_exists($klass, $validate_fn)) {
            if ($this->controller_instance->$validate_fn($fn)) {
                $is_valid = true;
                $result = $this->controller_instance->$fn();
            } else {
                $result = $this->controller_instance->__handle($fn);
            }
        } else {
            if ($this->controller_instance->__validate($fn)) {
                $is_valid = true;
                $result = $this->controller_instance->$fn();
            } else {
                $result = $this->controller_instance->__handle($fn);
            }
        }
        $result = Ac::hookApply(Ac::HOOK_ON_ROUTER_CALL, $result);
        return $result;
    }

    public function controllerUrl() {
        $this->resolve();
        return $this->controller_url;
    }

    public function actionUrl() {
        $this->resolve();
        return $this->action_url;
    }

}