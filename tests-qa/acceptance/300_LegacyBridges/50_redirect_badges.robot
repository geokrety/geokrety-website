*** Settings ***
Library         RequestsLibrary
Resource        ../functions/FunctionsGlobal.robot
Force Tags      Redirect    legacy    statpics

*** Test Cases ***

Should Redirect To New Url
    Go To Url                            ${GK_URL}/templates/badges/top100-mover-2012.png
    Location Should Contain              /images/badges/top100-mover-2012.png

Should Redirect To Bucket Url Link
    Create Session     geokrety          ${GK_URL}
    GET On Session     geokrety          /templates/badges/top100-mover-2012.png         expected_status=301  allow_redirects=${false}
