<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Picture;
use GeoKrety\PictureType;

class UserAvatarUpload extends AbstractPictureUpload {
    use \UserLoader;

    protected function _generateKey(): string {
        return self::generateKey($this->user->id);
    }

    public static function generateKey($id): string {
        return uniqid(sprintf('US%06d_', $id));
    }

    public static function getBucket(): string {
        return GK_BUCKET_NAME_USERS_AVATARS;
    }

    public function getPictureType(): int {
        return PictureType::PICTURE_USER_AVATAR;
    }

    public function getEventNameBase(): string {
        return 'geokret.avatar';
    }

    public function setRelationships(Picture $picture): void {
        $picture->user = $this->user;
    }

    /**
     * Check if the current user has permission on this object.
     *
     * @throws UploadPermissionException
     */
    protected function check_permission(): void {
        if ($this->user->isCurrentUser()) {
            return;
        }
        throw new UploadPermissionException(_('This is not your profile'));
    }
}
