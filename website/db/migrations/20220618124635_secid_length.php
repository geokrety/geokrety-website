<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class SecidLength extends AbstractMigration {
    public function up(): void {
        $sql = <<<'EOL'
CREATE OR REPLACE FUNCTION geokrety.generate_secid(IN size integer DEFAULT 128)
    RETURNS character varying
    LANGUAGE 'sql'
    VOLATILE
    PARALLEL UNSAFE
    COST 100

AS $BODY$SELECT array_to_string(array(select substr('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz',((random()*(62-1)+1)::integer),1) from generate_series(1,size)),'');$BODY$;
EOL;
        $this->execute($sql);
    }

    public function down(): void {
        $sql = <<<'EOL'
CREATE OR REPLACE FUNCTION geokrety.generate_secid(IN size integer DEFAULT 42)
    RETURNS character varying
    LANGUAGE 'sql'
    VOLATILE
    PARALLEL UNSAFE
    COST 100

AS $BODY$SELECT array_to_string(array(select substr('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz',((random()*(62-1)+1)::integer),1) from generate_series(1,size)),'');$BODY$;
EOL;
        $this->execute($sql);
    }
}
