*** Settings ***
Library         RequestsLibrary
Resource        ../../ressources/Authentication.robot
Variables       ../../ressources/vars/users.yml
Suite Setup     Suite Setup

*** Test Cases ***

Anonymous users should not access form
    Sign Out Fast
    Go To Url                               ${PAGE_USER_CHANGE_LANGUAGE_URL}    redirect=${PAGE_SIGN_IN_URL}
    Flash message shown                     ${UNAUTHORIZED}

User himself can access form - Form
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_USER_CHANGE_LANGUAGE_URL}
    Page Should Not Contain                 ${UNAUTHORIZED}
    Wait Until Panel                        Choose your preferred language

User himself can access form - Modal
    Sign In ${USER_1.name} Fast
    Go To User ${1}
    Click Button                            ${USER_PROFILE_LANGUAGE_EDIT_BUTTON}
    Page Should Not Contain                 ${UNAUTHORIZED}
    Wait Until Modal                        Choose your preferred language

Expired session ask to login
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_USER_CHANGE_LANGUAGE_URL}
    Press Keys                              None      CTRL+T
    Sign Out Fast
    Press Keys                              None      CTRL+W

*** Keywords ***

Suite Setup
    Clear Database And Seed ${1} users
