*** Settings ***
Resource        ../functions/FunctionsGlobal.robot
Resource        ../vars/users.resource
Force Tags      Users Details    Email
Suite Setup     Seed

*** Test Cases ***

Anonymous users should not access form
    Sign Out Fast
    Go To Url                               ${PAGE_USER_CHANGE_EMAIL_URL}
    Page Should Contain                     Unauthorized

User himself can access form - Form
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_USER_CHANGE_EMAIL_URL}
    Page Should Not Contain                 Unauthorized
    Wait Until Panel                        Update your email address

User himself can access form - Modal
    Sign In ${USER_1.name} Fast
    Go To User 1 url
    Click Button                            ${USER_PROFILE_EMAIL_EDIT_BUTTON}
    Page Should Not Contain                 Unauthorized
    Wait Until Modal                        Update your email address

Expired session ask to login
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_USER_CHANGE_EMAIL_URL}
    Press Keys                              None      CTRL+T
    Sign Out Fast
    Press Keys                              None      CTRL+W

*** Keywords ***

Seed
    Clear DB And Seed 1 users
