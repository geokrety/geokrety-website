<?php

namespace GeoKrety\Model\Traits;

trait EmailField {
    public function get_email(): ?string {
        if (!is_null($this->_email)) {
            return $this->_email;
        }

        $sql = <<<EOT
            SELECT gkdecrypt("_email_crypt", ?, ?) AS email
            FROM gk_email_activation
            WHERE id = ?
EOT;

        $f3 = \Base::instance();
        $result = $f3->get('DB')->exec($sql, [GK_DB_SECRET_KEY, GK_DB_GPG_PASSWORD, $this->id]);
        if (count($result) === 0) {
            return null;
        }

        return $result[0]['email'] ?: null;
    }

    public function set_email($value): ?string {
        $this->_email = $value;

        return $value;
    }

    public function validate($level = 0, $op = '<=') {
        // TODO: `unique` need a special case as we rely on hashes
        $rules = [
            'email' => [
                'filter' => 'trim',
                'validate' => 'not_empty|valid_email|email_host',
                'validate_level' => 2,
            ],
        ];
        $data = [
            'email' => $this->_email ?: $this->email,
        ];

        $validation_1 = \Validation::instance()->validate($rules, $data, null, $level);
        $validation_2 = \Validation::instance()->validateCortexMapper($this, $level, $op, true);

        return $validation_1 && $validation_2;
    }
}
