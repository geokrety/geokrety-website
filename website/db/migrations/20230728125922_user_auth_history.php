<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UserAuthHistory extends AbstractMigration {
    public function up(): void {
        $table_authentication_history = $this->table('geokrety.gk_users_authentication_history', ['id' => false, 'primary_key' => 'id']);
        $table_authentication_history->addColumn('id', 'biginteger', ['null' => false, 'identity' => true])
            ->addColumn('created_on_datetime', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => '', 'timezone' => true])
            ->addColumn('updated_on_datetime', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP', 'timezone' => true])
            ->addColumn('user', 'biginteger', ['null' => true])
            ->addColumn('username', 'text', ['null' => true])
            ->addColumn('user_agent', 'text', ['null' => true])
            ->addColumn('ip', 'inet', ['null' => false])
            ->addColumn('succeed', 'boolean', ['null' => false])
            ->addColumn('session', 'string', ['null' => false])
            ->addColumn('comment', 'string', ['null' => true])
            ->addForeignKey('user', 'gk_users', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->addIndex(['username'])
            ->addIndex(['succeed'])
            ->addIndex(['username', 'succeed'])
            ->create();

        $this->execute('CREATE TYPE authentication_method AS ENUM (\'password\', \'secid\', \'devel\', \'oauth\', \'registration.activate\', \'registration.oauth\', \'api2secid\', \'google\')');
        $this->execute('ALTER TABLE geokrety.gk_users_authentication_history ADD COLUMN method authentication_method NOT NULL');

        $sql = <<<'EOL'
CREATE OR REPLACE FUNCTION geokrety.previous_failed_logins(
	user_name text)
    RETURNS SETOF gk_users_authentication_history
    LANGUAGE 'plpgsql'
    COST 100
    VOLATILE PARALLEL UNSAFE
    ROWS 1000

AS $BODY$
DECLARE
    last_auth bigint;
BEGIN

-- Get last authentication result
SELECT CAST(CAST(succeed AS INT) AS BIGINT)
FROM geokrety.gk_users_authentication_history AS uah
WHERE uah."username" = user_name
ORDER BY "created_on_datetime" DESC
LIMIT 1
INTO last_auth;

-- Find previously failed attempts
RETURN QUERY WITH user_attempts AS (
    SELECT *
    FROM geokrety.gk_users_authentication_history AS uah
    WHERE uah."username" = user_name
    ORDER BY "created_on_datetime" DESC
    OFFSET last_auth
),
last_login AS (
    (
        SELECT created_on_datetime
        FROM user_attempts
        WHERE succeed IS TRUE
        LIMIT 1
    )
    UNION ALL
    (
        SELECT '1970-01-01' AS created_on_datetime
    )
    ORDER BY created_on_datetime DESC
    LIMIT 1
)
SELECT *
FROM user_attempts AS ua
WHERE succeed IS FALSE
AND ua.created_on_datetime > (SELECT created_on_datetime FROM last_login);

END;
$BODY$;
EOL;
        $this->execute($sql);
    }

    public function down(): void {
        $this->execute('DROP FUNCTION IF EXISTS geokrety.previous_failed_logins(text)');
        $this->table('geokrety.gk_users_authentication_history')
            ->drop()
            ->save();
        $this->execute('DROP TYPE authentication_method');
    }
}
