<?php

// namespace GeoKrety\Validation;
//
// use GeoKrety\GeokretyType;
//
// class GeokretValidator extends Base {
//     public function validate(\GeoKrety\Model\Geokret $obj): bool {
//         $this->obj = $obj;
//         $this->checkNotNull('name', _('GeoKret name could not be empty.'));
//         $this->checkType('type', GeokretyType::GEOKRETY_TYPES, _('Invalid GeoKret type.'));
//
//         return !$this->hasErrors;
//     }
//
//     protected function checkType() {
//         if (!$this->obj->type->isValid()) {
//             $this->hasErrors = true;
//             $this->flash(sprintf(_('\'%s\' is not a valid GeoKret type.'), $attribute));
//
//             return false;
//         }
//
//         return true;
//     }
// }
