*** Settings ***
Library         SeleniumLibrary  timeout=10  implicit_wait=0
Library         Dialogs
Resource        ../functions/FunctionsGlobal.robot
Resource        ../vars/users.resource
Force Tags      Users Details    Username
Test Setup     Seed

*** Test Cases ***

Changing username success
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_USER_CHANGE_USERNAME_URL}
    Wait Until Panel                        Change your username
    Input Text                              ${USER_CHANGE_USERNAME_INPUT}       foobar

    Simulate Event                          ${USER_CHANGE_USERNAME_INPUT}       blur
    Input validation has success            ${USER_CHANGE_USERNAME_INPUT}
    Click Button                            ${MODAL_PANEL_SUBMIT_BUTTON}

    Location Should Be                      ${PAGE_HOME_URL}
    Page Should Contain Element             ${NAVBAR_SIGN_IN_LINK}
    Page Should Contain                     Username changed. Please login again.

    Sign In User                            foobar
    Location Should Be                      ${PAGE_HOME_URL}
    Element Should Contain                  ${NAVBAR_PROFILE_LINK}    foobar

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

Old username can't be used to connect but new can
    Sign In ${USER_1.name} Fast
    Change username                         foobar

    Sign In User                            ${USER_1.name}
    Page Should Contain                     Username and password doesn't match

    Sign In User                            foobar
    Page Should Contain                     Welcome on board

Confirmation mail should be sent
    Sign In ${USER_1.name} Fast
    Change username                         foobar

    Mailbox Should Contain 1 Messages
    Go To Url                               ${PAGE_DEV_MAILBOX_URL}
    ${rowCount}=                            Get Element Count     ${DEV_MAILBOX_MAILS_TABLE_ROWS}
    Should Be Equal As Integers             1   ${rowCount}
    Page Should Contain                     👥 Your username has been changed

    Go To Url                               ${PAGE_DEV_MAILBOX_FIRST_MAIL_URL}
    Page Should Contain                     Someone, hopefully you, has requested a change on your GeoKrety username to: foobar.


*** Keywords ***

Seed
    Clear DB And Seed 2 users

Change username
    [Arguments]     ${new_username}
    Go To Url                               ${PAGE_USER_CHANGE_USERNAME_URL}
    Wait Until Panel                        Change your username
    Input Text                              ${USER_CHANGE_USERNAME_INPUT}       ${new_username}
    Click Button                            ${MODAL_PANEL_SUBMIT_BUTTON}
