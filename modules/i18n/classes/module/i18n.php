<?php

class Module_I18n extends Ac_Module {

    public function __construct() {
        parent::__construct('i18n', array(
            'default_language' => "en",
            "available_languages" => array('en'),
        ));
        Ac::hookRegister(Ac::HOOK_BEFORE_ROUTER_RESOLVE, array($this, "onRouterResource"));
    }

    public function onRouterResource($request) {
        $baseUri = $request["baseUri"];
        $rs = empty($request["resource"]) ? array() : explode("/", trim($request["resource"], " /"));
        if (empty($rs)) {
            $this->langRedirect($this->langFromBrowser());
        } else {
            $lang = array_shift($rs);
            if (!in_array($lang, $this->config("available_languages"))) {
                $this->langRedirect($this->langFromBrowser());
            }
            $baseUri.=$lang . "/";
        }
        return array("baseUri" => $baseUri, "resource" => implode('/', $rs));
    }

    public function langRedirect($lang) {
        Ac::redirect(Ac::url('base') . $lang . "/");
    }

    public function langFromBrowser() {
        $browser_langs = explode(",", Ac::request()->languages);
        foreach ($browser_langs as $i => $lang) {
            if (in_array($lang, $this->config("available_languages"))) {
                return $lang;
            }
        }
        return $this->config("default_language", 'en');
    }

    public function init() {
        parent::init();
    }

}