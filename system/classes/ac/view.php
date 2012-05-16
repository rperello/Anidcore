<?php

class Ac_View extends Ac_Object {

    protected static $level = 0;
    protected static $instance = NULL;

    public function __construct($vars = array()) {
        parent::__construct($vars);
    }

    /**
     * Ac_Object::properties alias
     * @param array $vars
     * @return array 
     */
    public function vars($vars = null) {
        return $this->properties($vars);
    }

    public function load($template, $options = array()) {
        $options = array_merge(array(
            "master" => "master.php",
            "path" => null
                ), $options);

        $path = $options["path"];

        $this->template_name = basename($template, '.php');
        if (empty($path))
            $path = Ac::path("templates");

        if (!is_dir($path))
            Ac::exception("The templates directory does not exist: " . $path);

        // To prevent wrong includes, we do a chdir
        chdir($path);

        if (is_readable($template)) {
            $file = $this->template_file = realpath($template);
        } else {
            $file = $this->template_file = $path . $template;
        }

        if ($options["master"] != NULL) {
            if (is_readable($options["master"])) {
                $file = realpath($options["master"]);
            } else {
                $file = $path . $options["master"];
            }
        }

        set_include_path(
                get_include_path() . PATH_SEPARATOR .
                $path . PATH_SEPARATOR
        );

        $content = static::process($file, $this->vars());
        chdir(AC_PATH);

        return $content;
    }

    /**
     *
     * @return Ac_View
     */
    public static function getInstance() {
        if (static::$instance == NULL) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public static function getLevel() {
        return static::$level; // (if == 0) we are not inside a view
    }

    /**
     * Process a template and return its content
     * @param string $template Template filename
     * @param array $vars Variables passed to the template
     * @return string|false The template contents
     */
    public static function process($template, array $vars = array()) {
        static::$level++;
        extract($vars, EXTR_OVERWRITE);
        ob_start();
        try {
            include $template;
            $content = ob_get_contents();
            ob_end_clean();
            static::$level--;
        } catch (Exception $e) {
            Ac::log()->error($e->getMessage(), "Ac_View error " . $e->getCode(), $e->getFile(), $e->getLine());
            $content = false;
            @ob_end_clean();
            static::$level--;
        }
        return $content;
    }

    /**
     * Parses a string and replaces :{%FUNCNAME(param1,param2,...)%} tags for the function result,
     * considering that FUNCNAME is the callable name of the function.
     * The call can have parameters without quotes.
     * Internally the 'call_user_func_array' function is used.
     * 
     * @param string $content 
     */
    public static function parse($content) {
        preg_match_all("/\:\{\%(.+)\((.*)\)\%\}/", $content, $matches);
        if (empty($matches) || (count($matches) != 3))
            return $content;

        $functions = $matches[1];
        $params = $matches[2];

        if (empty($functions))
            return $content;

        $filters = array();
        $replacements = array();
        foreach ($functions as $i => $func) {
            if (is_function($func)) {
                $filters[] = ":{%$func(" . $params[$i] . ")%}";
                if (!empty($params[$i])) {
                    $func_params = explode(",", $params[$i]);
                    array_walk($func_params, "trim");
                } else {
                    $func_params = array();
                }
                $replacements[] = call_user_func_array($func, $func_params);
            }
        }
        return str_replace($filters, $replacements, $content);
    }

}