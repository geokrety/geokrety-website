*** Settings ***
Library         RequestsLibrary
Resource        ../ressources/Authentication.robot
Resource        ../ressources/vars/Urls.robot

*** Test Cases ***

Should Redirect To Home
    Sign Out Fast
    Go To Url                                       ${PAGE_LEGACY_INDEX_URL}    redirect=${PAGE_HOME_URL_EN}

Should Redirect Get 302
    Sign Out Fast
    Create Session     geokrety          ${GK_URL}
    GET On Session     geokrety          /index.php       expected_status=302  allow_redirects=${false}

Lang Parameter Is Ignored
    Sign Out Fast
    Go To Url                                       ${PAGE_LEGACY_INDEX_URL}?lang\=fr_FR.UTF-8    redirect=${PAGE_HOME_URL_EN}
    Location Should Not Be                          ${PAGE_HOME_URL_FR}
