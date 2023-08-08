<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Libravatar;

class UserAvatar extends Base {
    use \UserLoader;

    public function get() {
        if ($this->user->avatar) {
            $url = $this->user->avatar->url;
            header("Location: $url");
            exit;
        }

        $identifier = $this->user->email ?: $this->user->username;
        $url = Libravatar::getUrl($identifier);
        header("Location: $url");
    }
}
