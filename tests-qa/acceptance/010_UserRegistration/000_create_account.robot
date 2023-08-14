*** Settings ***
Library         RequestsLibrary
Resource        ../ressources/Authentication.robot
Variables       ../ressources/vars/users.yml
Test Setup      Test Setup

*** Variables ***

*** Test Cases ***

Link is present
    Go To Home
    User Is Not Connected
    Click Element                       ${NAVBAR_REGISTER_LINK}
    Location Should Be                  ${PAGE_REGISTER_URL}

Create first account
    ${passed} =    Run Keyword And Return Status
    ...            Register User        &{USER_1}
    Run Keyword If    not ${passed}     Fatal Error    Failed to create an initial user
    User Is Not Connected
    Mailbox Should Contain 1 Messages

Check csrf
    ${params.newsid}    Set Variable        ${1}
    Create Session                          gk      ${GK_URL}
    ${resp} =           POST On Session     gk      url=${PAGE_REGISTER_URL}?skip_csrf=False    expected_status=200
    ${body} =           Convert To String   ${resp.content}
    Should Contain                          ${body}    CSRF error, please try again.
    Delete All Sessions

*** Keywords ***

Test Setup
    Clear Database
    Sign Out Fast
    Empty Dev Mailbox Fast
