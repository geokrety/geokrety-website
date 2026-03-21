<?php

namespace Test\Unit\App\GeoKrety;

use GeoKrety\Model\Move;
use PHPUnit\Framework\TestCase;

class SmartyModifierCachelinkTest extends TestCase {
    private $extension;

    protected function setUp(): void {
        parent::setUp();
        $this->extension = new \SmartyGeokretyExtension();
    }

    /**
     * @test
     * Test that null move returns empty string
     */
    public function testCachelinkReturnsEmptyStringForNullMove() {
        $result = $this->extension->smarty_modifier_cachelink(null);

        $this->assertEquals('', $result);
    }
}
