<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class LogTypeMissing extends AbstractMigration {
    public function up(): void {
        $this->execute(<<<'SQL'
CREATE OR REPLACE FUNCTION geokrety.moves_types_markable_as_missing()
    RETURNS smallint[]
    LANGUAGE 'sql'
    VOLATILE
    PARALLEL UNSAFE
    COST 100

AS $BODY$
SELECT '{0,1,3,5}'::smallint[]
$BODY$;
SQL
        );
    }

    public function down(): void {
        $this->execute(<<<'SQL'
CREATE OR REPLACE FUNCTION geokrety.moves_types_markable_as_missing()
    RETURNS smallint[]
    LANGUAGE 'sql'
    VOLATILE
    PARALLEL UNSAFE
    COST 100

AS $BODY$
SELECT '{0,3}'::smallint[]
$BODY$;
SQL
        );
    }
}
