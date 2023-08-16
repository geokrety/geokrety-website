*** Settings ***
Library         RequestsLibrary
Resource        ../ressources/vars/Urls.robot

*** Test Cases ***

Should Redirect To Login
    Go To Url                            ${PAGE_LEGACY_LONGIN_URL}    redirect=${PAGE_SIGN_IN_URL}

Should Redirect Get 302
    Create Session     geokrety          ${GK_URL}
    GET On Session     geokrety          /longin.php         expected_status=302  allow_redirects=${false}
