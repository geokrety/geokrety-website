*** Settings ***
Library         RequestsLibrary
Resource        ../../ressources/Authentication.robot
Variables       ../../ressources/vars/users.yml
Suite Setup     Suite Setup


*** Test Cases ***

Anonymous users should not access form
    Sign Out Fast
    Go To Url                               ${PAGE_USER_REFRESH_SECID_URL}    redirect=${PAGE_SIGN_IN_URL}
    Flash message shown                     ${UNAUTHORIZED}

User himself can access form - Form
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_USER_REFRESH_SECID_URL}
    Page Should Not Contain                 ${UNAUTHORIZED}
    Wait Until Panel                        Refresh your secid?

User himself can access form - Modal
    Sign In ${USER_1.name} Fast
    Go To User ${USER_1.id}
    Click Link                              ${USER_PROFILE_SECID_REFRESH_BUTTON}
    Page Should Not Contain                 ${UNAUTHORIZED}
    Wait Until Modal                        Refresh your secid?

Expired session ask to login
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_USER_REFRESH_SECID_URL}
    Press Keys                              None      CTRL+T
    Sign Out Fast
    Press Keys                              None      CTRL+W

*** keywords ***

Suite Setup
    Clear Database And Seed ${1} users
