*** Settings ***
Library         RequestsLibrary
Resource        ../ressources/Authentication.robot
Variables       ../ressources/vars/waypoints.yml

*** Test Cases ***

Parameter wpt Must Be Provided
    Go To Url                            ${PAGE_LEGACY_SEARCH_BY_WAYPOINT_URL}
    Page Should Contain                  Waypoint parameter must be provided.

    Create Session     geokrety          ${GK_URL}
    GET On Session     geokrety          url=szukaj.php                               expected_status=400


Should Redirect To New Url
    Create Session     geokrety          ${GK_URL}
    GET On Session     geokrety          url=szukaj.php?wpt=${WPT_GC_1.id}            expected_status=301  allow_redirects=${false}

    Go To Url                            url=${PAGE_LEGACY_SEARCH_BY_WAYPOINT_URL}?wpt=${WPT_GC_1.id}    redirect=${NO_REDIRECT_CHECK}
    Location With Param Should Be        ${PAGE_SEARCH_BY_WAYPOINT_URL}               waypoint=${WPT_GC_1.id}
