*** Settings ***
Library         SeleniumLibrary  timeout=10  implicit_wait=0
Library         RequestsLibrary
Resource        ../functions/FunctionsGlobal.robot
Resource        ../vars/users.resource
Force Tags      login2secid    legacy
Test Setup      Seed



*** Test Cases ***

Should Redirect To New Url
    Go To Url                            ${GK_URL}/api-login2secid.php
    Location Should Contain              /api/v1/login/secid

    Create Session     geokrety          ${GK_URL}
    GET On Session     geokrety          /api-login2secid.php         expected_status=302  allow_redirects=${false}
    Delete All Sessions

Post To Old Url Dont Redirect
    Create Session     geokrety          ${GK_URL}
    ${resp} =                            POST On Session    geokrety          /api-login2secid.php         expected_status=400  allow_redirects=${false}
    Should Be Equal As Strings           Please provide 'login' and 'password' parameters.    ${resp.content}
    Delete All Sessions

Test Form - valid
    Go To Url                             ${GK_URL}/api-login2secid.php
    Input Text                            //input[@name="login"]            ${USER_1.name}
    Input Text                            //input[@name="password"]         password
    Click Button                          //button
    Page Should Contain                   ${USER_1.secid}

Test Form - invalid
    Go To Url                             ${GK_URL}/api-login2secid.php
    Input Text                            //input[@name="login"]            ${USER_1.name}
    Input Text                            //input[@name="password"]         wrongpassword
    Click Button                          //button
    Page Should Not Contain               ${USER_1.secid}
    Page Should Contain                   Username and password doesn't match.
