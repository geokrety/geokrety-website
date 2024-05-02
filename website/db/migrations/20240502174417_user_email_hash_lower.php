<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UserEmailHashLower extends AbstractMigration {
    public function up(): void {
        $this->execute(<<<'EOL'
CREATE OR REPLACE FUNCTION geokrety.manage_email()
    RETURNS trigger
    LANGUAGE 'plpgsql'
    VOLATILE
    COST 100
AS $BODY$
BEGIN

IF OLD._email_crypt IS DISTINCT FROM NEW._email_crypt THEN
	RAISE '_email_crypt must not be manually updated';
END IF;
IF OLD._email_hash IS DISTINCT FROM NEW._email_hash THEN
	RAISE '_email_hash must not be manually updated';
END IF;

IF NEW._email IS NULL OR NEW._email = '' THEN
	NEW._email_crypt = NULL;
	NEW._email_hash = NULL;
ELSE
	NEW._email_crypt = gkencrypt(NEW._email::character varying);
	NEW._email_hash = public.digest(lower(NEW._email::character varying), 'sha256');
END IF;

-- Ensure email field is always NULL
NEW._email = NULL;

RETURN NEW;
END;
$BODY$;
EOL);
    }

    public function down() {
        $this->execute(<<<'EOL'
        CREATE OR REPLACE FUNCTION geokrety.manage_email()
            RETURNS trigger
            LANGUAGE 'plpgsql'
            VOLATILE
            COST 100
        AS $BODY$
        BEGIN

        IF OLD._email_crypt IS DISTINCT FROM NEW._email_crypt THEN
            RAISE '_email_crypt must not be manually updated';
        END IF;
        IF OLD._email_hash IS DISTINCT FROM NEW._email_hash THEN
            RAISE '_email_hash must not be manually updated';
        END IF;

        IF NEW._email IS NULL OR NEW._email = '' THEN
            NEW._email_crypt = NULL;
            NEW._email_hash = NULL;
        ELSE
            NEW._email_crypt = gkencrypt(NEW._email::character varying);
            NEW._email_hash = public.digest(NEW._email::character varying, 'sha256');
        END IF;

        -- Ensure email field is always NULL
        NEW._email = NULL;

        RETURN NEW;
        END;
        $BODY$;
EOL);
    }
}
