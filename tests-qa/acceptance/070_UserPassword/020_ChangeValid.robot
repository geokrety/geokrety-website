*** Settings ***
Resource        ../functions/FunctionsGlobal.robot
Resource        ../functions/PagePasswordChange.robot
Resource        ../vars/users.resource
Force Tags      Users Details    Security
Test Setup      Clear DB And Seed 1 users

*** Test Cases ***

Valid password change - modal
    Sign In ${USER_1.name} Fast
    Go To User 1 url
    Click Link                              ${USER_PROFILE_PASSWORD_EDIT_BUTTON}
    Wait Until Modal                        Change your password
    Fill Password Change Form               password    newpass    newpass
    Click Button                            ${MODAL_DIALOG_SUBMIT_BUTTON}
    Flash message shown                     Your password has been changed.

Valid password change - page form
    Valid password change - page form

Confirmation mail should be sent
    Valid password change - page form

    Mailbox Should Contain 1 Messages
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_DEV_MAILBOX_URL}
    ${rowCount}=                            Get Element Count                   ${DEV_MAILBOX_MAILS_TABLE_ROWS}
    Should Be Equal As Integers             1   ${rowCount}
    Page Should Contain                     Your password has been changed
    Click Link                              ${DEV_MAILBOX_FIRST_MAIL_LINK}
    Page Should Contain                     Congratulation
    Page Should Contain                     Your password has been successfully changed.

*** Keywords ***

Valid password change - page form
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_USER_CHANGE_PASSWORD_URL}
    Wait Until Panel                        Change your password
    Fill Password Change Form               password    newpass    newpass
    Click Button                            ${MODAL_PANEL_SUBMIT_BUTTON}
    Flash message shown                     Your password has been changed.
