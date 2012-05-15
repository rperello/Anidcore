<?php
function __t($string, $lang = null, $realm = null) {
    return $string;
}
class Module_I18n extends Ac_Module {

    public function __construct() {
        Ac::on("Ac_Router_before_resolve", array($this, "routerBeforeResolve"));
        parent::__construct('i18n', array(
            'default_language' => "en",
            "available_languages" => array('en'),
        ));
    }

    public function routerBeforeResolve($request = "") {
        $directoryUrl = $request["directoryUrl"];
        $rs = empty($request["resource"]) ? array() : explode("/", trim($request["resource"], " /"));
        if (empty($rs)) {
            $this->langRedirect($this->langFromBrowser());
        } else {
            $lang = array_shift($rs);
            if (!in_array($lang, $this->config("available_languages"))) {
                $this->langRedirect($this->langFromBrowser());
            }
            $directoryUrl.=$lang . "/";
        }
        return array("directoryUrl" => $directoryUrl, "resource" => implode('/', $rs));
    }

    public function langRedirect($lang) {
        Ac::redirect(Ac::request()->directoryUrl() . $lang . "/");
    }

    public function langFromBrowser() {
        $browser_langs = explode(",", Ac::request()->languages());
        foreach ($browser_langs as $i => $lang) {
            if (in_array($lang, $this->config("available_languages"))) {
                return $lang;
            }
        }
        return $this->config("default_language", 'en');
    }

}