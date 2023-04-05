<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class DeleteUser extends AbstractMigration {
    public function up(): void {
        $sql = <<<'EOL'
ALTER TABLE "gk_moves"
DROP CONSTRAINT "gk_moves_author_fkey",
DROP CONSTRAINT "gk_moves_geokret_fkey",
ADD FOREIGN KEY ("author") REFERENCES "gk_users" ("id") ON DELETE SET NULL ON UPDATE CASCADE,
ADD FOREIGN KEY ("geokret") REFERENCES "gk_geokrety" ("id") ON DELETE CASCADE ON UPDATE CASCADE
EOL;
        $this->execute($sql);

        $sql = <<<'EOL'
ALTER TABLE "gk_pictures"
DROP CONSTRAINT "gk_pictures_author_fkey",
DROP CONSTRAINT "gk_pictures_user_fkey",
DROP CONSTRAINT "gk_pictures_move_fkey",
DROP CONSTRAINT "gk_pictures_geokret_fkey",

ADD FOREIGN KEY ("author") REFERENCES "gk_users" ("id") ON DELETE SET NULL ON UPDATE CASCADE,
ADD FOREIGN KEY ("user") REFERENCES "gk_users" ("id") ON DELETE CASCADE ON UPDATE CASCADE,
ADD FOREIGN KEY ("move") REFERENCES "gk_moves" ("id") ON DELETE CASCADE ON UPDATE CASCADE,
ADD FOREIGN KEY ("geokret") REFERENCES "gk_geokrety" ("id") ON DELETE CASCADE ON UPDATE CASCADE
EOL;
        $this->execute($sql);

        $sql = <<<'EOL'
ALTER TABLE "gk_geokrety"
DROP CONSTRAINT "gk_geokrety_owner_fkey",
DROP CONSTRAINT "gk_geokrety_holder_fkey",
DROP CONSTRAINT "gk_geokrety_avatar_fkey",
ADD FOREIGN KEY ("owner") REFERENCES "gk_users" ("id") ON DELETE SET NULL ON UPDATE CASCADE,
ADD FOREIGN KEY ("holder") REFERENCES "gk_users" ("id") ON DELETE SET NULL ON UPDATE CASCADE,
ADD FOREIGN KEY ("avatar") REFERENCES "gk_pictures" ("id") ON DELETE SET NULL ON UPDATE CASCADE
EOL;
        $this->execute($sql);

        $sql = <<<'EOL'
CREATE FUNCTION user_delete_anonymize()
    RETURNS trigger
    LANGUAGE 'plpgsql'
    NOT LEAKPROOF
AS $BODY$
BEGIN

UPDATE gk_moves
SET username = 'Deleted user', author = NULL
WHERE author = OLD.id;

RETURN OLD;
END;
$BODY$;

COMMENT ON FUNCTION geokrety.user_delete_anonymize()
    IS 'Set username as deleted user.';
EOL;
        $this->execute($sql);

        $sql = <<<'EOL'
CREATE TRIGGER before_70_set_username_deleted
BEFORE DELETE ON gk_users
FOR EACH ROW EXECUTE FUNCTION user_delete_anonymize();
EOL;
        $this->execute($sql);

        $sql = <<<'EOL'
CREATE FUNCTION delete_user(IN user_id bigint, IN clear_comments boolean DEFAULT FALSE)
    RETURNS void
    LANGUAGE 'plpgsql'

AS $BODY$
BEGIN

IF clear_comments IS TRUE THEN
    UPDATE gk_moves
    SET comment='Comment suppressed'
    WHERE author = user_id;

    UPDATE gk_moves_comments
    SET content='Comment suppressed'
    WHERE author = user_id;
END IF;

DELETE FROM gk_users
WHERE id = user_id;

END;
$BODY$;
EOL;
        $this->execute($sql);
    }

    public function down(): void {
        $this->execute('DROP TRIGGER before_70_set_username_deleted ON gk_users;');
        $this->execute('DROP FUNCTION user_delete_anonymize;');
        $this->execute('DROP FUNCTION delete_user;');
    }
}
