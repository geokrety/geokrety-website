*** Settings ***
Library         RequestsLibrary
Resource        ../../ressources/Authentication.robot
Resource        ../../ressources/Users.robot
Variables       ../../ressources/vars/users.yml
Test Setup      Test Setup

*** Test Cases ***

Anonymous users - doen't see the banner
    Go To Home
    Page Should Not Contain                 Sorry, but your account has no password registered and no OAuth connection.

User with a password - doesn't see the banner
    Seed ${1} users
    Sign In ${USER_1.name} Fast
    Go To Home
    Page Should Not Contain                 Sorry, but your account has no password registered and no OAuth connection.

User with no password - see the banner
    Seed ${1} users without password
    Sign In ${USER_1.name} Fast
    Go To Home
    Page Should Contain                     Sorry, but your account has no password registered and no OAuth connection.

User with no password but with social auth - doesn't see the banner
    Seed ${1} users without password with social_auth_provider_id ${1}
    Sign In ${USER_1.name} Fast
    Go To Home
    Page Should Not Contain                 Sorry, but your account has no password registered and no OAuth connection.

User with password and with social auth - doesn't see the banner
    Seed ${1} users with social_auth_provider_id ${1}
    Sign In ${USER_1.name} Fast
    Go To Home
    Page Should Not Contain                 Sorry, but your account has no password registered and no OAuth connection.

*** Keywords ***

Test Setup
    Clear Database
