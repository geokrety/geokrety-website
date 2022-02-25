<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Libravatar;
use UserLoader;

class UserAvatar extends Base {
    use UserLoader;

    public function get(\Base $f3) {
        if ($this->user->avatar) {
            $url = $this->user->avatar->url;
        } else {
            $identifier = $this->user->email ?: $this->user->username;
            $url = Libravatar::getUrl($identifier);
        }

        header("Location: $url");
    }
}
