<?php

// śćńółżć

function smarty_function_test($params, &$smarty)
{
    $answers = array('Yes',
                     'No',
                     'No way',
                     'Outlook not so good',
                     'Ask again soon',
                     'Maybe in your reality', );

    $result = array_rand($answers);

    return $answers[$result];
}
