<?php

namespace GeoKrety\Service;

class Smarty extends \Prefab {
    private $smarty;

    public static function getSmarty() {
        return Smarty::instance()->smarty;
    }

    public static function assign(string $variable, $value) {
        self::getSmarty()->assign($variable, $value);
    }

    public static function render(string $template) {
        $smarty = self::getSmarty();
        // $smarty->assign('alert_msgs', $f3['alert_msgs'] ?? array());
        $smarty->display($template);
    }

    public static function fetch(string $template) {
        $smarty = self::getSmarty();

        return $smarty->fetch($template);
    }

    public function __construct() {
        $smarty = new \SmartyBC();
        $smarty->escape_html = true;
        $smarty->addTemplateDir(GK_SMARTY_TEMPLATES_DIR, 'main');
        $smarty->addTemplateDir(GK_SMARTY_FOUNDATION_TEMPLATES_DIR, 'emails');
        $smarty->compile_dir = GK_SMARTY_COMPILE_DIR;
        $smarty->cache_dir = GK_SMARTY_CACHE_DIR;
        $smarty->addPluginsDir(GK_SMARTY_PLUGINS_DIR);
        $smarty->compile_check = !GK_IS_PRODUCTION;
        $smarty->assign('f3', \Base::instance());
        $smarty->assign('css', array()); // Store dynamic css filename to load
        $smarty->assign('javascript', array()); // Store dynamic javascript filename to load
        $smarty->assign('isSuperUser', false);
        $smarty->registerClass('Carbon', '\Carbon\Carbon');
        $smarty->caching = 0; // caching is off

        $this->smarty = $smarty;
    }
}
