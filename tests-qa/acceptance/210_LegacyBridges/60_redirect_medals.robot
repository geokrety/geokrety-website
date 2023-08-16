*** Settings ***
Library         RequestsLibrary
Resource        ../ressources/vars/Urls.robot

*** Test Cases ***

Should Redirect To New Url
    Go To Url                            ${GK_URL}/templates/medal-pi.png    redirect=${NO_REDIRECT_CHECK}
    Location Should Match Regexp         /images/medals/medal-pi.png$

Should Redirect To Bucket Url Link
    Create Session     geokrety          ${GK_URL}
    GET On Session     geokrety          /templates/medal-pi.png         expected_status=301  allow_redirects=${false}
