*** Settings ***
Library         RequestsLibrary
Resource        ../ressources/Authentication.robot
Variables       ../ressources/vars/users.yml
Test Setup      Test Setup

*** Variables ***

${MSG_PASSWORD_DO_NOT_MATCH} =          Username and password doesn't match.

*** Test Cases ***

Good password
    Sign In User                        ${USER_1.name}
    Page Should Not Contain             ${MSG_PASSWORD_DO_NOT_MATCH}
    Page Should Contain                 Welcome on board
    User Is Connected

Wrong password
    Sign In User                        ${USER_1.name}    bad password
    Page Should Contain                 ${MSG_PASSWORD_DO_NOT_MATCH}
    User Is Not Connected
    Location Should Contain             ${PAGE_SIGN_IN_URL}?goto=

Non existent username
    Sign In User                        someone inexistent    password
    Page Should Contain                 ${MSG_PASSWORD_DO_NOT_MATCH}
    User Is Not Connected
    Location Should Contain             ${PAGE_SIGN_IN_URL}?goto=

*** Keywords ***

Test Setup
    Clear Database
    Sign Out Fast
    Empty Dev Mailbox Fast
    Seed 1 users
