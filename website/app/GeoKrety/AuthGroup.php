<?php

namespace GeoKrety;

class AuthGroup extends \Auth {
    public const AUTH_LEVEL_ANONYMOUS = 0;
    public const AUTH_LEVEL_AUTHENTICATED = 1;
    public const AUTH_LEVEL_ADMINISTRATORS = 90;
    public const AUTH_LEVEL_SUPER_ADMINISTRATORS = 99;

    public const AUTH_GROUP_ANONYMOUS = [
        self::AUTH_LEVEL_ANONYMOUS,
    ];

    public const AUTH_GROUP_AUTHENTICATED = [
        self::AUTH_LEVEL_AUTHENTICATED,
        self::AUTH_LEVEL_ADMINISTRATORS,
        self::AUTH_LEVEL_SUPER_ADMINISTRATORS,
    ];

    public const AUTH_GROUP_ADMINISTRATORS = [
        self::AUTH_LEVEL_ADMINISTRATORS,
        self::AUTH_LEVEL_SUPER_ADMINISTRATORS,
    ];

    public const AUTH_GROUP_SUPER_ADMINISTRATORS = [
        self::AUTH_LEVEL_SUPER_ADMINISTRATORS,
    ];
}
