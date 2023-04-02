<?php

use Phinx\Migration\AbstractMigration;

class SetDefaultSchema extends AbstractMigration {
    public function up() {
        $this->execute('ALTER DATABASE "'.GK_DB_NAME.'" SET SEARCH_PATH TO geokrety;');
    }

    public function down() {
        $this->execute('ALTER DATABASE "'.GK_DB_NAME.'" SET SEARCH_PATH TO geokrety;');
    }
}
