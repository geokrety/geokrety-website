*** Settings ***
Library         RequestsLibrary
Resource        ../ressources/Authentication.robot
Variables       ../ressources/vars/users.yml
Test Setup      Test Setup

*** Test Cases ***

Create an account
    Go To Url                 ${PAGE_REGISTER_URL}
    Fill Registration Form    ${USER_2.name}
    ...                       email=${USER_2.email}
    ...                       language=${USER_2.language}
    ...                       daily_mail=${USER_2.daily_mail}
    Click Button              ${REGISTRATION_REGISTER_BUTTON}

    Go To Url                 ${PAGE_DEV_MAILBOX_URL}
    Element Should Contain    ${DEV_MAILBOX_FIRST_MAIL_LINK}    Bienvenue sur GeoKrety.org

*** Keywords ***

Test Setup
    Clear Database
    Sign Out Fast
