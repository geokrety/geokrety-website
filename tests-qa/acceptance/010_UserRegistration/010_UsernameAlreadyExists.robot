*** Settings ***
Resource        ../functions/PageRegistration.robot
Resource        ../vars/users.resource
Force Tags      CreateAccount
Suite Setup     Seed

*** Test Cases ***

Check username
    [Template]    Fill username
    ${USER_1.name}    Sorry, but username "${USER_1.name}" is already used.
    ${SPACE}${USER_1.name}    Sorry, but username "${USER_1.name}" is already used.
    ${SPACE}${USER_1.name}${SPACE}    Sorry, but username "${USER_1.name}" is already used.
    ${SPACE}${SPACE}${SPACE}${USER_1.name}${SPACE}${SPACE}${SPACE}    Sorry, but username "${USER_1.name}" is already used.
    ${USER_1.email}    Sorry, but username "${USER_1.email}" is already used.

*** Keywords ***
Seed

    [Documentation]         Seed an account
    Clear Database
    Seed 1 users

Fill username
    [Arguments]    ${username}    ${expected}
    Go To Url                        ${PAGE_REGISTER_URL}
    Input Text                       ${REGISTRATION_USERNAME_INPUT}    ${username}
    Simulate Event                   ${REGISTRATION_USERNAME_INPUT}    blur
    Input validation has error       ${REGISTRATION_USERNAME_INPUT}
    Input validation has error help  ${REGISTRATION_USERNAME_INPUT}   ${expected}
