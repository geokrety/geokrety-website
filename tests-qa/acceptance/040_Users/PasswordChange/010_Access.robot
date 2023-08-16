*** Settings ***
Library         RequestsLibrary
Resource        ../../ressources/Authentication.robot
Variables       ../../ressources/vars/users.yml
Suite Setup     Suite Setup

*** Test Cases ***

Anonymous users should not access form
    Sign Out Fast
    Go To Url                               ${PAGE_USER_CHANGE_PASSWORD_URL}    redirect=${PAGE_SIGN_IN_URL}
    Flash message shown                     ${UNAUTHORIZED}

User himself can access form - Form
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_USER_CHANGE_PASSWORD_URL}
    Page Should Not Contain                 ${UNAUTHORIZED}
    Wait Until Panel                        Change your password

User himself can access form - Modal
    Sign In ${USER_1.name} Fast
    Go To User ${USER_1.id}
    Click Link                              ${USER_PROFILE_PASSWORD_EDIT_BUTTON}
    Page Should Not Contain                 ${UNAUTHORIZED}
    Wait Until Modal                        Change your password

Expired session ask to login
    Sign In ${USER_1.name} Fast
    Go To User ${USER_1.id}
    Press Keys                              None      CTRL+T
    Sign Out Fast
    Press Keys                              None      CTRL+W

*** Keywords ***

Suite Setup
    Clear Database And Seed ${2} users
