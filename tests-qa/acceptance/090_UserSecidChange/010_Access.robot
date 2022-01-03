*** Settings ***
Resource        ../functions/FunctionsGlobal.robot
Resource        ../vars/users.resource
Force Tags      Users Details    Security
Suite Setup     Seed

*** Test Cases ***

Anonymous users should not access form
    Sign Out Fast
    Go To Url                               ${PAGE_USER_REFRESH_SECID_URL}
    Page Should Contain                     ${UNAUTHORIZED}

User himself can access form - Form
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_USER_REFRESH_SECID_URL}
    Page Should Not Contain                 ${UNAUTHORIZED}
    Wait Until Panel                        Refresh your secid?

User himself can access form - Modal
    Sign In ${USER_1.name} Fast
    Go To User 1 url
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

Seed
    Clear DB And Seed 1 users
