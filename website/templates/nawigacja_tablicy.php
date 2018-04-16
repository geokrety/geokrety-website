<?php

if (!function_exists('nawiguj_tablice')) {
    function nawiguj_tablice($rozmiar_tablicy, $po_ile, $show_all = true)
    {
        $adres = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        $adres = preg_replace('/page=[0-9]*&?/', '', $adres);

        $lastchar = substr($adres, -1);
        if (strstr($adres, '?') == false) {
            $prefix = '?';
        } // if the script does not have a parameter
        else {
            if ($lastchar == '?' or $lastchar == '&') {
                $prefix = '';
            } else {
                $prefix = '&amp;';
            }
        }

        $adres = htmlentities($adres);

        //ilosc wszystkich potrzebnych stron
        $stron = ceil($rozmiar_tablicy / $po_ile);

        //ktora strone wyswietlamy
        $strona = isset($_GET['page']) ? $_GET['page'] : '';
        if ($strona == '' or !ctype_digit($strona)) {
            $strona = $stron;
        } // by default, we want the last page, which is the one that has the latest News and moles

        //jezeli ktos chce strone 'all' ale opcja jest wylaczona to serwuj ostatnia strone
        if (!$show_all and ($strona == '0')) {
            $strona = $stron;
        }

        $return['od'] = ($stron - $strona) * $po_ile;     // sql

        $do = $rozmiar_tablicy - ($stron - $strona + 1) * $po_ile;
        $od = $do + $po_ile;
        if ($do < 1) {
            $do = 0;
        }

        if ($show_all and ($strona == '0')) {
            $od = $rozmiar_tablicy;
            if ($rozmiar_tablicy == 0) {
                $do = -1;
            } else {
                $do = 0;
            }
        }

        $t_od = 0;
        $t_do = 0;
        $t_poczatek = '';
        $t_koniec = '';

        if ($strona > 1) {
            $gt = '<a href="'.$adres.$prefix.'page='.($strona - 1).'">&gt;</a>';
        } else {
            $gt = '&gt;';
        }

        if (($strona < $stron) and ($strona > 0)) {
            $lt = '<a href="'.$adres.$prefix.'page='.($strona + 1).'">&lt;</a>';
        } else {
            $lt = '&lt;';
        }

        if ($stron > 7) {
            if ($strona > $stron - 3) { //na koncu
                $t_od = $stron - 6;
                $t_do = $stron;
                $t_poczatek = ' <a href="'.$adres.$prefix.'page=1">1</a>';
                $t_koniec = '<a href="'.$adres.$prefix."page=$stron\">$stron</a> ";
            } elseif ($strona <= (1 + 3)) { //na poczatku
                $t_od = 1;
                $t_do = 1 + 6;
                $t_poczatek = ' <a href="'.$adres.$prefix.'page=1">1</a>';
                $t_koniec = '<a href="'.$adres.$prefix."page=$stron\">$stron</a> ";
            } else { //po srodku
                $t_od = $strona - 3;
                $t_do = $t_od + 6;
                $t_poczatek = ' <a href="'.$adres.$prefix.'page=1">1</a>';
                $t_koniec = '<a href="'.$adres.$prefix."page=$stron\">$stron</a> ";
            }
        } else {
            $t_od = 1;
            $t_do = $stron;
        }

        // przewijanie
        $przewijanie = '';
        //for($i = $stron; $i >= 0; $i--){
        for ($i = $t_do; $i >= $t_od; --$i) {
            $link = '';
            if ($show_all and ($i == 0) and ($stron > 1)) {
                $link = '<a href="'.$adres.$prefix.'page='.$i.'">'._('Show all').'</a>';
            }
            if ($i > 0) {
                $link = '<a href="'.$adres.$prefix.'page='.$i.'">'.$i.'</a>';
            }
            if ($i == $strona) {
                $link = "<strong>$link</strong>";
            }
            $przewijanie .= " $link ";
        }

        $show_all_link = '';
        if ($show_all and ($stron > 1)) {
            $show_all_link = '<a href="'.$adres.$prefix.'page=0">'._('Show all').'</a>';
        }
        if ($strona == 0 and ($show_all_link != '')) {
            $show_all_link = "<strong>$show_all_link</strong>";
        }
        if ($show_all_link != '') {
            $show_all_link = ' :: '.$show_all_link;
        }

        $przewijanie = trim($przewijanie);
        $przewijanie = "$t_koniec $lt [$przewijanie] $gt $t_poczatek $show_all_link";

        $return['naglowek'] = '<div style="text-align:right;margin:15px auto 15px auto; ">'.sprintf(_('Showing items %s - %s (of %s) :: %s'), $od, $do + 1, $rozmiar_tablicy, $przewijanie).'</div>';
        $return['naglowek_bez_stron'] = '<div style="text-align:right;margin:15px auto 15px auto; ">'.sprintf(_('Showing items %s - %s (of %s)'), $od, $do + 1, $rozmiar_tablicy).'</div>';
        $return['po_ile'] = $po_ile;
        $return['od'] = $rozmiar_tablicy - $od;

        if ($strona == '0') {
            $return['od'] = 0;
            $return['po_ile'] = $rozmiar_tablicy;
        }

        return $return;
    }
}
