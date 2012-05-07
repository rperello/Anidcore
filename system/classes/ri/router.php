<?php

class Ri_Router extends Ri_Environment {

    protected $controller_name = NULL;

    /**
     *
     * @var Ri_Controller 
     */
    protected $controller_instance = NULL;
    protected $action = NULL;
    protected $controller_url = NULL;
    protected $action_url = NULL;
    protected $resource = NULL;
    protected $params = NULL; //remaining resource from action
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

    public function segment($index, $default = false, $filter = NULL) {
        $this->resolve();
        return ri_arr_value($this->resource, $index, $default, $filter);
    }

    public function resolve() {
        //Yet initialized
        if ($this->controller_name != NULL)
            return;

        $this->context()->hookApply("ri.before.router_resolve", $this);

        if (empty($this->canonical_segments)) {
            $this->canonical_segments = array(trim($this->context()->request->baseUri(), " /"));
        }

        $rs = $this->context()->request->resource;
        $this->resource = $rs = empty($rs) ? array() : explode("/", trim($rs, " /"));

        $rs = $this->context()->hookApply("ri.on.router_resource", $rs);

        //Defaults
        $default_app_controller = ucfirst($this->context()->config("router.default_controller", "index"));
        $this->controller_name = ucfirst($default_app_controller);
        $this->action = "__default";


        if (empty($rs)) {
            $this->controller_url = $this->action_url = $this->canonicalUrl();
        } else {

            //CONTROLLER
            $part = strtolower(ri_str_slug($rs[0], "_"));
            if ($part == $default_app_controller) {
                //prevent access to main app controller using /$default_app_controller/ segment
                $this->action = "__handle";
                $this->params = $rs;
                $this->context()->hookApply("ri.on.router_resolve", $this);
                return;
            }

            $controller_name = ucfirst($part); //Example: Pages
            $controller_exists = $this->context()->autoload($this->controllerClassName($controller_name));

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
                if (!$this->context()->autoload($this->controllerClassName($default_app_controller))) {
                    $this->context()->log()->fatal($default_app_controller, "The controller cannot be loaded", __FILE__, __LINE__);
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
                $part = strtolower(ri_str_slug($rs[0], "_"));
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

        $this->context()->hookApply("ri.on.router_resolve", $this);
    }

    public function controllerClassName($controllerName) {
        return "Controller_" . $controllerName;
    }

    public function call() {
        $this->context()->hookApply("ri.before.router_call", $this);
        $klass = $this->controllerClassName($this->controller_name);
        $fn = $this->action;
        $result = NULL;

        if ($fn == "__default")
            $validate_fn = "validate_default";
        elseif ($fn == "__handle")
            $validate_fn = "validate_handle";
        else
            $validate_fn = str_replace("action_", "validate_", $this->action);

        $this->controller_instance = new $klass($this->context()->name);
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
        $result = $this->context()->hookApply("ri.on.router_call", $result);
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