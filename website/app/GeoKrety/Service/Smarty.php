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

    public function __construct() {
        $smarty = new \SmartyBC();
        $smarty->escape_html = true;
        $smarty->template_dir = GK_SMARTY_TEMPLATES_DIR;
        $smarty->compile_dir = GK_SMARTY_COMPILE_DIR;
        $smarty->cache_dir = GK_SMARTY_CACHE_DIR;
        $smarty->addPluginsDir(GK_SMARTY_PLUGINS_DIR);
        $smarty->compile_check = true;
        $smarty->assign('f3', \Base::instance());
        // if (amIOnProd()) {
        //     $smarty->compile_check = false; // use smarty_admin.php to clear compiled templates when necessary - http://www.smarty.net/docsv2/en/variable.compile.check.tpl
        // } else {
        //     $smarty->compile_check = true;
        // }
        $smarty->assign('content_template', false); // Store included template name
        $smarty->assign('css', array()); // Store dynamic css filename to load
        $smarty->assign('javascript', array()); // Store dynamic javascript filename to load
        $smarty->assign('js_template', array()); // Store dynamic javascript filename to load as javascript content
        $smarty->assign('jquery', array()); // Store page jquery
        $smarty->assign('isSuperUser', false);
        $smarty->registerClass('Carbon', '\Carbon\Carbon');
        $smarty->caching = 0; // caching is off
        // TODO REMOVE THAT
        $smarty->clear_all_cache();
        $smarty->clear_compiled_tpl();

        $this->smarty = $smarty;
    }
}
