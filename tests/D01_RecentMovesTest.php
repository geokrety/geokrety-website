<?php

class RecentMovesTest extends GKTestCase {
    public function setUp() {
        parent::setUp();
        $this->assumeTestDatabase();
    }

    public function test_recent_moves() {
        // GIVEN
        include_once 'website/recent_moves.php';

        // WHEN
        $result = recent_moves('', 50, '', '', true);

        // THEN
        $testUtil = new TestUtil();
        $this->assertNotNull($result);
        $this->assertTrue($testUtil->isValidHtmlContent($result), 'recent moves invalid html content');
    }
}
