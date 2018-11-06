<?php
/**
 * @SuppressWarnings(PHPMD)
 */
class TestUtil {
    public function isValidHtmlContent($htmlExtract) {
        try {
            $doc = new DOMDocument();

            return $doc->loadHTML('<html><body>'.$htmlExtract.'</body></html>');
        } catch (Exception $e) {
            echo "\n\nInvalid HTML:\n",$e->getMessage(),"\n\n",$htmlExtract,"\n\n";

            return false;
        }
    }
}
