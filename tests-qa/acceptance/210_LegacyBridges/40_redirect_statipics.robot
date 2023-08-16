*** Settings ***
Library         RequestsLibrary
Resource        ../ressources/vars/Urls.robot

*** Test Cases ***

Should Redirect To New Url
    Go To Url                            ${GK_URL}/statpics/3807.png    redirect=${NO_REDIRECT_CHECK}
    Location Should Match Regexp         /statpic/3807.png$

Should Redirect To Bucket Url Link
    Create Session     geokrety          ${GK_URL}
    GET On Session     geokrety          /statpics/3807.png         expected_status=301  allow_redirects=${false}
