<?php
/**
 * JSON-LD generator used: https://github.com/Torann/json-ld
 * example: https://www.google.com/webmasters/markup-helper/u/0/.
 */
class LDGeneratorFactory {
    public static function createLdContext($contextType, array $data = []) {
        return \JsonLd\Context::create($contextType, $data);
    }
}
