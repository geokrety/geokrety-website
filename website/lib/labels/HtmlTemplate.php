<?php

abstract class HtmlTemplate extends Template {
    public function generate($gkId, $trackingCode, $gkName, $owner, $comment = '', $languages = ['en']) {
        include_once SMARTY_DIR.'Smarty.class.php';

        $smarty = new Smarty();
        $smarty->template_dir = './';
        $smarty->compile_dir = '../compile/';
        $smarty->cache_dir = '../cache/';

        $smarty->error_reporting = E_ALL;

        $smarty->assign('help', implode('<br><br>', $this->getManuals($languages)));
        $smarty->assign('szablon', '/lib/labels/'.$this->getId());
        $smarty->assign('nazwa', stripcslashes($gkName));
        $smarty->assign('id', $gkId);
        $smarty->assign('owner', $owner);
        $smarty->assign('tracking', $trackingCode);
        $smarty->assign('opis', stripcslashes(strip_tags($comment, '<img>')));
        $smarty->assign('szablon_css', '/lib/labels/'.$this->getId().'/label.css');

        $smarty->display(__DIR__.'/'.$this->getId().'/label.html');
    }
}
