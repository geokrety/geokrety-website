*** Settings ***
Library         RequestsLibrary
Resource        ../functions/FunctionsGlobal.robot
Force Tags      Redirect    legacy    statpics

*** Test Cases ***

Should Redirect To New Url
    Go To Url                            ${GK_URL}/statpics/3807.png
    Location Should Contain              /statpic/3807.png

Should Redirect To Bucket Url Link
    Create Session     geokrety          ${GK_URL}
    GET On Session     geokrety          /statpics/3807.png         expected_status=301  allow_redirects=${false}
