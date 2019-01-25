<?php

/**
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
class CreditsTest extends GKTestCase {
    public function test_generate_credits() {
        // GIVEN
        include 'website/templates/konfig-credits.php';
        $credits = new \Geokrety\View\Credits($config['gk_credits']);

        // WHEN
        $creditsCount = $credits->count();
        $creditsDivs = $credits->toHtmlDivs();

        // THEN
        $testUtil = new TestUtil();
        $this->assertGreaterThan(0, $creditsCount, 'please define one or more credits into konfig-credits');

        $this->assertNotNull($creditsDivs);
        $this->assertTrue($testUtil->isValidHtmlContent($creditsDivs), 'credits divs invalid html content');
    }
}
