<?php

use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
class CreditsTest extends TestCase {
    public function test_generate_credits() {
        // GIVEN
        define('CONFIG_CDN_LOGOS', 'https://cdn.geokrety.org/images/logos/');
        require_once 'website/templates/konfig-credits.php';
        $credits = new \Geokrety\View\Credits($config['gk_credits']);

        // WHEN
        $creditsCount = $credits->count();
        $creditsDivs = $credits->toHtmlDivs();

        // THEN
        $testUtil = new TestUtil();
        $this->assertGreaterThan(0, $creditsCount, 'please define one or more credits into konfig-credits');

        $this->assertNotNull($creditsDivs);
        $this->assertTrue($testUtil->isValidHtmlContent($creditsDivs), 'credits divs: must be valid html content');
    }
}
