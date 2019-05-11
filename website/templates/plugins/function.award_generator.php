<?php

/**
 * Determine granted awards given geokrety count.
 *
 * @return awards array of indexed values
 *                index key: award title suffix
 *                value: award image names
 */
function determineGrantedAwards($count) {
    $awards = array();
    if ($count >= 1) {
        $awards['1'] = 'medal-1-1.png';
    }
    if ($count >= 10) {
        $awards['10'] = 'medal-1-2.png';
    }
    if ($count >= 20) {
        $awards['20'] = 'medal-1-3.png';
    }
    if ($count >= 50) {
        $awards['50'] = 'medal-1-4.png';
    }
    if ($count >= 100) {
        $awards['100'] = 'medal-bialy.png';
    }
    if ($count >= 120) {
        $awards['5! = 120'] = 'medal-120.png';
    }
    if ($count >= 200) {
        $awards['200'] = 'medal-brazowy.png';
    }
    if ($count >= 314) {
        $awards['100* Pi = 100 * 3.14 = 314'] = 'medal-pi.png';
    }
    if ($count >= 500) {
        $awards['500'] = 'medal-srebrny.png';
    }
    if ($count >= 512) {
        $awards['2^9 = 512'] = 'medal-512.png';
    }
    if ($count >= 720) {
        $awards['6! = 1*2*3*4*5*6 = 720'] = 'medal-720.png';
    }
    if ($count >= 800) {
        $awards['800'] = 'medal-zloty.png';
    }
    if ($count >= 1000) {
        $awards['1000'] = 'medal-1000.png';
    }
    if ($count >= 1024) {
        $awards['2^10 = 1024'] = 'medal-1024.png';
    }
    if ($count >= 2000) {
        $awards['2000'] = 'medal-2000.png';
    }
    if ($count >= 3000) {
        $awards['3000'] = 'medal-3000.png';
    }
    if ($count >= 5000) {
        $awards['5000'] = 'medal-5000.png';
    }
    if ($count >= 5040) {
        $awards['7! = 1*2*3*4*5*6*7 = 5040'] = 'medal-5040.png';
    }
    if ($count >= 10000) {
        $awards['10000'] = 'medal-10000.png';
    }

    return $awards;
}

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.award_generator.php
 * Type:     function
 * Name:     award_generator
 * Purpose:  generate awards info, and store them in `var`
 * -------------------------------------------------------------
 */
function smarty_function_award_generator(array $params, Smarty_Internal_Template $template) {
    if (empty($params['var'])) {
        trigger_error("award_generator: missing 'var' parameter");

        return;
    }
    $template->assign($params['var'], determineGrantedAwards($params['count']));
}
