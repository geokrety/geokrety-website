<?php

namespace GeoKrety\Model;

use DB\SQL\Schema;

/**
 * @property int id
 * @property string name provider name
 */
class SocialAuthProvider extends Base implements \JsonSerializable {
    //    use \Validation\Traits\CortexTrait;

    protected $db = 'DB';
    protected $table = 'gk_social_auth_providers';

    protected $fieldConf = [
        'name' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
        ],
    ];

    /**
     * @param string $name The provider name
     *
     * @throws \Exception
     */
    public static function getProvider(string $name): SocialAuthProvider {
        $provider = new SocialAuthProvider();
        $provider->load(['name = ?', $name], null, GK_SITE_CACHE_TTL_SOCIAL_AUTH_PROVIDERS);
        if ($provider->dry()) {
            throw new \Exception('Unsupported social auth provider');
        }

        return $provider;
    }

    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
