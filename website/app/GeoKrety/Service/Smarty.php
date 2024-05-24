<?php

namespace GeoKrety\Service;

class Smarty extends \Prefab {
    private \Smarty\Smarty $smarty;

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

    /**
     * @param false|string $template
     *
     * @return string
     *
     * @throws \SmartyException
     */
    public static function fetch(string $template) {
        $smarty = self::getSmarty();

        return $smarty->fetch($template);
    }

    public function __construct() {
        $smarty = new \Smarty\Smarty();
        $smarty->escape_html = true;
        $smarty->setTemplateDir([
            'main' => GK_SMARTY_TEMPLATES_DIR,
        ]);
        $smarty->setCompileDir(GK_SMARTY_COMPILE_DIR);
        $smarty->setCacheDir(GK_SMARTY_CACHE_DIR);
        $smarty->registerPlugin(\Smarty\Smarty::PLUGIN_BLOCK, 't', 'smarty_block_t');
        $smarty->registerPlugin(\Smarty\Smarty::PLUGIN_FUNCTION, 'fa', 'smarty_tag_fa');
        $smarty->compile_check = !GK_IS_PRODUCTION;
        $smarty->assign('f3', \Base::instance());
        $smarty->assign('css', []); // Store dynamic css filename to load
        $smarty->assign('javascript', []); // Store dynamic javascript filename to load
        $smarty->assign('isSuperUser', false);
        $smarty->registerClass('Carbon', '\Carbon\Carbon');
        $smarty->setCaching(\Smarty\Smarty::CACHING_OFF);
        $smarty->addExtension(new \SmartyCallablePassThroughExtension());
        $smarty->addExtension(new \SmartyGeokretyExtension());

        $this->smarty = $smarty;
    }
}
