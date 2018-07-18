<?php

use PHPUnit\Framework\TestCase;

class SocialGroupsTest extends TestCase {
    public function isValidHtmlContent($htmlExtract) {
        try {
            $doc = new DOMDocument();

            return $doc->loadHTML('<html><body>'.$htmlExtract.'</body></html>');
        } catch (Exception $e) {
            echo "\n\ninvalid HTML:\n",$e->getMessage(),"\n\n",$htmlExtract,"\n\n";

            return false;
        }
    }

    public function test_generate_social_groups() {
        // GIVEN
        require_once 'website/templates/konfig-groups.php';
        $socialGroupsConfig = $config['gk_social_groups'];
        $socialGroups = new \Geokrety\View\SocialGroups($socialGroupsConfig);

        // WHEN
        $groupsCount = $socialGroups->count();
        $groupsTable = $socialGroups->toHtmlTable();

        // THEN
        $this->assertGreaterThan(0, $groupsCount, 'please define one or more social groups into konfig-groups');

        $this->assertNotNull($groupsTable);
        $this->assertTrue($this->isValidHtmlContent($groupsTable), 'social groups table: must be valid html content');
    }
}
