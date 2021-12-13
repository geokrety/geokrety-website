*** Settings ***
Library         RequestsLibrary
Resource        ../functions/FunctionsGlobal.robot
Force Tags      Redirect    legacy    index

*** Test Cases ***

Should Redirect To Home
    Sign Out Fast
    Go To Url                                       ${PAGE_LEGACY_INDEX_URL}
    Location Should Be                              ${PAGE_HOME_URL}

Should Redirect Get 302
    Sign Out Fast
    Create Session     geokrety          ${GK_URL}
    GET On Session     geokrety          /index.php       expected_status=302  allow_redirects=${false}

Lang Parameter Is Ignored
    Sign Out Fast
    Go To Url                                       url=${PAGE_LEGACY_INDEX_URL}?lang=fr_FR.UTF-8
    Location Should Not Be                          ${PAGE_HOME_URL_FR}
    Location Should Be                              ${PAGE_HOME_URL}
