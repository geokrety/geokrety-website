<?php

declare(strict_types=1);

/**
 * GeoKrety Instant Notification Worker.
 *
 * RabbitMQ consumer for processing instant email notifications.
 * Listens to the 'geokrety' exchange and sends immediate email notifications
 * for GeoKret activities (moves, comments) based on user preferences.
 */
require_once __DIR__.'/../init-f3.php';
require_once __DIR__.'/WorkerBase.php';

use GeoKrety\Email\InstantNotification;
use GeoKrety\Model\Geokret;
use GeoKrety\Model\Move;
use GeoKrety\Model\MoveComment;
use GeoKrety\Model\User;
use GeoKrety\Service\StaticMapImage;
use PhpAmqpLib\Message\AMQPMessage;

class MailNotificationWorker extends WorkerBase {
    protected function getWorkerName(): string {
        return 'Mail Notification Worker';
    }

    protected function getQueueName(): string {
        return 'mail_notification_worker_queue';
    }

    protected function getQueueArguments(): array {
        return ['x-message-ttl' => ['I', 10800000]];
    }

    protected function processMessage(AMQPMessage $msg): void {
        try {
            $data = json_decode($msg->body, true);

            if (!$data || !isset($data['id'], $data['op'], $data['kind'])) {
                $this->log(self::LOG_WARNING, 'Invalid message format', ['body' => $msg->body]);
                $msg->ack();

                return;
            }

            $this->log(self::LOG_DEBUG, 'Message received', $data);

            // Only process INSERT operations
            if ($data['op'] !== 'INSERT') {
                $this->log(self::LOG_DEBUG, 'Ignoring non-INSERT operation', ['op' => $data['op']]);
                $msg->ack();

                return;
            }

            // Route to appropriate handler
            switch ($data['kind']) {
                case 'gk_moves':
                    $this->handleMove((int) $data['id']);
                    break;
                case 'gk_moves_comments':
                    $this->handleMoveComment((int) $data['id']);
                    break;
                default:
                    $this->log(self::LOG_DEBUG, 'Unhandled entity kind', ['kind' => $data['kind']]);
            }

            $msg->ack();
        } catch (Exception $e) {
            $this->log(self::LOG_ERROR, 'Error processing message: '.$e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            $msg->nack(true); // Requeue on error
        }
    }

    private function handleMove(int $moveId): void {
        $this->log(self::LOG_DEBUG, 'Querying database for move', ['move_id' => $moveId]);

        $move = new Move();
        $move->load(['id = ?', $moveId]);

        if ($move->dry()) {
            $this->log(self::LOG_WARNING, 'Move not found', ['move_id' => $moveId]);

            return;
        }

        $this->log(self::LOG_DEBUG, 'Move loaded', [
            'move_id' => $moveId,
            'geokret_id' => $move->geokret,
            'author_id' => $move->author,
        ]);

        // Validate required relations
        if (!$move->geokret || !$move->author) {
            $this->log(self::LOG_WARNING, 'Move missing required relations', [
                'move_id' => $moveId,
                'has_geokret' => $move->geokret !== null,
                'has_author' => $move->author !== null,
            ]);

            return;
        }

        // Find users who should be notified about this move
        $notifyUsers = $this->getUsersToNotifyForMove($move);

        foreach ($notifyUsers as $userId => $isHomeLocationNotification) {
            $this->sendMoveNotification($userId, $move, $isHomeLocationNotification);
        }
    }

    private function handleMoveComment(int $commentId): void {
        $this->log(self::LOG_DEBUG, 'Querying database for comment', ['comment_id' => $commentId]);

        $comment = new MoveComment();
        $comment->load(['id = ?', $commentId]);

        if ($comment->dry()) {
            $this->log(self::LOG_WARNING, 'Comment not found', ['comment_id' => $commentId]);

            return;
        }

        $this->log(self::LOG_DEBUG, 'Comment loaded', [
            'comment_id' => $commentId,
            'move_id' => $comment->move,
            'author_id' => $comment->author,
        ]);

        // Validate required relations
        if (!$comment->move || !$comment->author) {
            $this->log(self::LOG_WARNING, 'Comment missing required relations', [
                'comment_id' => $commentId,
                'has_move' => $comment->move !== null,
                'has_author' => $comment->author !== null,
            ]);

            return;
        }

        // Find users who should be notified about this comment
        $notifyUsers = $this->getUsersToNotifyForComment($comment);

        foreach ($notifyUsers as $userId) {
            $this->sendCommentNotification($userId, $comment);
        }
    }

    private function getUsersToNotifyForMove(Move $move): array {
        // Find GeoKret owner and watchers who have instant notifications enabled
        $f3 = Base::instance();
        $db = $f3->get('DB');

        // Get GeoKret owner who wants notifications about their own GeoKret
        // Note: INSTANT_NOTIFICATIONS defaults to false (check EXISTS true), granular settings default to true (check NOT EXISTS false)
        $ownerSql = <<<'SQL'
SELECT DISTINCT u.id
FROM geokrety.gk_users u
WHERE u.id = ?
AND u.email_invalid = 0
AND EXISTS (
    SELECT 1
    FROM geokrety.gk_users_settings s
    WHERE s.user = u.id
    AND s.name = 'INSTANT_NOTIFICATIONS'
    AND s.value = 'true'
)
AND NOT EXISTS (
    SELECT 1
    FROM geokrety.gk_users_settings s
    WHERE s.user = u.id
    AND s.name = 'INSTANT_NOTIFICATIONS_MOVES_OWN_GK'
    AND s.value = 'false'
)
SQL;

        // Get watchers who want notifications about watched GeoKret
        $watchersSql = <<<'SQL'
SELECT DISTINCT u.id
FROM geokrety.gk_users u
WHERE u.id IN (
    SELECT w.user
    FROM geokrety.gk_watched w
    WHERE w.geokret = ?
)
AND u.email_invalid = 0
AND EXISTS (
    SELECT 1
    FROM geokrety.gk_users_settings s
    WHERE s.user = u.id
    AND s.name = 'INSTANT_NOTIFICATIONS'
    AND s.value = 'true'
)
AND NOT EXISTS (
    SELECT 1
    FROM geokrety.gk_users_settings s
    WHERE s.user = u.id
    AND s.name = 'INSTANT_NOTIFICATIONS_MOVES_WATCHED_GK'
    AND s.value = 'false'
)
SQL;

        // Get users with home location near the move who want notifications
        $homeLocationSql = <<<'SQL'
SELECT DISTINCT u.id
FROM geokrety.gk_users u
WHERE u.email_invalid = 0
AND u.home_latitude IS NOT NULL
AND u.home_longitude IS NOT NULL
AND u.observation_area > 0
AND public.st_dwithin(
    public.st_setsrid(public.st_makepoint(?, ?), 4326),
    public.st_setsrid(public.st_makepoint(u.home_longitude, u.home_latitude), 4326),
    (u.observation_area * 1000)::double precision
)
AND EXISTS (
    SELECT 1
    FROM geokrety.gk_users_settings s
    WHERE s.user = u.id
    AND s.name = 'INSTANT_NOTIFICATIONS'
    AND s.value = 'true'
)
AND NOT EXISTS (
    SELECT 1
    FROM geokrety.gk_users_settings s
    WHERE s.user = u.id
    AND s.name = 'INSTANT_NOTIFICATIONS_MOVES_AROUND_HOME'
    AND s.value = 'false'
)
SQL;

        $ownerResult = $db->exec($ownerSql, [$move->geokret->id]);
        $watchersResult = $db->exec($watchersSql, [$move->geokret->id]);
        $homeLocationResult = $db->exec($homeLocationSql, [$move->lon, $move->lat]);

        // Build array with user IDs and a flag indicating if it's a home-location notification
        $users = [];

        // Add owners and watchers (not home-location notifications)
        foreach (array_column($ownerResult, 'id') as $userId) {
            $users[$userId] = false;
        }
        foreach (array_column($watchersResult, 'id') as $userId) {
            $users[$userId] = false;
        }

        // Add home-location users (with flag set to true)
        foreach (array_column($homeLocationResult, 'id') as $userId) {
            $users[$userId] = true;
        }

        // Remove duplicates and exclude the move author
        $users = array_unique($users, SORT_REGULAR);

        return array_filter($users, fn ($userId) => $userId != $move->author->id, ARRAY_FILTER_USE_KEY);
    }

    private function getUsersToNotifyForComment(MoveComment $comment): array {
        // Find move author and GeoKret owner/watchers who want comment notifications
        $f3 = Base::instance();
        $db = $f3->get('DB');

        // Note: INSTANT_NOTIFICATIONS defaults to false (check EXISTS true), granular settings default to true (check NOT EXISTS false)
        $sql = <<<'SQL'
SELECT DISTINCT u.id
FROM geokrety.gk_users u
WHERE u.id IN (
    -- Move author
    SELECT m.author
    FROM geokrety.gk_moves m
    WHERE m.id = ?
    AND m.author IS NOT NULL

    UNION

    -- GeoKret owner
    SELECT gk.owner
    FROM geokrety.gk_geokrety gk
    INNER JOIN geokrety.gk_moves m ON m.geokret = gk.id
    WHERE m.id = ?
    AND gk.owner IS NOT NULL

    UNION

    -- Watchers
    SELECT w.user
    FROM geokrety.gk_watched w
    INNER JOIN geokrety.gk_moves m ON m.geokret = w.geokret
    WHERE m.id = ?
)
AND u.email_invalid = 0
AND EXISTS (
    SELECT 1
    FROM geokrety.gk_users_settings s
    WHERE s.user = u.id
    AND s.name = 'INSTANT_NOTIFICATIONS'
    AND s.value = 'true'
)
AND NOT EXISTS (
    SELECT 1
    FROM geokrety.gk_users_settings s
    WHERE s.user = u.id
    AND s.name = 'INSTANT_NOTIFICATIONS_MOVE_COMMENTS'
    AND s.value = 'false'
)
SQL;

        $result = $db->exec($sql, [$comment->move->id, $comment->move->id, $comment->move->id]);

        // Return user IDs excluding the comment author
        return array_filter(array_column($result, 'id'), fn ($userId) => $userId != $comment->author->id);
    }

    private function sendMoveNotification(int $userId, Move $move, bool $isHomeLocationNotification = false): void {
        try {
            $user = new User();
            $user->load(['id = ?', $userId]);

            if ($user->dry()) {
                $this->log(self::LOG_WARNING, 'User not found', ['user_id' => $userId]);

                return;
            }

            $this->log(self::LOG_INFO, 'Sending move notification', [
                'user_id' => $userId,
                'email' => $user->email,
                'move_id' => $move->id,
            ]);

            $email = new InstantNotification();

            // Generate and embed map only for "moves around home" notifications
            if ($isHomeLocationNotification && $user->hasHomeCoordinates()) {
                $this->attachNotificationMapToEmail($email, $user, (float) $move->lon, (float) $move->lat);
            }

            $email->sendMoveNotification($user, $move);

            $this->log(self::LOG_INFO, 'Move notification sent successfully', [
                'user_id' => $userId,
                'move_id' => $move->id,
            ]);
        } catch (Exception $e) {
            $this->log(self::LOG_ERROR, 'Failed to send move notification: '.$e->getMessage(), [
                'user_id' => $userId,
                'move_id' => $move->id,
                'exception' => get_class($e),
            ]);
        }
    }

    private function sendCommentNotification(int $userId, MoveComment $comment): void {
        $user = new User();
        $user->load(['id = ?', $userId]);

        if ($user->dry()) {
            $this->log(self::LOG_WARNING, 'User not found', ['user_id' => $userId]);

            return;
        }

        $this->log(self::LOG_INFO, 'Sending comment notification', [
            'user_id' => $userId,
            'email' => $user->email,
            'comment_id' => $comment->id,
        ]);

        try {
            $email = new InstantNotification();

            $email->sendCommentNotification($user, $comment);

            $this->log(self::LOG_INFO, 'Comment notification sent successfully', [
                'user_id' => $userId,
                'comment_id' => $comment->id,
            ]);
        } catch (Exception $e) {
            $this->log(self::LOG_ERROR, 'Failed to send comment notification: '.$e->getMessage(), [
                'user_id' => $userId,
                'comment_id' => $comment->id,
                'exception' => get_class($e),
            ]);
        }
    }

    /**
     * Attach a map image with the notification-triggering GeoKret and user's home to the email.
     *
     * @param InstantNotification $email Email instance to attach map to
     * @param User                $user  User with home coordinates
     * @param float               $lon   Longitude of the GeoKret that triggered the notification
     * @param float               $lat   Latitude of the GeoKret that triggered the notification
     */
    private function attachNotificationMapToEmail(InstantNotification $email, User $user, float $lon, float $lat): void {
        try {
            // Build GeoJSON with only the triggered GeoKret location + home marker
            $geojson = (object) [
                'type' => 'FeatureCollection',
                'features' => [
                    [
                        'type' => 'Feature',
                        'geometry' => [
                            'type' => 'Point',
                            'coordinates' => [$lon, $lat],
                        ],
                    ],
                ],
            ];

            // Convert to array format expected by StaticMapImage
            $result = [['geojson' => json_encode($geojson)]];

            $imgCid = 'NOTIFICATION_HOME_MAP';
            if (StaticMapImage::generateHomeMapWithMarkers($email, $user, $result, $imgCid)) {
                // Assign to Smarty so templates can reference the image
                GeoKrety\Service\Smarty::assign('notification_home_map_cid', $imgCid);
                $this->log(self::LOG_DEBUG, 'Home-location map embedded in notification', [
                    'user_id' => $user->id,
                    'geokrety_lat' => $lat,
                    'geokrety_lon' => $lon,
                ]);
            }
        } catch (Exception $e) {
            $this->log(self::LOG_WARNING, 'Failed to attach notification map: '.$e->getMessage(), [
                'user_id' => $user->id,
                'exception' => get_class($e),
            ]);
        }
    }
}

// Handle graceful shutdown
WorkerBase::registerSignalHandlers();

// Run the worker
try {
    $worker = new MailNotificationWorker();
    $worker->run();
} catch (Exception $e) {
    echo 'Fatal error: '.$e->getMessage()."\n";
    exit(1);
}
