<?php

namespace Geokrety\View;

/**
 * HTML Helper, a way to generate GeoKrety Credits Html Table.
 **/
class Credits {
    private $credits;

    public function __construct($config) {
        $this->credits = $config;
    }

    public function count() {
        if (!isset($this->credits)) {
            return 0;
        }

        return count($this->credits) - 1;
    }

    public function toHtmlDivs() {
        $creditsDivs =
        '<div class="dcreds">';
        for ($i = 1; $i < count($this->credits); ++$i) {
            $creditsDivs .= $this->toHtmlDiv($this->credits[$i]);
        }
        $creditsDivs .= '</div>';

        return $creditsDivs;
    }

    private function toHtmlDiv($credit) {
        $creditName = $credit['name'];
        if (isset($credit['link'])) {
            $creditName = '<a href="'.$credit['link'].'">'.$creditName.'</a>';
        }
        if (isset($credit['desc'])) {
            $creditName .= ' : '.$credit['desc'];
        }
        $iconStyle = isset($credit['icon_style']) ? ' style="'.$credit['icon_style'].'"' : '';
        $creditDiv = '<div class="dcred"><div class="dcredimg"'.$iconStyle.'>';
        if (isset($credit['icon'])) {
            $iconAlt = $this->credits[0]['icon'].' of '.$credit['name'];
            $iconWidth = isset($credit['icon_width']) ? $credit['icon_width'] : '100px';
            $creditDiv .= '<img width="'.$iconWidth.'" src="'.$credit['icon'].'" alt="'.$iconAlt.'" title="'.$iconAlt.'">';
        }
        $creditDiv .= '</div><div class="dcredname">'.$creditName.'</div></div>';

        return $creditDiv;
    }
}
