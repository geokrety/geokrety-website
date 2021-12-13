*** Settings ***
Library         DependencyLibrary
Resource        ../functions/PageRegistration.robot
Resource        ../vars/users.resource
Force Tags      CreateAccount

*** Test Cases ***
Seed an account
    [Documentation]         Seed an account
    Clear Database
    Seed 1 users

Check Valid Username
    [Template]    Check Valid Username
    ${USER_2.name}
    ${USER_2.email}
    🦧 Orangutan
    🐂🐃🐄
    🐂🐃🐄👶👼🎅🤶
    looks like an @email.com

Check Invalid Username
    [Template]    Check Invalid Username
    ${SPACE*1}
    ${SPACE*2}
    ${SPACE*2} ${SPACE*1}
    ${SPACE*2} ${SPACE*1}


*** Keywords ***
Check Valid Username
    [Arguments]    ${username}
    Fill username                    ${username}
    Input validation has success     ${REGISTRATION_USERNAME_INPUT}

Check Invalid Username
    [Arguments]    ${username}
    Fill username                    ${username}
    Input validation has error       ${REGISTRATION_USERNAME_INPUT}

Fill username
    [Arguments]    ${username}
    Go To Url                        ${PAGE_REGISTER_URL}
    Input Text                       ${REGISTRATION_USERNAME_INPUT}    ${username}
    Simulate Event                   ${REGISTRATION_USERNAME_INPUT}    blur
