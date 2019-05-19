<?php

namespace Geokrety\View;

/**
 * HTML Helper, a way to generate GeoKrety Social Groups Html Table.
 **/
class SocialGroups {
    private $socialGroups;

    public function __construct($config) {
        $this->socialGroups = $config;
    }

    public function count() {
        if (!isset($this->socialGroups)) {
            return 0;
        }

        return count($this->socialGroups) - 1;
    }

    public function toHtmlTable() {
        $groupsTable = '<div class="table-responsive">'
            .'<table class="table table-striped">'
                .'<thead>'
                    .'<tr>'
                    .'<th>'.$this->socialGroups[0]['lang'].'</th>'
                    .'<th>'.$this->socialGroups[0]['service'].'</th>'
                    .'<th>'.$this->socialGroups[0]['title'].'</th>'
                    .'</tr>'
                .'</thead>'
                .'<tbody>';
        for ($i = 1; $i < count($this->socialGroups); ++$i) {
            $groupsTable .= '<tr>'
                       .'<td><span class="flag-icon flag-icon-'
                       .$this->socialGroups[$i]['flag']
                       .'"></span>'
                       .'&#160;'.$this->socialGroups[$i]['lang'].'</td>'
                       .'<td>'.$this->socialGroups[$i]['service'].'</td>'
                       .'<td><a href="'.$this->socialGroups[$i]['link'].'">'.$this->socialGroups[$i]['title'].'</td>'
                       .'</tr>';
        }
        $groupsTable .= '</tbody>'
                .'</table>'
            .'</div>';

        return $groupsTable;
    }
}
