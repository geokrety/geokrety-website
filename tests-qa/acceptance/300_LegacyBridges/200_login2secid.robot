*** Settings ***
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
    ${resp} =                            POST On Session    geokrety          url=/api-login2secid.php         expected_status=400  allow_redirects=${false}
    Should Be Equal As Strings           error 3 Please provide 'login' and 'password' parameters.    ${resp.content}
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

Test Login - Invalid user
    Clear Database
    Seed 1 users with status 0
    Go To Url                             ${GK_URL}/api-login2secid.php
    Input Text                            //input[@name="login"]            ${USER_1.name}
    Input Text                            //input[@name="password"]         password
    Click Button                          //button
    Page Should Not Contain               ${USER_1.secid}
    Page Should Contain                   Your account is not valid
    Go To Url                             ${PAGE_HOME_URL}
    Mailbox Should Contain 1 Messages

Test Login - Valid user
    Clear Database
    Seed 1 users with status 1
    Go To Url                             ${GK_URL}/api-login2secid.php
    Input Text                            //input[@name="login"]            ${USER_1.name}
    Input Text                            //input[@name="password"]         password
    Click Button                          //button
    Page Should Contain                   ${USER_1.secid}

Test Login - Imported user
    Clear Database
    Seed 1 users with status 2
    Go To Url                             ${GK_URL}/api-login2secid.php
    Input Text                            //input[@name="login"]            ${USER_1.name}
    Input Text                            //input[@name="password"]         password
    Click Button                          //button
    Page Should Contain                   ${USER_1.secid}


*** Keywords ***

Seed
    Clear DB And Seed 1 users
    Sign Out Fast
