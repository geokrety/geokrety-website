<?php

/**
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
class SocialGroupsTest extends GKTestCase {
    public function test_generate_social_groups() {
        // GIVEN
        include 'website/templates/konfig-groups.php';
        $socialGroups = new \Geokrety\View\SocialGroups($config['gk_social_groups']);

        // WHEN
        $groupsCount = $socialGroups->count();
        $groupsTable = $socialGroups->toHtmlTable();

        // THEN
        $testUtil = new TestUtil();
        $this->assertGreaterThan(0, $groupsCount, 'please define one or more social groups into konfig-groups');

        $this->assertNotNull($groupsTable);
        $this->assertTrue($testUtil->isValidHtmlContent($groupsTable), 'social groups table invalid html content');
    }
}
