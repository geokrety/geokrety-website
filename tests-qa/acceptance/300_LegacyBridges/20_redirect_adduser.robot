*** Settings ***
Library         RequestsLibrary
Resource        ../functions/FunctionsGlobal.robot
Force Tags      Redirect    legacy    registration

*** Test Cases ***

Should Redirect To Login
    Go To Url                            ${PAGE_LEGACY_REGISTRATION_URL}
    Location Should Be                   ${PAGE_REGISTER_URL}

Should Redirect Get 302
    Create Session     geokrety          ${GK_URL}
    GET On Session     geokrety          /adduser.php         expected_status=302  allow_redirects=${false}
