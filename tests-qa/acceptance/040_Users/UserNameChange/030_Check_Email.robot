*** Settings ***
Library         RequestsLibrary
Resource        ../../ressources/Authentication.robot
Variables       ../../ressources/vars/users.yml
Test Setup      Test Setup
*** Test Cases ***

Users without mail address cannot change their username
    Seed ${1} users without email
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_USER_CHANGE_USERNAME_URL}    redirect=${PAGE_USER_1_PROFILE_URL}
    Page Should Contain                     Sorry, to use this feature, you must have a valid registered email address.

Users without valid mail address cannot change their username
    [Template]    Seed user with invalid email
    1
    2
    3

*** Keywords ***

Test Setup
    Clear Database

Seed user with invalid email
    [Arguments]    ${email_invalid}
    Seed ${1} users with email status ${email_invalid}
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_USER_CHANGE_USERNAME_URL}    redirect=${PAGE_USER_1_PROFILE_URL}
    Page Should Contain                     Sorry, to use this feature, you must have a valid registered email address.
