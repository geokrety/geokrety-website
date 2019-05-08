<?php

use benhall14\PHPPagination\Pagination as Pagination;

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.paginate.php
 * Type:     function
 * Name:     paginate
 * Purpose:  outputs a pagination
 * -------------------------------------------------------------
 */
function smarty_function_paginate(array $params, Smarty_Internal_Template $template) {
    if (empty($params['total'])) {
        trigger_error("assign: missing 'total' parameter");

        return;
    }

    $pagination = new Pagination();
    $pagination->separator('…');
    $pagination->nextText(_('Next'));
    $pagination->previousText(_('Previous'));
    $pagination->retainQueryString();
    $pagination->screenReader(false);

    $pagination->pagesBeforeSeparator(isset($params['pagesBeforeSeparator']) ? $params['pagesBeforeSeparator'] : 2);
    $pagination->pagesAroundActive(isset($params['pagesAroundActive']) ? $params['pagesAroundActive'] : 2);
    $pagination->perPage(isset($params['perPage']) ? $params['perPage'] : 20);

    if (in_array('fragment', array_keys($params)) && !empty($params['fragment'])) {
        $pagination->fragmentQueryString($params['fragment']);
    }

    return $pagination->total($params['total'])->output();
}
