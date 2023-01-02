<?php

// Framework bootstrap code here
use GeoKrety\PictureType;
use GeoKrety\Service\ConsoleWriter;
use GeoKrety\Service\LanguageService;
use GeoKrety\Service\Markdown;
use GeoKrety\Service\SecretCode;

$dsn = 'mysql:host=db;dbname=prod';
$username = getenv('GK_DB_ORIG_USER');
$password = getenv('GK_DB_ORIG_PASSWORD');
$options = [
    \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4;',
];

$mysql = new PDO($dsn, $username, $password, $options);

require '../init-f3.php';

// Get PDO object
$pgsql = $f3->get('DB')->pdo();

echo 'Disable replication'.PHP_EOL;
$pgsql->query('SET session_replication_role = replica;');

define('DEFAULT_PAGINATION', 1000);

try {
    $pgsql->query('DROP INDEX gk_moves_country_index;');
    $pgsql->query('DROP INDEX gk_moves_type_index;');
    $pgsql->query('DROP INDEX id_type_position;');
    $pgsql->query('DROP INDEX idx_21034_kret_id;');
    $pgsql->query('DROP INDEX idx_21034_ruch_id;');
    $pgsql->query('DROP INDEX idx_21034_user_id;');
    $pgsql->query('DROP INDEX idx_21044_alt;');
    $pgsql->query('DROP INDEX idx_21044_data;');
    $pgsql->query('DROP INDEX idx_21044_data_dodania;');
    $pgsql->query('DROP INDEX idx_21044_lat;');
    $pgsql->query('DROP INDEX idx_21044_lon;');
    $pgsql->query('DROP INDEX idx_21044_timestamp;');
    $pgsql->query('DROP INDEX idx_21044_user;');
    $pgsql->query('DROP INDEX idx_21044_waypoint;');
    $pgsql->query('DROP INDEX idx_moves_geokret;');
    $pgsql->query('DROP INDEX idx_moves_id;');
    $pgsql->query('DROP INDEX idx_moves_type_id;');
} catch (Exception $e) {
}

echo 'Saving pictures table'.PHP_EOL;
$pgsql->query('DROP TABLE IF EXISTS gk_pictures2;');
$pgsql->query('CREATE TABLE gk_pictures2 AS SELECT filename, bucket, key FROM gk_pictures;');
$pgsql->query('CREATE INDEX tmp_idx_pictures_filename ON geokrety.gk_pictures2 USING btree (filename);');

echo 'Truncating tables'.PHP_EOL;
$sql = 'TRUNCATE "gk_users_settings", "gk_users_settings_parameters", "gk_waypoints_gc", "gk_statistics_counters", "gk_statistics_daily_counters", "gk_account_activation", "gk_awards_won", "gk_email_activation", "gk_geokrety", "gk_geokrety_rating", "gk_mails", "gk_moves_comments", "gk_moves", "gk_news", "gk_news_comments", "gk_news_comments_access", "gk_owner_codes", "gk_password_tokens", "gk_pictures", "gk_races", "gk_races_participants", "gk_users", "gk_watched", "gk_waypoints_country", "gk_waypoints_types", "scripts", "gk_yearly_ranking" RESTART IDENTITY CASCADE';
$pgsql->query($sql);

echo 'Start import data'.PHP_EOL;
// ---------------------------------------------------------------------------------------------------------------------
$mName = 'gk-waypointy-gc';
$pName = 'gk_waypoints_gc';
$mFields = ['wpt', 'country', 'alt', 'lon', 'lat'];
$pFields = ['waypoint', 'country', 'elevation', 'position'];
$migrator = new WaypointGCMigrator($mysql, $pgsql, $mName, $pName, $mFields, $pFields);
$migrator->process();

// ---------------------------------------------------------------------------------------------------------------------
$mName = 'gk-waypointy-country';
$pName = 'gk_waypoints_country';
$mFields = ['kraj', 'country'];
$pFields = ['original', 'country'];
$migrator = new BaseMigrator($mysql, $pgsql, $mName, $pName, $mFields, $pFields);
$migrator->process();

// // ---------------------------------------------------------------------------------------------------------------------
// $mName = 'gk-waypointy-sync';
// $pName = 'gk_waypoints_sync';
// $mFields = ['service_id', 'last_update'];
// $pFields = ['service_id', 'revision'];
// $migrator = new BaseMigrator($mysql, $pgsql, $mName, $pName, $mFields, $pFields);
// $migrator->process();

// ---------------------------------------------------------------------------------------------------------------------
$mName = 'gk-waypointy-type';
$pName = 'gk_waypoints_types';
$mFields = ['typ', 'cache_type'];
$pFields = ['type', 'cache_type'];
$migrator = new BaseMigrator($mysql, $pgsql, $mName, $pName, $mFields, $pFields);
$migrator->process();

// ---------------------------------------------------------------------------------------------------------------------
// Skip migrating this table, it's now fully populated using cron scripts
// $mName = 'gk-waypointy';
// $pName = 'gk_waypoints_oc';
// $mFields = ['waypoint', 'lat', 'lon', 'alt', 'country', 'name', 'owner', 'typ', 'kraj', 'link', 'status', 'timestamp'];
// $pFields = ['waypoint', 'lat', 'lon', 'elevation', 'country', 'name', 'owner', 'type', 'country_name', 'link', 'status', 'added_on_datetime'];
// $migrator = new WaypointMigrator($mysql, $pgsql, $mName, $pName, $mFields, $pFields);
// $migrator->process();

// ---------------------------------------------------------------------------------------------------------------------
$mName = 'gk-users';
$pName = 'gk_users';
$mFields = ['userid', 'user', 'haslo2', 'email', 'email_invalid', 'joined', 'timestamp', 'wysylacmaile', 'ip', 'lang', 'lat', 'lon', 'promien', 'country', 'godzina', 'statpic', 'ostatni_mail', 'ostatni_login', 'secid'];
$pFields = ['id', 'username', 'password', '_email', 'email_invalid', 'joined_on_datetime', 'updated_on_datetime', 'daily_mails', 'registration_ip', 'preferred_language', 'home_latitude', 'home_longitude', 'observation_area', 'home_country', 'daily_mails_hour', 'statpic_template', 'last_mail_datetime', 'last_login_datetime', '_secid', 'account_valid'];
// // , 'pictures_count', 'avatar', 'terms_of_use_datetime', 'account_valid'
$migrator = new UserMigrator($mysql, $pgsql, $mName, $pName, $mFields, $pFields);
$migrator->process();

// ---------------------------------------------------------------------------------------------------------------------
$mName = 'gk-news';
$pName = 'gk_news';
$mFields = ['news_id', 'date', 'tytul', 'tresc', 'who', 'userid', 'komentarze', 'ostatni_komentarz'];
$pFields = ['id', 'created_on_datetime', 'title', 'content', 'author_name', 'author', 'comments_count', 'last_commented_on_datetime'];
$migrator = new NewsMigrator($mysql, $pgsql, $mName, $pName, $mFields, $pFields);
$migrator->process();

// ---------------------------------------------------------------------------------------------------------------------
$mName = 'gk-news-comments';
$pName = 'gk_news_comments';
$mFields = ['comment_id', 'news_id', 'user_id', 'date', 'comment'];
$pFields = ['id', 'news', 'author', 'created_on_datetime', 'content', 'updated_on_datetime'];
$migrator = new NewsCommentsMigrator($mysql, $pgsql, $mName, $pName, $mFields, $pFields);
$migrator->process();

// ---------------------------------------------------------------------------------------------------------------------
$mName = 'gk-news-comments-access';
$pName = 'gk_news_comments_access';
$mFields = ['news_id', 'user_id', 'read', 'subscribed'];
$pFields = ['news', 'author', 'last_read_datetime', 'subscribed'];
$migrator = new NewsCommentsAccessMigrator($mysql, $pgsql, $mName, $pName, $mFields, $pFields);
$migrator->process();

// ---------------------------------------------------------------------------------------------------------------------
$mName = 'gk-badges';
$pName = 'gk_awards_won';
$mFields = ['userid', 'timestamp', 'desc', 'file'];
$pFields = ['holder', 'awarded_on_datetime', 'description', 'award', 'updated_on_datetime'];
$migrator = new BadgesMigrator($mysql, $pgsql, $mName, $pName, $mFields, $pFields);
$migrator->process();

// ---------------------------------------------------------------------------------------------------------------------
$mName = 'gk-geokrety';
$pName = 'gk_geokrety';
$mFields = ['id', 'nr', 'nazwa', 'opis', 'owner', 'data', 'droga', 'skrzynki', 'zdjecia', 'ost_pozycja_id', 'ost_log_id', 'hands_of', 'missing', 'typ', 'avatarid', 'timestamp'];
$pFields = ['gkid', 'tracking_code', 'name', 'mission', 'owner', 'created_on_datetime', 'distance', 'caches_count', 'pictures_count', 'last_position', 'last_log', 'holder', 'missing', 'type', 'avatar', 'updated_on_datetime'];
$migrator = new GeokretyMigrator($mysql, $pgsql, $mName, $pName, $mFields, $pFields);
$migrator->process();

// ---------------------------------------------------------------------------------------------------------------------
$mName = 'gk-geokrety-rating';
$pName = 'gk_geokrety_rating';
$mFields = ['id', 'userid', 'rate'];
$pFields = ['geokret', 'author', 'rate'];
$migrator = new GeokretyRatesMigrator($mysql, $pgsql, $mName, $pName, $mFields, $pFields);
$migrator->process();

// ---------------------------------------------------------------------------------------------------------------------
$mName = 'gk-maile';
$pName = 'gk_mails';
$mFields = ['random_string', 'from', 'to', 'temat', 'tresc', 'timestamp', 'ip'];
$pFields = ['token', 'from_user', 'to_user', 'subject', 'content', 'sent_on_datetime', 'ip'];
$migrator = new MailsMigrator($mysql, $pgsql, $mName, $pName, $mFields, $pFields);
$migrator->process();

// ---------------------------------------------------------------------------------------------------------------------
$mName = 'gk-races';
$pName = 'gk_races';
$mFields = ['raceid', 'created', 'raceOwner', 'private', 'haslo', 'raceTitle', 'racestart', 'raceend', 'opis', 'raceOpts', 'wpt', 'targetlat', 'targetlon', 'targetDist', 'targetCaches', 'status'];
$pFields = ['id', 'created_on_datetime', 'organizer', 'private', 'password', 'title', 'start_on_datetime', 'end_on_datetime', 'description', 'type', 'waypoint', 'target_lat', 'target_lon', 'target_dist', 'target_caches', 'status', 'updated_on_datetime'];
$migrator = new RacesMigrator($mysql, $pgsql, $mName, $pName, $mFields, $pFields);
$migrator->process();

// ---------------------------------------------------------------------------------------------------------------------
$mName = 'gk-races-krety';
$pName = 'gk_races_participants';
$mFields = ['raceGkId', 'raceid', 'geokretid', 'initDist', 'initCaches', 'distToDest', 'joined', 'finished', 'finishDist', 'finishCaches', 'finishLat', 'finishLon'];
$pFields = ['id', 'race', 'geokret', 'initial_distance', 'initial_caches_count', 'distance_to_destination', 'joined_on_datetime', 'finished_on_datetime', 'finish_distance', 'finish_caches_count', 'finish_lat', 'finish_lon', 'updated_on_datetime'];
$migrator = new RacesParticipantsMigrator($mysql, $pgsql, $mName, $pName, $mFields, $pFields);
$migrator->process();

// ---------------------------------------------------------------------------------------------------------------------
$mName = 'gk-owner-codes';
$pName = 'gk_owner_codes';
$mFields = ['id', 'kret_id', 'code', 'generated_date', 'claimed_date', 'user_id'];
$pFields = ['id', 'geokret', 'token', 'generated_on_datetime', 'claimed_on_datetime', 'adopter', 'validating_ip', 'used'];
$migrator = new OwnerCodesMigrator($mysql, $pgsql, $mName, $pName, $mFields, $pFields);
$migrator->process();

// ---------------------------------------------------------------------------------------------------------------------
$mName = 'gk-statystyki-dzienne';
$pName = 'gk_statistics_daily_counters';
$mFields = ['data', 'dzien', 'gk', 'gk_', 'gk_zakopane_', 'procent_zakopanych', 'users', 'users_', 'ruchow', 'ruchow_'];
$pFields = ['date', 'day_total', 'geokrety_created', 'geokrety_created_total', 'geokrety_in_caches', 'percentage_in_caches', 'users_registered', 'users_registered_total', 'moves_created', 'moves_created_total'];
$migrator = new BaseMigrator($mysql, $pgsql, $mName, $pName, $mFields, $pFields);
$migrator->process();

// ---------------------------------------------------------------------------------------------------------------------
$mName = 'gk-wartosci';
$pName = 'gk_statistics_counters';
$mFields = ['name', 'value'];
$pFields = ['name', 'value'];
$migrator = new BaseMigrator($mysql, $pgsql, $mName, $pName, $mFields, $pFields);
$migrator->process();

// ---------------------------------------------------------------------------------------------------------------------
$mName = 'gk-ruchy';
$pName = 'gk_moves';
$mFields = ['ruch_id', 'id', 'lat', 'lon', 'alt', 'country', 'droga', 'waypoint', 'data', 'data_dodania', 'user', 'koment', 'zdjecia', 'komentarze', 'logtype', 'username', 'timestamp', 'app', 'app_ver'];
$pFields = ['id', 'geokret', 'lat', 'lon', 'elevation', 'country', 'distance', 'waypoint', 'moved_on_datetime', 'created_on_datetime', 'author', 'comment', 'pictures_count', 'comments_count', 'move_type', 'username', 'updated_on_datetime', 'app', 'app_ver', 'position'];
$migrator = new MovesMigrator($mysql, $pgsql, $mName, $pName, $mFields, $pFields);
$migrator->process();

// ---------------------------------------------------------------------------------------------------------------------
$mName = 'gk-ruchy-comments';
$pName = 'gk_moves_comments';
$mFields = ['ruch_id', 'kret_id', 'user_id', 'data_dodania', 'comment', 'type', 'timestamp'];
$pFields = ['move', 'geokret', 'author', 'created_on_datetime', 'content', 'type', 'updated_on_datetime'];
$migrator = new MoveCommentsMigrator($mysql, $pgsql, $mName, $pName, $mFields, $pFields);
$migrator->process();

// ---------------------------------------------------------------------------------------------------------------------
$mName = 'gk-obrazki';
$pName = 'gk_pictures';
$mFields = ['obrazekid', 'typ', 'id', 'id_kreta', 'user', 'plik', 'opis', 'timestamp'];
$pFields = ['id', 'type', 'move', 'geokret', 'user', 'filename', 'caption', 'created_on_datetime', 'updated_on_datetime', 'uploaded_on_datetime', 'author', 'bucket', 'key'];
$migrator = new PicturesMigrator($mysql, $pgsql, $mName, $pName, $mFields, $pFields);
$migrator->process();

// ---------------------------------------------------------------------------------------------------------------------
$mName = 'gk-obserwable';
$pName = 'gk_watched';
$mFields = ['userid', 'id'];
$pFields = ['user', 'geokret'];
$migrator = new WatchedMigrator($mysql, $pgsql, $mName, $pName, $mFields, $pFields);
$migrator->process();

// // ---------------------------------------------------------------------------------------------------------------------
echo 'Finished data import'.PHP_EOL;

echo 'Resetting sequences'.PHP_EOL;
$pgsql->query("SELECT SETVAL('geokrety.account_activation_id_seq', COALESCE(MAX(id), 1) ) FROM geokrety.gk_account_activation;");
$pgsql->query("SELECT SETVAL('geokrety.badges_id_seq', COALESCE(MAX(id), 1) ) FROM geokrety.gk_awards_won;");
$pgsql->query("SELECT SETVAL('geokrety.email_activation_id_seq', COALESCE(MAX(id), 1) ) FROM geokrety.gk_email_activation;");
$pgsql->query("SELECT SETVAL('geokrety.geokrety_id_seq', COALESCE(MAX(id), 1) ) FROM geokrety.gk_geokrety;");
$pgsql->query("SELECT SETVAL('geokrety.geokrety_rating_id_seq', COALESCE(MAX(id), 1) ) FROM geokrety.gk_geokrety_rating;");
$pgsql->query("SELECT SETVAL('geokrety.gk_statistics_counters_id_seq', COALESCE(MAX(id), 1) ) FROM geokrety.gk_statistics_counters;");
$pgsql->query("SELECT SETVAL('geokrety.gk_statistics_daily_counters_id_seq', COALESCE(MAX(id), 1) ) FROM geokrety.gk_statistics_daily_counters;");
$pgsql->query("SELECT SETVAL('geokrety.mails_id_seq', COALESCE(MAX(id), 1) ) FROM geokrety.gk_mails;");
$pgsql->query("SELECT SETVAL('geokrety.move_comments_id_seq', COALESCE(MAX(id), 1) ) FROM geokrety.gk_moves_comments;");
$pgsql->query("SELECT SETVAL('geokrety.moves_id_seq', COALESCE(MAX(id), 1) ) FROM geokrety.gk_moves;");
$pgsql->query("SELECT SETVAL('geokrety.news_comments_access_id_seq', COALESCE(MAX(id), 1) ) FROM geokrety.gk_news_comments_access;");
$pgsql->query("SELECT SETVAL('geokrety.news_comments_id_seq', COALESCE(MAX(id), 1) ) FROM geokrety.gk_news_comments;");
$pgsql->query("SELECT SETVAL('geokrety.news_id_seq', COALESCE(MAX(id), 1) ) FROM geokrety.gk_news;");
$pgsql->query("SELECT SETVAL('geokrety.owner_codes_id_seq', COALESCE(MAX(id), 1) ) FROM geokrety.gk_owner_codes;");
$pgsql->query("SELECT SETVAL('geokrety.password_tokens_id_seq', COALESCE(MAX(id), 1) ) FROM geokrety.gk_password_tokens;");
$pgsql->query("SELECT SETVAL('geokrety.pictures_id_seq', COALESCE(MAX(id), 1) ) FROM geokrety.gk_pictures;");
$pgsql->query("SELECT SETVAL('geokrety.races_id_seq', COALESCE(MAX(id), 1) ) FROM geokrety.gk_races;");
$pgsql->query("SELECT SETVAL('geokrety.races_participants_id_seq', COALESCE(MAX(id), 1) ) FROM geokrety.gk_races_participants;");
$pgsql->query("SELECT SETVAL('geokrety.scripts_id_seq', COALESCE(MAX(id), 1) ) FROM geokrety.scripts;");
$pgsql->query("SELECT SETVAL('geokrety.users_id_seq', COALESCE(MAX(id), 1) ) FROM geokrety.gk_users;");
$pgsql->query("SELECT SETVAL('geokrety.watched_id_seq', COALESCE(MAX(id), 1) ) FROM geokrety.gk_watched;");
// $pgsql->query("SELECT SETVAL('geokrety.waypoints_oc_id_seq', COALESCE(MAX(id), 1) ) FROM geokrety.gk_waypoints_oc;");
// $pgsql->query("SELECT SETVAL('geokrety.waypoints_gc_id_seq', COALESCE(MAX(id), 1) ) FROM geokrety.gk_waypoints_gc;");

echo 'Extracting GC waypoints from moves'.PHP_EOL;
$pgsql->query('SELECT waypoints_gc_fill_from_moves();');

echo 'Reusing old pictures tables buckets/keys'.PHP_EOL;
$pgsql->query('UPDATE gk_pictures SET bucket = gkp2.bucket, "key" = gkp2.key FROM gk_pictures2 AS gkp2 WHERE gk_pictures.filename = gkp2.filename AND gkp2.bucket IS NOT NULL;');
$pgsql->query('DROP TABLE gk_pictures2;');

echo 'Re-enable replication'.PHP_EOL;
$pgsql->query('SET session_replication_role = DEFAULT;');

echo 'Re-creating indexes'.PHP_EOL;
$pgsql->query('CREATE INDEX gk_moves_country_index ON geokrety.gk_moves USING btree (country);');
$pgsql->query('CREATE INDEX gk_moves_type_index ON geokrety.gk_moves USING btree (move_type);');
$pgsql->query('CREATE INDEX id_type_position ON geokrety.gk_moves USING btree (move_type, id, "position");');
$pgsql->query('CREATE INDEX idx_21034_kret_id ON geokrety.gk_moves_comments USING btree (geokret);');
$pgsql->query('CREATE INDEX idx_21034_ruch_id ON geokrety.gk_moves_comments USING btree (move);');
$pgsql->query('CREATE INDEX idx_21034_user_id ON geokrety.gk_moves_comments USING btree (author);');
$pgsql->query('CREATE INDEX idx_21044_alt ON geokrety.gk_moves USING btree (elevation);');
$pgsql->query('CREATE INDEX idx_21044_data ON geokrety.gk_moves USING btree (created_on_datetime);');
$pgsql->query('CREATE INDEX idx_21044_data_dodania ON geokrety.gk_moves USING btree (moved_on_datetime);');
$pgsql->query('CREATE INDEX idx_21044_lat ON geokrety.gk_moves USING btree (lat);');
$pgsql->query('CREATE INDEX idx_21044_lon ON geokrety.gk_moves USING btree (lon);');
$pgsql->query('CREATE INDEX idx_21044_timestamp ON geokrety.gk_moves USING btree (updated_on_datetime);');
$pgsql->query('CREATE INDEX idx_21044_user ON geokrety.gk_moves USING btree (author);');
$pgsql->query('CREATE INDEX idx_21044_waypoint ON geokrety.gk_moves USING btree (waypoint);');
$pgsql->query('CREATE INDEX idx_moves_geokret ON geokrety.gk_moves USING btree (geokret);');
$pgsql->query('CREATE INDEX idx_moves_id ON geokrety.gk_moves USING btree (id);');
$pgsql->query('CREATE INDEX idx_moves_type_id ON geokrety.gk_moves USING btree (move_type, id);');

echo 'Refresh materialized views'.PHP_EOL;
$pgsql->query('REFRESH MATERIALIZED VIEW gk_geokrety_in_caches;');

class BaseMigrator {
    private $debug = false;
    private $mName;
    private $pName;
    protected $mPdo;
    protected $pPdo;

    private $mFields;
    protected $pFields;

    private $totalRecords;
    private $processedRecords = 0;
    private $totalPages;

    private $console_writer;
    protected $purifier;

    public function __construct(PDO $mPdo, PDO $pPdo, string $mName, string $pName, array $mFields, array $pFields) {
        $this->mName = $mName;
        $this->pName = $pName;
        $this->mPdo = $mPdo;
        $this->pPdo = $pPdo;
        $this->mFields = $mFields;
        $this->pFields = $pFields;

        array_walk($this->pFields, function (&$value) { $value = '"'.$value.'"'; });
        array_walk($this->mFields, function (&$value) { $value = '`'.$value.'`'; });

        if ($this->debug) {
            $sql = "TRUNCATE {$this->pName} RESTART IDENTITY CASCADE";
            $this->pPdo->query($sql);
        }
        $this->prepareData();

        $this->count();
        $this->purifier = GeoKrety\Service\HTMLPurifier::getPurifier();
        $this->console_writer = new ConsoleWriter("Importing {$this->pName}: %6.2f%% (%s/%d)");

//        $this->pPdo->query('SET session_replication_role = replica;');
    }

//    public function __destruct() {
//        $this->pPdo->query('SET session_replication_role = DEFAULT;');
//    }

    protected function prepareData() {
        // Empty
    }

    protected function postProcessData() {
        // Empty
    }

    private function count() {
        $sql = "SELECT count(*) FROM `{$this->mName}`";
        $stmt = $this->mPdo->query($sql);
        $this->totalRecords = $stmt->fetchColumn();
    }

    private function prepareSelect($size): PDOStatement {
        $fields = join(', ', $this->mFields);

        return $this->mPdo->prepare("SELECT $fields FROM `$this->mName` LIMIT :start, ".$size);
    }

    private function prepareInsert(int $chunkSize): PDOStatement {
        $fields = join(', ', $this->pFields);
        $sqlBase = "INSERT INTO {$this->pName} ($fields) VALUES ";
        $values = $this->prepareInsertValues($chunkSize);
        $sql = $sqlBase.$values;

        return $this->pPdo->prepare($sql);
    }

    protected function prepareInsertValues(int $chunkSize): string {
        $value = join(', ', array_fill(0, sizeof($this->pFields), '?'));

        return join(', ', array_fill(0, $chunkSize, "($value)"));
    }

    protected function cleanerHook(&$values) {
        array_walk($values, function (&$value) { $value = trim($value); });
    }

    public function process($paginate = DEFAULT_PAGINATION) {
        $select = $this->prepareSelect($paginate);
        $insert = $this->prepareInsert($paginate);

        $this->totalPages = ceil($this->totalRecords / $paginate);
        $firstpage = $this->totalPages - floor(($this->totalRecords - $this->processedRecords) / $paginate);

        for ($i = $firstpage; $i <= $this->totalPages; ++$i) {
            $select->bindParam(':start', $this->processedRecords, PDO::PARAM_INT);
            $select->execute();

            $results = $select->fetchAll(PDO::FETCH_NUM);
            $this->_process($insert, $results, $paginate);
        }
        $this->console_writer->flush();
        echo PHP_EOL;
        $this->postProcessData();
    }

    private function _process(&$insert, &$results, int $paginate) {
        $this->pPdo->beginTransaction();

        $chunkSize = sizeof($results);
        if (!$chunkSize) {
            $this->pPdo->rollBack();

            return;
        }

        if ($chunkSize < $paginate) {
            $insert = $this->prepareInsert($chunkSize);
            $paginate = $chunkSize;
        }
        $combine = [];
        foreach ($results as $line) {
            $this->cleanerHook($line);
            $combine = array_merge($combine, $line);
        }

        if ($insert->execute($combine) === false) {
            $this->pPdo->rollBack();
            if ($paginate > 1) {
                $newPaginate = ceil($paginate / 2);
                $this->process($newPaginate);
            }
            echo PHP_EOL;
            var_dump($results);
            echo join(', ', $this->pFields).PHP_EOL;
            echo join(', ', $combine).PHP_EOL;
            print_r($insert->errorInfo());
            exit();
        }

        $this->processedRecords += $chunkSize;
        $this->console_writer->print([$this->processedRecords / $this->totalRecords * 100, $this->processedRecords, $this->totalRecords]);
        $this->pPdo->commit();
    }
}

class WaypointMigrator extends BaseMigrator {
    protected function prepareData() {
        $this->mPdo->query("DELETE FROM `gk-waypointy` WHERE waypoint LIKE ' %' LIMIT 2");
        $this->mPdo->query('DELETE FROM `gk-waypointy` WHERE `lat` IS NULL OR `lon` IS NULL LIMIT 13');
    }

    protected function cleanerHook(&$values) {
        parent::cleanerHook($values);
        $values[1] = floatval($values[1]);
        $values[2] = floatval($values[2]);
    }
}

class UserMigrator extends BaseMigrator {
    protected function prepareData() {
        $this->mPdo->query('UPDATE `gk-users` SET joined = timestamp WHERE joined IS NULL LIMIT 3');
        // Enable functions as we need email and secid to be cyphered
        $this->pPdo->query('SET session_replication_role = DEFAULT;');
        $this->pPdo->query('ALTER TABLE geokrety.gk_users DISABLE TRIGGER after_99_notify_amqp;');
    }

    protected function postProcessData() {
        $this->pPdo->query('ALTER TABLE geokrety.gk_users ENABLE TRIGGER after_99_notify_amqp;');
        // Disable functions again
        $this->pPdo->query('SET session_replication_role = replica;');
        // Reset stat table as function will fill it
        $this->pPdo->query('TRUNCATE "gk_statistics_counters" RESTART IDENTITY CASCADE;');
    }

    protected function cleanerHook(&$values) {
//        parent::cleanerHook($values);
        $values[5] = $values[5] === '0000-00-00 00:00:00' ? null : $values[5];  // joined_on_datetime
        $values[16] = $values[16] === '0000-00-00 00:00:00' ? null : $values[16];  // last_mail_datetime
        $values[17] = $values[17] === '0000-00-00 00:00:00' ? null : $values[17];  // last_login_datetime

        $values[8] = filter_var($values[8], FILTER_VALIDATE_IP) ? $values[8] : '0.0.0.0';  // registration_ip

        $values[1] = html_entity_decode($this->purifier->purify($values[1]));  // username

        $values[2] = $values[2] ?: null;  // password
        $values[3] = filter_var($values[3], FILTER_VALIDATE_EMAIL) ? strtolower($values[3]) : null;  // email
        $values[9] = LanguageService::isLanguageSupported($values[9]) ? $values[9] : null;  // preferred_language
        $values[13] = $values[13] ? trim($values[13]) : null;  // home_country
        $values[18] = $values[18] ?: SecretCode::generateSecId();  // secid
        switch ($values[4]) { // email_invalid
          case 0:
            $values[4] = is_null($values[3]) ? 3 : 0; // USER_EMAIL_MISSING / USER_EMAIL_NO_ERROR
            break;
          case 1:
            $values[4] = 1; // USER_EMAIL_INVALID
            break;
        }
        $values[] = 2; // Account Imported
    }

    // TODO users avatar
    // TODO revalidate home_country
}

class NewsMigrator extends BaseMigrator {
    // 'id', 'created_on_datetime', 'title', 'content', 'author_name', 'author', 'comments_count', 'last_commented_on_datetime'
    protected function cleanerHook(&$values) {
        $values[4] = $values[4] ?: 'GK Team';  // author_name
        $values[5] = $values[5] ?: null;  // author
        $values[7] = $values[7] ?: null;  // last_commented_on_datetime
        $values[2] = Markdown::toFormattedMarkdown($values[2]);  // title
//        $values[3] = Markdown::toFormattedMarkdown($values[3]);  // content // TODO will need deeper migration path
    }

    // TODO: Recompute comments count
}

class NewsCommentsMigrator extends BaseMigrator {
    protected function postProcessData() {
        $this->pPdo->query('UPDATE gk_news_comments SET author=NULL WHERE author NOT IN (SELECT DISTINCT(id) FROM gk_users);');
    }

    // $pFields = ['id', 'news', 'author', 'content', 'created_on_datetime', 'updated_on_datetime'];
    protected function cleanerHook(&$values) {
        $values[3] = Markdown::toFormattedMarkdown($values[3]);  // content
        $values[5] = $values[3];  // updated_on_datetime
    }
}

class NewsCommentsAccessMigrator extends BaseMigrator {
    protected function prepareData() {
        $this->mPdo->query('DELETE FROM `gk-news-comments-access` WHERE user_id NOT IN (SELECT DISTINCT(userid) FROM `gk-users`);');
    }
}

class BadgesMigrator extends BaseMigrator {
    // M: 'userid', 'timestamp', 'desc', 'file']
    // P: 'holder', 'awarded_on_datetime', 'description', 'award', 'updated_on_datetime'
    protected function prepareData() {
        $this->mPdo->query('UPDATE `gk-badges` SET timestamp = "2019-12-30 23:25:00" WHERE id = 1056 LIMIT 1');
        $sql = <<<'EOL'
INSERT INTO "gk_awards_won" ("holder", "description", "awarded_on_datetime", "updated_on_datetime", "award") VALUES
(NULL, 'Top ten droppers in 2011 (65, rank #78)', '2013-01-12 20:17:36+00', '2013-01-12 20:17:36+00', 10),
(NULL, 'Top ten droppers in 2013 (176, rank #94)', '2014-02-20 13:49:24+00', '2014-02-20 13:49:24+00', 14),
(NULL, 'Top ten droppers in 2014 (1109, rank #19)', '2017-10-08 15:32:22+00', '2017-10-08 15:32:22+00', 16)
EOL;
        $this->pPdo->query($sql);
    }

    protected function cleanerHook(&$values) {
        $values[4] = $values[1];  // updated_on_datetime
        $stmt = $this->pPdo->query('SELECT id FROM gk_awards WHERE filename=\''.$values[3].'\';');
        $values[3] = $stmt->fetch()['id'];
    }

    protected function postProcessData() {
        echo 'Post processing'.PHP_EOL;
        $this->pPdo->query('DELETE FROM gk_awards_won WHERE holder NOT IN (SELECT DISTINCT(id) FROM gk_users);');
        $this->pPdo->query('UPDATE "gk_awards" SET name=REPLACE(name, \'mover \', \'movers \') WHERE description LIKE \'%mover %\';');
        $this->clear_outofbound_ranking();
        $this->fill_yearly_badges();
    }

    protected function clear_outofbound_ranking() {
        // Drop badges where rank > 100 ; sorry guys
        $sql = <<<'EOL'
DELETE FROM "gk_awards_won"
WHERE description LIKE '% rank %'
AND REGEXP_REPLACE(description, '.*\(.*, rank #(.*)\)', '\1')::int > 100
EOL;
        $this->pPdo->query($sql);

        // re-rank description
        $sql = <<<'EOL'
UPDATE "gk_awards_won" AS gkaw
SET description=REGEXP_REPLACE(REPLACE(description, 'droppers', 'movers'), '(.*)\((.*), rank #.*\)', '\1(total \2 drops, rank #'||rank||')')
FROM gk_yearly_ranking AS gkyr
WHERE gkaw.id = gkyr.award
AND "rank" IS NOT NULL
AND description LIKE '% droppers %'
AND description NOT LIKE '% droppers %total%'
EOL;
        $this->pPdo->query($sql);
    }

    protected function fill_yearly_badges() {
        $this->pPdo->query("SELECT SETVAL('geokrety.gk_yearly_ranking_id_seq', COALESCE(MAX(id), 1) ) FROM geokrety.gk_yearly_ranking;");
        $migration_year = date('Y');
        // Defining ranks
        for ($i = 2009; $i <= $migration_year; ++$i) {
            $ids = $this->pPdo->query("SELECT id FROM gk_awards WHERE name LIKE 'Top % movers $i';");
            $ids = join(',', $ids->fetchAll(PDO::FETCH_COLUMN, 0));

            if (strlen($ids)) {
                $sql = <<<EOL
WITH cte as (
     SELECT id, RANK() OVER ( ORDER BY REGEXP_REPLACE(description, '.*in [0-9]{4} \(([0-9]+), rank #.*\)', '\\1')::int DESC, REGEXP_REPLACE(description, '.*in [0-9]{4} \([0-9]+, rank #(.*)\)', '\\1')::int ASC) AS rnk
     FROM gk_awards_won
     WHERE award IN ($ids)
)

INSERT INTO gk_yearly_ranking

SELECT
nextval('gk_yearly_ranking_id_seq'),
REGEXP_REPLACE(description, '.*in ([0-9]{4}) \\(.*', '\\1')::int AS year,
holder AS "user",
cte.rnk AS rank,
(SELECT id from gk_awards_group WHERE name = 'movers') AS type,
NULL AS distance,
REGEXP_REPLACE(description, '.*in [0-9]{4} \\(([0-9]+), .*', '\\1')::int AS count,
cte.id AS award,
awarded_on_datetime,
updated_on_datetime

FROM "gk_awards_won" AS gkaw
RIGHT JOIN cte ON gkaw.id = cte.id
ORDER BY rank
LIMIT 100
EOL;
                $this->pPdo->query($sql);
            }
        }
    }
}

class GeokretyMigrator extends BaseMigrator {
    // $pFields = ['gkid', 'tracking_code', 'name', 'mission', 'owner', 'created_on_datetime', 'distance', 'caches_count', 'pictures_count', 'last_position', 'last_log', 'holder', 'missing', 'type', 'avatar', 'updated_on_datetime'];

    protected function prepareData() {
        $this->mPdo->query('UPDATE `gk-geokrety` SET typ = "1" WHERE id = 14282 LIMIT 1');
    }

    protected function cleanerHook(&$values) {
        $values[2] = trim(html_entity_decode($this->purifier->purify($values[2])));  // name
        $values[4] = $values[4] ?: null;  // owner
        $values[11] = $values[11] ?: null;  // holder
        $values[12] = $values[12] > 0 ? 1 : 0;  // missing

        $values[14] = $values[14] ?: null;  // avatar

        $values[9] = $values[9] ?: null;  // last_position
        $values[10] = $values[10] ?: null;  // last_log

        $values[8] = 0;  // pictures_count ; set to 0 as we'll count when pictures are imported

        $values[3] = Markdown::toFormattedMarkdown(trim(html_entity_decode($this->purifier->purify($values[3]))));  // mission
    }

    protected function postProcessData() {
        echo 'Post processing'.PHP_EOL;
        $this->pPdo->query('UPDATE gk_geokrety SET owner = NULL WHERE owner NOT IN (SELECT DISTINCT(id) FROM gk_users);');
        $this->pPdo->query('UPDATE gk_geokrety SET holder = NULL WHERE holder NOT IN (SELECT DISTINCT(id) FROM gk_users);');
    }

    protected function prepareInsertValues(int $chunkSize): string {
        $value = array_fill(0, sizeof($this->pFields), '?');
        $value[5] = '? AT TIME ZONE \'UTC\' AT TIME ZONE \'Europe/Paris\'';
        $value = join(', ', $value);

        return join(', ', array_fill(0, $chunkSize, "($value)"));
    }

    // TODO: recompute avatar
    // TODO: recompute distance
    // TODO: recompute caches_count
    // TODO: recompute pictures_count
    // TODO: recompute last_position
    // TODO: recompute last_log
    // TODO: recompute holder
    // TODO: recompute missing
}

class GeokretyRatesMigrator extends BaseMigrator {
    protected function cleanerHook(&$values) {
    }

    protected function postProcessData() {
        echo 'Post processing'.PHP_EOL;
        $this->pPdo->query('DELETE FROM gk_geokrety_rating WHERE author NOT IN (SELECT DISTINCT(id) FROM gk_users);');
    }
}

class MailsMigrator extends BaseMigrator {
    // 'token', 'from_user', 'to_user', 'subject', 'content', 'sent_on_datetime', 'ip'

    protected function prepareData() {
        $this->mPdo->query('UPDATE `gk-maile` SET ip = "107.178.38.23" WHERE id_maila = 7171 LIMIT 1');
    }

    protected function cleanerHook(&$values) {
        $values[3] = trim(html_entity_decode($this->purifier->purify($values[3])));  // name
        $values[4] = Markdown::toFormattedMarkdown($values[4]);  // content
    }

    protected function postProcessData() {
        echo 'Post processing'.PHP_EOL;
        $this->pPdo->query('UPDATE gk_mails SET from_user = NULL WHERE from_user NOT IN (SELECT DISTINCT(id) FROM gk_users);');
        $this->pPdo->query('UPDATE gk_mails SET to_user = NULL WHERE to_user NOT IN (SELECT DISTINCT(id) FROM gk_users);');
    }
}

class MovesMigrator extends BaseMigrator {
    //  'id', 'geokret', 'lat', 'lon', 'alt', 'country', 'distance', 'waypoint', 'created_on_datetime',
    //  'moved_on_datetime', 'author', 'comment', 'pictures_count', 'comments_count', 'move_type', 'username',
    //  'updated_on_datetime', 'app', 'app_ver'
    //  'position'

    protected function prepareData() {
        $this->mPdo->query('UPDATE `gk-ruchy` SET data = data_dodania WHERE data = "0000-00-00 00:00:00";');
        $this->mPdo->query('UPDATE `gk-ruchy` SET data = data_dodania WHERE DAY (data) = 0 OR  MONTH (data) = 0;');
        $this->mPdo->query('UPDATE `gk-ruchy` SET `user` = NULL, username = "Deleted user" WHERE `user` = 0 AND `username` = "";');
        $this->mPdo->query('UPDATE `gk-ruchy` SET `user` = NULL WHERE `user` = 0 AND `username` != "";');
        $this->mPdo->query('UPDATE `gk-ruchy` SET `lat` = NULL, `lon` = NULL WHERE `logtype` = \'2\' AND `lat` IS NOT NULL AND `lon` IS NOT NULL;');
    }

    protected function cleanerHook(&$values) {
        $values[7] = $values[7] ?: null;  // waypoint
        $values[18] = $values[18] ?: ($values[17] === 'www' ? '1.x.x' : null);  // app_ver
        $values[15] = $values[15] ? html_entity_decode($this->purifier->purify($values[15])) : null;  // username
        if (is_null($values[2]) || is_null($values[3])) {  // coordinates
            $values[4] = $values[5] = $values[6] = $values[7] = null;
            $values[] = null;  // lon
            $values[] = null;  // lat
        } else {
            $values[] = $values[3];  // lon
            $values[] = $values[2];  // lat
        }
        $values[11] = Markdown::toFormattedMarkdown($values[11]);  // comment

        $values[12] = 0;  // pictures_count ; set to 0 as we'll count when pictures are imported
    }

    protected function prepareInsertValues(int $chunkSize): string {
        $value = array_fill(0, sizeof($this->pFields) - 1, '?');
        $value[8] = $value[9] = $value[16] = '? AT TIME ZONE \'UTC\' AT TIME ZONE \'Europe/Paris\'';
        $value[] = 'public.ST_SetSRID(public.ST_MakePoint(?, ?), 4326)';
        $value = join(', ', $value);

        return join(', ', array_fill(0, $chunkSize, "($value)"));
    }

    protected function postProcessData() {
        echo 'Post processing'.PHP_EOL;
        // $this->pPdo->query('UPDATE gk_moves SET geokret = gk_geokrety.id FROM gk_geokrety WHERE gk_moves.geokret = gk_geokrety.gkid;');
        // TODO find -> Move date time can not be before GeoKret birth (2007-10-26 20:12:28+00)
        echo '* Begin transaction'.PHP_EOL;
        $this->pPdo->beginTransaction();
        echo '* Disable foreign keys'.PHP_EOL;
        $this->pPdo->query('ALTER TABLE "gk_pictures" DROP CONSTRAINT "gk_pictures_move_fkey";');
        $this->pPdo->query('ALTER TABLE "gk_moves_comments" DROP CONSTRAINT "gk_moves_comments_move_fkey";');
        $this->pPdo->query('ALTER TABLE "gk_geokrety" DROP CONSTRAINT "gk_geokrety_last_position_fkey";');
        $this->pPdo->query('ALTER TABLE "gk_geokrety" DROP CONSTRAINT "gk_geokrety_last_log_fkey";');
        $this->pPdo->query('ALTER TABLE gk_moves DISABLE TRIGGER ALL;');

        echo '* Delete orphan moves'.PHP_EOL;
        $this->pPdo->query('DELETE FROM gk_moves WHERE geokret NOT IN (SELECT DISTINCT(gkid) FROM gk_geokrety);');
        echo '* Anonymize orphaned user moves'.PHP_EOL;
        $this->pPdo->query("UPDATE gk_moves SET author = NULL, username = 'Deleted user' WHERE author NOT IN (SELECT DISTINCT(id) FROM gk_users);");
        echo '* Create temp table'.PHP_EOL;
        $this->pPdo->query('CREATE TABLE gk_moves_tmp AS SELECT m.id, g.id AS geokret, lat, lon, elevation, country, m.distance, waypoint, author, comment, m.pictures_count, comments_count, username, app, app_ver, m.created_on_datetime, moved_on_datetime, m.updated_on_datetime, move_type, position FROM gk_moves AS m LEFT JOIN gk_geokrety AS g ON m.geokret = g.gkid;');
        echo '* Truncate old table'.PHP_EOL;
        $this->pPdo->query('TRUNCATE gk_moves CASCADE;');
        echo '* Import data back into table'.PHP_EOL;
        $this->pPdo->query('INSERT INTO gk_moves SELECT * FROM gk_moves_tmp;');
        echo '* Drop temp table'.PHP_EOL;
        $this->pPdo->query('DROP TABLE gk_moves_tmp;');

        echo '* Enable foreign keys moves'.PHP_EOL;
        $this->pPdo->query('ALTER TABLE gk_moves ENABLE TRIGGER ALL;');
        echo '* Enable foreign keys pictures'.PHP_EOL;
        $this->pPdo->query('ALTER TABLE "gk_pictures" ADD FOREIGN KEY ("move") REFERENCES "gk_moves" ("id") ON DELETE CASCADE ON UPDATE CASCADE;');
        echo '* Enable foreign keys moves comments'.PHP_EOL;
        $this->pPdo->query('ALTER TABLE "gk_moves_comments" ADD FOREIGN KEY ("move") REFERENCES "gk_moves" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;');
        echo '* Enable foreign keys geokrety last pos'.PHP_EOL;
        $this->pPdo->query('ALTER TABLE "gk_geokrety" ADD FOREIGN KEY ("last_position") REFERENCES "gk_moves" ("id") ON DELETE SET NULL ON UPDATE NO ACTION;');
        echo '* Enable foreign keys geokrety last log'.PHP_EOL;
        $this->pPdo->query('ALTER TABLE "gk_geokrety" ADD FOREIGN KEY ("last_log") REFERENCES "gk_moves" ("id") ON DELETE SET NULL ON UPDATE NO ACTION;');
        echo '* Commit transaction'.PHP_EOL;
        $this->pPdo->commit();
    }

    // TODO: recompute distance
    // TODO: recompute pictures_count
    // TODO: recompute comments_count

    // TODO, waypoint foreign key ; remove lat/lon/alt/country ; waypoint id
}

class MoveCommentsMigrator extends BaseMigrator {
    // $pFields = ['move', 'geokret', 'author', 'created_on_datetime', 'content', 'type', 'updated_on_datetime'];

    protected function prepareData() {
        $this->mPdo->query('UPDATE `gk-ruchy-comments` SET user_id=NULL WHERE user_id NOT IN (SELECT DISTINCT(userid) FROM `gk-users`);');
        $this->mPdo->query('DELETE FROM `gk-ruchy-comments` WHERE kret_id NOT IN (SELECT DISTINCT(id) FROM `gk-geokrety`) OR ruch_id NOT IN (SELECT DISTINCT(ruch_id) FROM `gk-ruchy`);');
    }

    protected function cleanerHook(&$values) {
        $values[4] = trim(html_entity_decode(Markdown::toFormattedMarkdown($values[4])));  // content
    }

    protected function postProcessData() {
        echo 'Post processing'.PHP_EOL;
        $this->pPdo->query('UPDATE gk_moves_comments SET geokret = gk_geokrety.id FROM gk_geokrety WHERE gk_moves_comments.geokret = gk_geokrety.gkid');
    }
}

class PicturesMigrator extends BaseMigrator {
    // $pFields = ['id', 'type', 'move', 'geokret', 'user', 'filename', 'caption', 'created_on_datetime', 'updated_on_datetime', 'uploaded_on_datetime', 'author', 'bucket', 'key'];

    protected function prepareData() {
        $this->mPdo->query('DELETE FROM `gk-obrazki` WHERE typ = 2 AND user NOT IN (SELECT DISTINCT(userid) FROM `gk-users`) OR typ = 0 AND id_kreta NOT IN (SELECT DISTINCT(id) FROM `gk-geokrety`) OR typ = 1 AND id NOT IN (SELECT DISTINCT(ruch_id) FROM `gk-ruchy`);');
        $this->mPdo->query('UPDATE `gk-obrazki` LEFT JOIN `gk-geokrety` on id_kreta = `gk-geokrety`.id SET `gk-obrazki`.timestamp = `gk-geokrety`.timestamp WHERE `gk-obrazki`.timestamp = "0000-00-00 00:00:00";');
        $this->mPdo->query('UPDATE `gk-obrazki` SET id_kreta = id WHERE typ = 0 AND id != id_kreta;');
    }

    protected function cleanerHook(&$values) {
        $values[6] = trim(html_entity_decode($this->purifier->purify($values[6])));  // caption
        $values[] = $values[7];  // updated_on_datetime
        $values[] = $values[7];  // uploaded_on_datetime
        $values[] = $values[4];  // author
        $values[] = null;  // bucket
        $values[] = null;  // key

        switch ($values[1]) {
            case PictureType::PICTURE_GEOKRET_AVATAR:
                $values[2] = $values[4] = null;
                break;
            case PictureType::PICTURE_GEOKRET_MOVE:
                $values[3] = $values[4] = null;
                break;
            case PictureType::PICTURE_USER_AVATAR:
                $values[2] = $values[3] = null;
                break;
        }
        $values[6] = trim(html_entity_decode($this->purifier->purify($values[6])));  // caption
    }

    protected function postProcessData() {
        echo 'Post processing'.PHP_EOL;
        $this->pPdo->query('UPDATE gk_pictures SET geokret = gk_geokrety.id FROM gk_geokrety WHERE gk_pictures.geokret = gk_geokrety.gkid;');
        $this->pPdo->query('UPDATE gk_pictures SET author = NULL WHERE author NOT IN (SELECT DISTINCT(id) FROM gk_users);');
        $this->pPdo->query('UPDATE gk_geokrety SET avatar = NULL WHERE avatar NOT IN (SELECT DISTINCT(id) FROM gk_pictures);');
        $this->pPdo->query('DELETE FROM gk_pictures WHERE id IN (SELECT gk_pictures.id FROM gk_pictures LEFT JOIN gk_moves ON gk_pictures.move=gk_moves.id WHERE gk_pictures.move IS NOT NULL AND gk_moves.id IS NULL);');
        $this->pPdo->query('DELETE FROM gk_pictures WHERE id IN (SELECT gk_pictures.id FROM gk_pictures LEFT JOIN gk_users ON gk_pictures.user=gk_users.id WHERE gk_pictures.user IS NOT NULL AND gk_users.id IS NULL);');
        $this->pPdo->query('UPDATE "gk_pictures" SET geokret=gk_moves.geokret FROM "gk_moves" WHERE "gk_pictures".move=gk_moves.id AND CAST("gk_pictures".type AS text) = \'1\';');
        $this->pPdo->query('WITH pictures AS (SELECT COUNT(*) AS total, "user" FROM gk_pictures WHERE type = 2 AND uploaded_on_datetime IS NOT NULL AND "user" IS NOT NULL GROUP BY "user") UPDATE "gk_users" AS u  SET pictures_count = pictures.total FROM pictures WHERE u.id = pictures."user";');
        $this->pPdo->query('WITH pictures AS (SELECT COUNT(*) AS total, "geokret" FROM gk_pictures WHERE type = 0 AND uploaded_on_datetime IS NOT NULL AND "geokret" IS NOT NULL GROUP BY "geokret") UPDATE "gk_geokrety" AS g  SET pictures_count = pictures.total FROM pictures WHERE g.id = pictures."geokret";');
        $this->pPdo->query('WITH pictures AS (SELECT COUNT(*) AS total, "move" FROM gk_pictures WHERE type = 01 AND uploaded_on_datetime IS NOT NULL AND "move" IS NOT NULL GROUP BY "move") UPDATE "gk_moves" AS m  SET pictures_count = pictures.total FROM pictures WHERE m.id = pictures."move";');
        $this->pPdo->query('WITH subquery AS (select id, "user" as uid from gk_pictures where author = "user" and uploaded_on_datetime is not null order by uploaded_on_datetime desc) UPDATE gk_users SET avatar = subquery.id FROM subquery WHERE gk_users.id = subquery.uid;');
    }
}

class RacesMigrator extends BaseMigrator {
    // 'id', 'created_on_datetime', 'organizer', 'private', 'password', 'title', 'start_on_datetime', 'end_on_datetime', 'description', 'type', 'waypoint',
    // 'target_lat', 'target_lon', 'target_dist', 'target_caches', 'status', 'updated_on_datetime'
    protected function cleanerHook(&$values) {
        $values[4] = $values[4] ?: null;  // password
        $values[5] = trim(html_entity_decode($this->purifier->purify($values[5])));  // title
        $values[8] = Markdown::toFormattedMarkdown($values[8]);  // description
        $values[10] = $values[10] ?: null;  // waypoint
        $values[11] = $values[11] ?: null;  // target_lat
        $values[12] = $values[12] ?: null;  // target_lon
        $values[13] = $values[13] ?: null;  // target_dist
        $values[14] = $values[14] ?: null;  // target_caches
        $values[] = $values[1];  // updated_on_datetime
    }
}

class RacesParticipantsMigrator extends BaseMigrator {
    // 'id', 'race', 'geokret', 'initial_distance', 'initial_caches_count', 'distance_to_destination', 'joined_on_datetime', 'finished_on_datetime',
    // 'finish_distance', 'finish_caches_count', 'finish_lat', 'finish_lon', 'updated_on_datetime'
    protected function cleanerHook(&$values) {
        $values[] = $values[7] ?: $values[6];  // updated_on_datetime
    }

    protected function postProcessData() {
        echo 'Post processing'.PHP_EOL;
        $this->pPdo->query('UPDATE gk_races_participants SET geokret = gk_geokrety.id FROM gk_geokrety WHERE gk_races_participants.geokret = gk_geokrety.gkid;');
    }
}

class OwnerCodesMigrator extends BaseMigrator {
    // 'id', 'geokret', 'token', 'generated_on_datetime', 'claimed_on_datetime', 'user', 'validating_ip', 'used'
    protected function cleanerHook(&$values) {
        $values[4] = $values[4] === '0000-00-00 00:00:00' ? null : $values[4];  // claimed_on_datetime
        $values[5] = $values[5] ?: null;  // user
        $values[6] = is_null($values[4]) ? null : '127.0.0.1';  // validating_ip
        $values[7] = is_null($values[4]) ? 0 : 1;  // used
    }

    protected function postProcessData() {
        echo 'Post processing'.PHP_EOL;
        $this->pPdo->query('UPDATE gk_owner_codes SET geokret = gk_geokrety.id FROM gk_geokrety WHERE gk_owner_codes.geokret = gk_geokrety.gkid;');
    }
}

class WaypointGCMigrator extends BaseMigrator {
    // 'wpt', 'country', 'alt', 'lat', 'lon'

    // TODO clean spaces
    protected function prepareData() {
        $this->mPdo->query('DELETE FROM `gk-waypointy-gc`;');
        $this->mPdo->query('INSERT INTO `gk-waypointy-gc` SELECT waypoint, lat, lon, country, alt FROM `gk-ruchy` WHERE waypoint like \'GC%\' GROUP BY waypoint;');
    }

    // $this->mPdo->query('DELETE FROM `gk-waypointy-gc` WHERE wpt = "GC1K14H	";');
//    protected function cleanerHook(&$values) {
//        $lon = $values[3];
//        $lat = $values[4];
//        array_pop($values); // drop lon
//        $values[] = $lon;
//        $values[] = $lat;
//    }

    protected function prepareInsertValues(int $chunkSize): string {
        $value = array_fill(0, sizeof($this->pFields) - 1, '?');
        $value[] = 'public.ST_SetSRID(public.ST_MakePoint(?, ?), 4326)';
        $value = join(', ', $value);

        return join(', ', array_fill(0, $chunkSize, "($value)"));
    }
}

class WatchedMigrator extends BaseMigrator {
    //    'userid', 'id'
    //    'user', 'geokret'

    protected function prepareData() {
        $this->mPdo->query('DELETE FROM `gk-obserwable` WHERE userid NOT IN (SELECT DISTINCT(userid) FROM `gk-users`) OR id NOT IN (SELECT DISTINCT(id) FROM `gk-geokrety`);');
    }

    protected function postProcessData() {
        echo 'Post processing'.PHP_EOL;
        $this->pPdo->query('CREATE TABLE gk_watched_tmp AS SELECT * FROM gk_watched;');
        $this->pPdo->query('UPDATE gk_watched_tmp SET geokret = gk_geokrety.id FROM gk_geokrety WHERE gk_watched_tmp.geokret = gk_geokrety.gkid;');
        $this->pPdo->query('TRUNCATE gk_watched RESTART IDENTITY;');
        $this->pPdo->query('INSERT INTO gk_watched SELECT * FROM gk_watched_tmp;');
        $this->pPdo->query('DROP TABLE gk_watched_tmp;');
    }
}

// // Check integrity
// ALTER TABLE "gk_account_activation"
// DROP CONSTRAINT "gk_account_activation_user_fkey",
// ADD FOREIGN KEY ("user") REFERENCES "gk_users" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;
//
// ALTER TABLE "gk_badges"
// DROP CONSTRAINT "gk_badges_holder_fkey",
// ADD FOREIGN KEY ("holder") REFERENCES "gk_users" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;
//
// ALTER TABLE "gk_geokrety"
// DROP CONSTRAINT "gk_geokrety_owner_fkey",
// ADD FOREIGN KEY ("owner") REFERENCES "gk_users" ("id") ON DELETE SET NULL ON UPDATE NO ACTION;
//
// ALTER TABLE "gk_geokrety"
// DROP CONSTRAINT "gk_geokrety_last_position_fkey",
// ADD FOREIGN KEY ("last_position") REFERENCES "gk_moves" ("id") ON DELETE SET NULL ON UPDATE NO ACTION;
//
// ALTER TABLE "gk_geokrety"
// DROP CONSTRAINT "gk_geokrety_last_log_fkey",
// ADD FOREIGN KEY ("last_log") REFERENCES "gk_moves" ("id") ON DELETE SET NULL ON UPDATE NO ACTION;
//
// ALTER TABLE "gk_geokrety"
// DROP CONSTRAINT "gk_geokrety_holder_fkey",
// ADD FOREIGN KEY ("holder") REFERENCES "gk_users" ("id") ON DELETE SET NULL ON UPDATE NO ACTION;
//
// ALTER TABLE "gk_geokrety"
// DROP CONSTRAINT "gk_geokrety_avatar_fkey",
// ADD FOREIGN KEY ("avatar") REFERENCES "gk_pictures" ("id") ON DELETE SET NULL ON UPDATE NO ACTION;
//
// ALTER TABLE "gk_geokrety_rating"
// DROP CONSTRAINT "gk_geokrety_rating_geokret_fkey",
// ADD FOREIGN KEY ("geokret") REFERENCES "gk_geokrety" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;
//
// ALTER TABLE "gk_geokrety_rating"
// DROP CONSTRAINT "gk_geokrety_rating_author_fkey",
// ADD FOREIGN KEY ("author") REFERENCES "gk_users" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;
//
// ALTER TABLE "gk_mails"
// DROP CONSTRAINT "gk_mails_from_user_fkey",
// ADD FOREIGN KEY ("from_user") REFERENCES "gk_users" ("id") ON DELETE SET NULL ON UPDATE NO ACTION;
//
// ALTER TABLE "gk_mails"
// DROP CONSTRAINT "gk_mails_to_user_fkey",
// ADD FOREIGN KEY ("to_user") REFERENCES "gk_users" ("id") ON DELETE SET NULL ON UPDATE NO ACTION;
//
// ALTER TABLE "gk_moves_comments"
// DROP CONSTRAINT "gk_moves_comments_move_fkey",
// ADD FOREIGN KEY ("move") REFERENCES "gk_moves" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;
//
// ALTER TABLE "gk_moves_comments"
// DROP CONSTRAINT "gk_moves_comments_geokret_fkey",
// ADD FOREIGN KEY ("geokret") REFERENCES "gk_geokrety" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;
//
// ALTER TABLE "gk_moves_comments"
// DROP CONSTRAINT "gk_moves_comments_author_fkey",
// ADD FOREIGN KEY ("author") REFERENCES "gk_users" ("id") ON DELETE SET NULL ON UPDATE NO ACTION;
//
// ALTER TABLE "gk_moves"
// DROP CONSTRAINT "gk_moves_author_fkey",
// ADD FOREIGN KEY ("author") REFERENCES "gk_users" ("id") ON DELETE SET NULL ON UPDATE NO ACTION;
//
// ALTER TABLE "gk_moves"
// DROP CONSTRAINT "gk_moves_geokret_fkey",
// ADD FOREIGN KEY ("geokret") REFERENCES "gk_geokrety" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;
//
// ALTER TABLE "gk_news"
// DROP CONSTRAINT "gk_news_author_fkey",
// ADD FOREIGN KEY ("author") REFERENCES "gk_users" ("id") ON DELETE SET NULL ON UPDATE NO ACTION;
//
// ALTER TABLE "gk_news_comments"
// DROP CONSTRAINT "gk_news_comments_news_fkey",
// ADD FOREIGN KEY ("news") REFERENCES "gk_news" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;
//
// ALTER TABLE "gk_news_comments"
// DROP CONSTRAINT "gk_news_comments_author_fkey",
// ADD FOREIGN KEY ("author") REFERENCES "gk_users" ("id") ON DELETE SET NULL ON UPDATE NO ACTION;
//
// ALTER TABLE "gk_news_comments_access"
// DROP CONSTRAINT "gk_news_comments_access_news_fkey",
// ADD FOREIGN KEY ("news") REFERENCES "gk_news" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;
//
// ALTER TABLE "gk_news_comments_access"
// DROP CONSTRAINT "gk_news_comments_access_author_fkey",
// ADD FOREIGN KEY ("author") REFERENCES "gk_users" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;
//
// ALTER TABLE "gk_owner_codes"
// DROP CONSTRAINT "gk_owner_codes_geokret_fkey",
// ADD FOREIGN KEY ("geokret") REFERENCES "gk_geokrety" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;
//
// ALTER TABLE "gk_owner_codes"
// DROP CONSTRAINT "gk_owner_codes_user_fkey",
// ADD FOREIGN KEY ("user") REFERENCES "gk_users" ("id") ON DELETE SET NULL ON UPDATE NO ACTION;
//
// ALTER TABLE "gk_password_tokens"
// DROP CONSTRAINT "gk_password_tokens_user_fkey",
// ADD FOREIGN KEY ("user") REFERENCES "gk_users" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;
//
// ALTER TABLE "gk_pictures"
// DROP CONSTRAINT "gk_pictures_move_fkey",
// ADD FOREIGN KEY ("move") REFERENCES "gk_moves" ("id") ON DELETE SET NULL ON UPDATE NO ACTION;
//
// ALTER TABLE "gk_pictures"
// DROP CONSTRAINT "gk_pictures_geokret_fkey",
// ADD FOREIGN KEY ("geokret") REFERENCES "gk_geokrety" ("id") ON DELETE SET NULL ON UPDATE NO ACTION;
//
// ALTER TABLE "gk_pictures"
// DROP CONSTRAINT "gk_pictures_user_fkey",
// ADD FOREIGN KEY ("user") REFERENCES "gk_users" ("id") ON DELETE SET NULL ON UPDATE NO ACTION;
//
// ALTER TABLE "gk_pictures"
// DROP CONSTRAINT "gk_pictures_author_fkey",
// ADD FOREIGN KEY ("author") REFERENCES "gk_users" ("id") ON DELETE SET NULL ON UPDATE NO ACTION;
//
// ALTER TABLE "gk_races"
// DROP CONSTRAINT "gk_races_organizer_fkey",
// ADD FOREIGN KEY ("organizer") REFERENCES "gk_users" ("id") ON DELETE SET NULL ON UPDATE NO ACTION;
//
// ALTER TABLE "gk_races_participants"
// DROP CONSTRAINT "gk_races_participants_race_fkey",
// ADD FOREIGN KEY ("race") REFERENCES "gk_races" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;
//
// ALTER TABLE "gk_races_participants"
// DROP CONSTRAINT "gk_races_participants_geokret_fkey",
// ADD FOREIGN KEY ("geokret") REFERENCES "gk_geokrety" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;
//
// ALTER TABLE "gk_users"
// DROP CONSTRAINT "gk_users_avatar_fkey",
// ADD FOREIGN KEY ("avatar") REFERENCES "gk_pictures" ("id") ON DELETE SET NULL ON UPDATE NO ACTION;
//
// ALTER TABLE "gk_watched"
// DROP CONSTRAINT "gk_watched_user_fkey",
// ADD FOREIGN KEY ("user") REFERENCES "gk_users" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;
//
// ALTER TABLE "gk_watched"
// DROP CONSTRAINT "gk_watched_geokret_fkey",
// ADD FOREIGN KEY ("geokret") REFERENCES "gk_geokrety" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;
//
// //// Reset all sequence
// SELECT 'SELECT SETVAL(' ||
// quote_literal(quote_ident(PGT.schemaname) || '.' || quote_ident(S.relname)) ||
// ', COALESCE(MAX(' ||quote_ident(C.attname)|| '), 1) ) FROM ' ||
// quote_ident(PGT.schemaname)|| '.'||quote_ident(T.relname)|| ';'
// FROM pg_class AS S,
//     pg_depend AS D,
//     pg_class AS T,
//     pg_attribute AS C,
//     pg_tables AS PGT
// WHERE S.relkind = 'S'
// AND S.oid = D.objid
// AND D.refobjid = T.oid
// AND D.refobjid = C.attrelid
// AND D.refobjsubid = C.attnum
// AND T.relname = PGT.tablename
// ORDER BY S.relname;

// UPDATE "gk_moves" SET
// "country" = LOWER(iso_a2)
// from gk_countries
// where ST_Contains(geom, ST_GeomFromText('point(' || lon || ' ' || lat || ')'));
