<?php

require_once '__sentry.php';

// details of specified user śćńółźćą

// smarty cache -- above this declaration should be wybierz_jezyk.php!
$smarty_cache_this_page = 3800; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = _('Terms of use');

$TRESC = _(
    '<p>This document sets the conditions of use of "geokrety.org" - a voluntarily operated Internet geocaching service available at https://geokrety.org (hereinafter referred to as the "Service"). Each person registering an account (hereinafter called a "User") agrees to observe the rules of these terms and conditions, starting from the beginning of the registration procedure.</p>

<p>The service is monitored and supervised by a group of volunteer supporters, hereinafter referred to as GK Team. GK Team is not responsible for the content posted by users on the Site or for damages resulting from the use of information from the Service.</p>

<p>You must ensure that information published by the user in the Site <strong>content must not affect the existing law</strong>. In particular, the content published by the user may not violate the copyright of third parties. All content (including descriptions of the Geokrety, illustrations and all their entries in the logs) made available by publishing them on our site users <strong>are licensed under Creative Commons</strong> BY-NC-SA version 2.5, whose complete content is available online at <a href="https://creativecommons.org/licenses/by-nc-sa/2.5/">https://creativecommons.org/licenses/by-nc-sa/2.5/</a>. Public domain content is also admitted. The user is responsible directly to the copyright holder for any violations in this area associated with him published by the content on our site.</p>

<p>We don`t provide any warranty or guarantee as to the accuracy, timeliness, performance, completeness or suitability of the information and materials found or offered on this website for any particular purpose. You acknowledge that such information and materials may contain inaccuracies or errors and we expressly exclude liability for any such inaccuracies or errors to the fullest extent permitted by law.</p>

<p>Content published by you on the site may not contain vulgar expressions , abusive, or illegal content. </p>'
);

// --------------------------------------------------------------- SMARTY ---------------------------------------- //

require_once 'smarty.php';
