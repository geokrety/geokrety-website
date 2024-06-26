*** Settings ***
Library         RequestsLibrary
Resource        ../../ressources/Authentication.robot
Variables       ../../ressources/vars/users.yml
Test Setup      Test Setup


*** Test Cases ***

Banner not visible for logged out users
    Go To Url                               ${PAGE_HOME_URL_EN}
    Page Should Not Contain                 Sorry, but we have troubles sending you email notifications.
    Page Should Not Contain                 Sorry, but your account has no email registered. You will not be able to recover from a password loss! Also, you will not receive daily notifications of your GeoKrety or watched GeoKrety.

User with valid email doesn't see the banner
    Seed ${1} users
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_HOME_URL_EN}
    Page Should Not Contain                 Sorry, but we have troubles sending you email notifications.
    Page Should Not Contain                 Sorry, but your account has no email registered. You will not be able to recover from a password loss! Also, you will not receive daily notifications of your GeoKrety or watched GeoKrety.

User with missing email see the banner
    Seed ${1} users without email
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_HOME_URL_EN}
    Page Should Not Contain                 Sorry, but we have troubles sending you email notifications.
    Page Should Not Contain                 Sorry, but your account has no email registered.
    Go To Url                               ${PAGE_USER_1_PROFILE_URL}
    Page Should Contain                     Sorry, but your account has no email registered.
    Page Should Contain                     You will not be able to recover from a password loss!
    Page Should Contain                     Also, you will not receive daily notifications of your GeoKrety or watched GeoKrety.


User with invalid email see the banner
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
    Go To Url                               ${PAGE_HOME_URL_EN}
    Page Should Contain                     Sorry, but we have troubles sending you email notifications.
    Page Should Not Contain                 Sorry, but your account has no email registered. You will not be able to recover from a password loss! Also, you will not receive daily notifications of your GeoKrety or watched GeoKrety.
