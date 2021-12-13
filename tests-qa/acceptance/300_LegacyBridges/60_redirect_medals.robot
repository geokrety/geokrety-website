*** Settings ***
Library         RequestsLibrary
Resource        ../functions/FunctionsGlobal.robot
Force Tags      Redirect    legacy    medals

*** Test Cases ***

Should Redirect To New Url
    Go To Url                            ${GK_URL}/templates/medal-pi.png
    Location Should Contain              /images/medals/medal-pi.png

Should Redirect To Bucket Url Link
    Create Session     geokrety          ${GK_URL}
    GET On Session     geokrety          /templates/medal-pi.png         expected_status=301  allow_redirects=${false}
