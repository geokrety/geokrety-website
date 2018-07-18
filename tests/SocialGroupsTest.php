<?php

use PHPUnit\Framework\TestCase;

class SocialGroupsTest extends TestCase {
    public function test_generate_social_groups() {
        // GIVEN
        require_once 'website/templates/konfig-groups.php';
        $socialGroups = new \Geokrety\View\SocialGroups($config['gk_social_groups']);

        // WHEN
        $groupsCount = $socialGroups->count();
        $groupsTable = $socialGroups->toHtmlTable();

        // THEN
        $this->assertGreaterThan(0, $groupsCount, 'please define one or more social groups into konfig-groups');
    }
}
