*** Settings ***
Resource        ../functions/FunctionsGlobal.robot
Force Tags      Users Details    Username
Resource        ../vars/users.resource
Suite Setup     Seed

*** Test Cases ***

Anonymous users should not access form
    Sign Out Fast
    Go To Url                               ${PAGE_USER_CHANGE_USERNAME_URL}
    Page Should Contain                     ${UNAUTHORIZED}

User himself can access form - Form
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_USER_CHANGE_USERNAME_URL}
    Page Should Not Contain                 ${UNAUTHORIZED}
    Wait Until Panel                        Change your username

*** Keywords ***

Seed
    Clear DB And Seed 1 users
