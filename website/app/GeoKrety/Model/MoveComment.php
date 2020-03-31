<?php

namespace GeoKrety\Model;

use DB\SQL\Schema;
use GeoKrety\Service\HTMLPurifier;

class MoveComment extends Base {
    use \Validation\Traits\CortexTrait;

    protected $db = 'DB';
    protected $table = 'gk_moves_comments';

    protected $fieldConf = [
        'content' => [
            'type' => Schema::DT_VARCHAR512,
            'filter' => 'trim|HTMLPurifier',
            'validate' => 'not_empty|min_len,1|max_len,500',
            'nullable' => false,
        ],
        'type' => [
            'type' => Schema::DT_TINYINT,
            'nullable' => false,
            'item' => ['0', '1'],
        ],
        'created_on_datetime' => [
            'type' => \DB\SQL\Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => true,
        ],
        'updated_on_datetime' => [
            'type' => \DB\SQL\Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => false,
        ],
        'author' => [
            'belongs-to-one' => '\GeoKrety\Model\User',
        ],
        'geokret' => [
            'belongs-to-one' => '\GeoKrety\Model\Geokret',
        ],
        'move' => [
            'belongs-to-one' => '\GeoKrety\Model\Move',
        ],
    ];

    public function set_content($value) {
        return HTMLPurifier::getPurifier()->purify($value);
    }

    public function get_created_on_datetime($value) {
        return self::get_date_object($value);
    }

    public function get_updated_on_datetime($value) {
        return self::get_date_object($value);
    }

    public function isAuthor() {
        $f3 = \Base::instance();

        return $f3->get('SESSION.CURRENT_USER') && !is_null($this->author) && $f3->get('SESSION.CURRENT_USER') === $this->author->id;
    }

    public function __construct() {
        parent::__construct();
        $this->aftersave(function ($self) {
            $self->move->comments_count = $self->count(['move = ?', $self->move->id], null, 0); // Disable TTL
            $self->move->save();
        });
        $this->aftererase(function ($self) {
            $self->move->comments_count = $self->count(['move = ?', $self->move->id], null, 0); // Disable TTL
            $self->move->save();
        });
    }
}
