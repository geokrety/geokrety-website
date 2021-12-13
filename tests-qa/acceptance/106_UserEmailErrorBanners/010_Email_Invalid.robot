*** Settings ***
Resource        ../functions/FunctionsGlobal.robot
Force Tags      Users Details    Username
Resource        ../vars/users.resource
Test Setup     Seed Test

*** Test Cases ***

Banner not visible for logged out users
    Go To Url                               ${PAGE_HOME_URL}
    Page Should Not Contain                 Sorry, but we have troubles sending you email notifications. Is your email still valid?
    Page Should Not Contain                 Sorry, but your account has no email registered. You will not be able to recover from a password loss! Also, you will not receive daily notifications of your GeoKrety or watched GeoKrety.


User with valid email doesn't see the banner
    Seed 1 users
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_HOME_URL}
    Page Should Not Contain                 Sorry, but we have troubles sending you email notifications. Is your email still valid?
    Page Should Not Contain                 Sorry, but your account has no email registered. You will not be able to recover from a password loss! Also, you will not receive daily notifications of your GeoKrety or watched GeoKrety.

User with missing email see the banner
    Go To Url                               ${PAGE_SEED_USER}/1?noemail\=true
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_HOME_URL}
    Page Should Not Contain                 Sorry, but we have troubles sending you email notifications. Is your email still valid?
    Page Should Contain                     Sorry, but your account has no email registered. You will not be able to recover from a password loss! Also, you will not receive daily notifications of your GeoKrety or watched GeoKrety.


User with invalid email see the banner
    [Template]    Seed user with invalid email
    1
    2
    3

*** Keywords ***

Seed Test
    Clear Database

Seed user with invalid email
    [Arguments]    ${email_invalid}
    Clear Database
    Go To Url                               ${PAGE_SEED_USER}/1?email_invalid\=${email_invalid}
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_HOME_URL}
    Page Should Contain                     Sorry, but we have troubles sending you email notifications. Is your email still valid?
    Page Should Not Contain                 Sorry, but your account has no email registered. You will not be able to recover from a password loss! Also, you will not receive daily notifications of your GeoKrety or watched GeoKrety.
