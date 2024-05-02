*** Settings ***
Library         RequestsLibrary
Resource        ../ressources/Authentication.robot
Variables       ../ressources/vars/users.yml
Suite Setup     Suite Setup

*** Test Cases ***

Should Redirect To New Url
    Go To Url                            ${GK_URL}/api-login2secid.php    redirect=${GK_URL}/api/v1/login/secid

    Create Session     geokrety          ${GK_URL}
    GET On Session     geokrety          /api-login2secid.php         expected_status=302  allow_redirects=${false}
    Delete All Sessions

Post To Old Url Dont Redirect
    Create Session     geokrety          ${GK_URL}
    ${resp} =                            POST On Session    geokrety          url=/api-login2secid.php         expected_status=400  allow_redirects=${false}
    Should Be Equal As Strings           error 3 Please provide 'login' and 'password' parameters.    ${resp.content}
    Delete All Sessions

Test Form - valid
    Go To Url                             ${GK_URL}/api-login2secid.php     redirect=${GK_URL}/api/v1/login/secid
    Input Text                            //input[@name="login"]            ${USER_1.name}
    Input Text                            //input[@name="password"]         password
    Click Button                          //button
    Page Should Contain                   ${USER_1.secid}

Test Form - invalid
    Go To Url                             ${GK_URL}/api-login2secid.php     redirect=${GK_URL}/api/v1/login/secid
    Input Text                            //input[@name="login"]            ${USER_1.name}
    Input Text                            //input[@name="password"]         wrongpassword
    Click Button                          //button
    Page Should Not Contain               ${USER_1.secid}
    Page Should Contain                   Username and password doesn't match.

Test Login - Invalid user
    [Tags]    EmailTokenBase
    Clear Database
    Seed 1 users with status ${USER_ACCOUNT_STATUS_INVALID}
    Go To Url                             ${GK_URL}/api-login2secid.php     redirect=${GK_URL}/api/v1/login/secid
    Input Text                            //input[@name="login"]            ${USER_1.name}
    Input Text                            //input[@name="password"]         password
    Click Button                          //button
    Page Should Not Contain               ${USER_1.secid}
    Page Should Contain                   Your account is not valid
    Mailbox Should Contain ${0} Messages

Test Login - Valid user
    Clear Database
    Seed 1 users with status ${USER_ACCOUNT_STATUS_VALID}
    Go To Url                             ${GK_URL}/api-login2secid.php     redirect=${GK_URL}/api/v1/login/secid
    Input Text                            //input[@name="login"]            ${USER_1.name}
    Input Text                            //input[@name="password"]         password
    Click Button                          //button
    Page Should Contain                   ${USER_1.secid}

Test Login - Imported user
    [Tags]    EmailTokenBase
    Clear Database
    Seed 1 users with status ${USER_ACCOUNT_STATUS_IMPORTED}
    Go To Url                             ${GK_URL}/api-login2secid.php     redirect=${GK_URL}/api/v1/login/secid
    Input Text                            //input[@name="login"]            ${USER_1.name}
    Input Text                            //input[@name="password"]         password
    Click Button                          //button
    Page Should Contain                   ${USER_1.secid}


*** Keywords ***

Suite Setup
    Clear Database And Seed ${1} users
    Sign Out Fast
