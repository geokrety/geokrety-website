*** Settings ***
Library         RequestsLibrary
Resource        ../../ressources/Authentication.robot
Variables       ../../ressources/vars/users.yml
Test Setup      Test Setup

*** Variables ***

${NEW_USERNAME}    foobar


*** Test Cases ***

Changing username success
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_USER_CHANGE_USERNAME_URL}
    Wait Until Panel                        Change your username

    # Check form
    Input Text                              ${USER_CHANGE_USERNAME_INPUT}       foobar
    Simulate Event                          ${USER_CHANGE_USERNAME_INPUT}       blur
    Input validation has success            ${USER_CHANGE_USERNAME_INPUT}
    Click Button                            ${MODAL_PANEL_SUBMIT_BUTTON}

    # Success
    Location Should Be                      ${PAGE_HOME_URL_EN}
    Page Should Contain Element             ${NAVBAR_SIGN_IN_LINK}
    Flash message shown                     Username changed. Please login again.

    # Mails are sent
    Mailbox Should Contain ${1} Messages
    Mailbox Message ${1} Subject Should Contain 👥 Your username has been changed
    Mailbox Open Message ${1}
    Page Should Contain                     Someone, hopefully you, has requested a change on your GeoKrety username to: ${NEW_USERNAME}.

    # Old username cannot be used
    Go To Home
    Sign In User                            ${USER_1.name}
    Flash message shown                     Username and password doesn't match.

    # New username is working
    Sign In User                            ${NEW_USERNAME}
    Location Should Be                      ${PAGE_HOME_URL_EN}
    Element Should Contain                  ${NAVBAR_PROFILE_LINK}    ${NEW_USERNAME}
    Flash message shown                     Welcome on board

Username already used
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_USER_CHANGE_USERNAME_URL}
    Wait Until Panel                        Change your username

    Input Text                              ${USER_CHANGE_USERNAME_INPUT}       ${USER_2.name}
    Simulate Event                          ${USER_CHANGE_USERNAME_INPUT}       blur
    Input validation has error              ${USER_CHANGE_USERNAME_INPUT}
    Input validation has error help         ${USER_CHANGE_USERNAME_INPUT}       Sorry, but username "${USER_2.name}" is already used.

    Input Text                              ${USER_CHANGE_USERNAME_INPUT}       ${USER_2.name}foo
    Simulate Event                          ${USER_CHANGE_USERNAME_INPUT}       blur
    Input validation has success            ${USER_CHANGE_USERNAME_INPUT}

Cannot use a username in pending state
    Seed ${1} users with status ${0}        start_at=3
    Sign In ${USER_1.name} Fast
    Change username                         ${USER_3.name}
    Input validation has error              ${USER_CHANGE_USERNAME_INPUT}
    Input validation has error help         ${USER_CHANGE_USERNAME_INPUT}       Sorry, but username "${USER_3.name}" is already used.

Invalid accounts cannot proceed to username change
    Seed ${1} users with status ${2}        start_at=3
    Sign In ${USER_3.name} Fast
    Go To Url                               ${PAGE_USER_CHANGE_USERNAME_URL}    redirect=${PAGE_USER_3_PROFILE_URL}
    Page Should Contain                     Sorry, to use this feature, you must have a valid registered email address.

*** Keywords ***

Test Setup
    Clear Database And Seed ${2} users

Change username
    [Arguments]     ${new_username}
    Go To Url                               ${PAGE_USER_CHANGE_USERNAME_URL}
    Wait Until Panel                        Change your username
    Input Text                              ${USER_CHANGE_USERNAME_INPUT}       ${new_username}
    Click Button                            ${MODAL_PANEL_SUBMIT_BUTTON}
