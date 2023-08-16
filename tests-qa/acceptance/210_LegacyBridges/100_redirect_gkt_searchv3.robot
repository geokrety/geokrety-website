*** Settings ***
Library         RequestsLibrary
Resource        ../ressources/vars/Urls.robot

*** Test Cases ***

Should Redirect To New Url
    Go To Url                            ${GK_URL}/gkt/search_v3.php    redirect=${PAGE_LEGACY_GKT_SEARCH_URL}

Should Redirect To A 302
    Create Session     geokrety          ${GK_URL}
    GET On Session     geokrety          /gkt/search_v3.php         expected_status=302  allow_redirects=${false}

Unset lat/lon
    Go To Url                            ${GK_URL}/gkt/search_v3.php                      redirect=${PAGE_LEGACY_GKT_SEARCH_URL}

Valid lat/lon
    Go To Url                            url=${GK_URL}/gkt/search_v3.php?lat=43&lon=6     redirect=${NO_REDIRECT_CHECK}
    Location Should Be                   url=${PAGE_LEGACY_GKT_SEARCH_URL}?lat=43&lon=6

Empty lat/lon
    Go To Url                            url=${GK_URL}/gkt/search_v3.php?lat=&lon=        redirect=${NO_REDIRECT_CHECK}
    Location Should Be                   url=${PAGE_LEGACY_GKT_SEARCH_URL}?lat=&lon=
