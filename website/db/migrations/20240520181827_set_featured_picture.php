<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class SetFeaturedPicture extends AbstractMigration {
    public function up(): void {
        $this->execute(<<<'EOL'
CREATE FUNCTION geokrety.pictures_set_featured()
    RETURNS trigger
    LANGUAGE 'plpgsql'
    VOLATILE NOT LEAKPROOF
AS $BODY$
DECLARE
	PICTURE_GEOKRET_AVATAR bigint := 0;
	PICTURE_GEOKRET_MOVE bigint := 1;
	PICTURE_USER_AVATAR bigint := 2;
BEGIN

IF NEW.type = PICTURE_GEOKRET_AVATAR THEN
	UPDATE "gk_geokrety"
	SET "avatar" = NEW.id
	WHERE "id" = NEW.geokret
	AND "avatar" IS NULL;
ELSIF NEW.type = PICTURE_USER_AVATAR THEN
	UPDATE "gk_users"
	SET "avatar" = NEW.id
	WHERE "id" = NEW.user
	AND "avatar" IS NULL;
END IF;

-- No featured images for move pictures

RETURN NEW;
END;
$BODY$;

CREATE OR REPLACE TRIGGER after_20_set_featured_picture
    AFTER UPDATE OF uploaded_on_datetime
    ON geokrety.gk_pictures
    FOR EACH ROW
    EXECUTE FUNCTION geokrety.pictures_set_featured();
EOL
        );
    }

    public function down(): void {
        $this->execute(<<<'EOL'
DROP TRIGGER IF EXISTS after_20_set_featured_picture ON geokrety.gk_pictures;
DROP FUNCTION IF EXISTS geokrety.pictures_set_featured();
EOL
        );
    }
}
