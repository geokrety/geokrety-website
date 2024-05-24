<?php

class SmartyCallablePassThroughExtension extends Smarty\Extension\Base {
    public function getModifierCallback(string $modifierName) {
        if (is_callable($modifierName)) {
            return $modifierName;
        }

        return null;
    }
}
