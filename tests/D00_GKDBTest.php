<?php

class GKDBTest extends GKTestCase {
    public function setUp() {
        parent::setUp();
        $this->assumeTestDatabase();
    }

    public function test_should_get_database_link() {
        // GIVEN
        // WHEN
        $database = DBConnect();

        // THEN
        $this->assertTrue(($database == true));
        $this->assertSingleConnectCount();
    }

    public function test_should_get_working_database_query() {
        // GIVEN
        $database = DBConnect();
        $sql = 'SELECT * FROM `gk-waypointy-type` LIMIT 50';

        // WHEN
        $result = mysqli_query($database, $sql);
        $row_cnt = $result->num_rows;
        mysqli_free_result($result);

        // THEN
        $this->assertSame($row_cnt, 50, 'expect to get 50 gk-waypointy-type');
        $this->assertSingleConnectCount();
    }

    public function test_should_get_single_database_link() {
        // GIVEN
        $database = DBConnect();
        $database = null;

        // WHEN
        $database = DBConnect();

        // THEN
        $this->assertTrue(($database == true));
        $this->assertSingleConnectCount();
    }

    public function test_should_reconnect_database_link() {
        // GIVEN
        $database = DBConnect();
        $database->close();

        // WHEN
        $database = DBConnect();

        // THEN
        $this->assertTrue(($database == true));
        $this->assertConnectCount(2);
    }

    private function assertSingleConnectCount() {
        $this->assertConnectCount(1);
    }

    private function assertConnectCount($expectedCount) {
        $this->assertSame(GKDB::getConnectCount(), $expectedCount, 'expect one db connect over the time');
    }
}
