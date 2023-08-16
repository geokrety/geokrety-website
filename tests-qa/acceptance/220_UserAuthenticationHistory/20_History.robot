*** Settings ***
Resource        ../ressources/Authentication.robot
Variables       ../ressources/vars/users.yml
Test Setup      Test Setup

*** Test Cases ***

No activity
    Sign In ${USER_1.name} Fast
    Go To Url                             ${PAGE_USER_AUTHENTICATION_HISTORY_URL}
    Page Should Contain                   No activity yet!

Password authentication
    Sign Out Fast
    Go To Url                             ${PAGE_HOME_URL}    redirect=${PAGE_HOME_URL_EN}
    Sign In User                          ${USER_1.name}
    Go To Url                             ${PAGE_USER_AUTHENTICATION_HISTORY_URL}
    Page Should Not Contain               No activity yet!
    Table Cell Should Contain             ${USER_AUTHENTICATION_HISTORY_TABLE}    ${2}    ${1}    Success
    Table Cell Should Contain             ${USER_AUTHENTICATION_HISTORY_TABLE}    ${2}    ${2}    password

Secid authentication
    Go To Url                             ${GK_URL}/api/v1/login/secid
    Input Text                            //input[@name="login"]            ${USER_1.name}
    Input Text                            //input[@name="password"]         password
    Click Button                          //button
    Page Should Contain                   ${USER_1.secid}

    Sign In ${USER_1.name} Fast
    Go To Url                             ${PAGE_USER_AUTHENTICATION_HISTORY_URL}
    Wait For Condition 	                  return jQuery.active == 0
    Table Cell Should Contain             ${USER_AUTHENTICATION_HISTORY_TABLE}    ${2}    ${1}    Success
    Table Cell Should Contain             ${USER_AUTHENTICATION_HISTORY_TABLE}    ${2}    ${2}    api2secid

Secid authentication - invalid
    # Bad login
    Go To Url                             ${GK_URL}/api/v1/login/secid
    Input Text                            //input[@name="login"]            ${USER_1.name}
    Input Text                            //input[@name="password"]         wrongpassword
    Click Button                          //button
    Page Should Not Contain               ${USER_1.secid}
    Page Should Contain                   Username and password doesn't match.

    # Good login
    Go To Url                             ${GK_URL}/api/v1/login/secid
    Input Text                            //input[@name="login"]            ${USER_1.name}
    Input Text                            //input[@name="password"]         password
    Click Button                          //button
    Page Should Contain                   ${USER_1.secid}

    Sign In ${USER_1.name} Fast
    Go To Url                             ${PAGE_USER_AUTHENTICATION_HISTORY_URL}
    Wait For Condition                    return jQuery.active == 0
    Table Cell Should Contain             ${USER_AUTHENTICATION_HISTORY_TABLE}    ${2}    ${1}    Success
    Table Cell Should Contain             ${USER_AUTHENTICATION_HISTORY_TABLE}    ${2}    ${2}    api2secid
    Table Cell Should Contain             ${USER_AUTHENTICATION_HISTORY_TABLE}    ${3}    ${1}    Failure
    Table Cell Should Contain             ${USER_AUTHENTICATION_HISTORY_TABLE}    ${3}    ${2}    api2secid



*** Keywords ***

Test Setup
    Clear Database And Seed ${2} users
