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
    [Tags]    EmailTokenBase    UsernameFree
    Register User        ${USER_1}
    User Is Not Connected
    Mailbox Should Contain 1 Messages

Re-create same account
    [Tags]    EmailTokenBase    sendActivationOnCreateAgain    UsernameFree
    Register User        ${USER_1}
    User Is Not Connected
    Mailbox Should Contain 1 Messages

    Go To Url                           ${PAGE_REGISTER_URL}
    Location Should Be                  ${PAGE_REGISTER_URL}

    Page Should Show Registration Form
    Fill Registration Form              ${USER_1.name}
    ...                                 email=${USER_1.email}
    ...                                 password=${USER_1.password}
    ...                                 language=${USER_1.language}
    ...                                 daily_mail=${USER_1.daily_mail}
    ...                                 terms_of_use=${USER_1.terms_of_use}
    Click Button                        ${REGISTRATION_REGISTER_BUTTON}

    User Is Not Connected
    Location Should Be                  ${PAGE_USER_1_PROFILE_URL}
    Page Should Contain                 Your account seems to already exist
    Mailbox Should Contain 2 Messages

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
