*** Settings ***
Library         SeleniumLibrary  timeout=10  implicit_wait=0
Resource        ../functions/FunctionsGlobal.robot
Resource        ../functions/PagePasswordChange.robot
Resource        ../vars/users.resource
Force Tags      Users Details    Security
Test Setup      Clear DB And Seed 1 users

*** Test Cases ***

Wrong old password
    Fill Form Wrapper                       wrongpassword    newpass    newpass
    Flash message shown                     Your old password is invalid.
    Mailbox Should Contain 0 Messages

New password does not match confirm
    Fill Form Wrapper                       password    newpass    newPASS
    Input validation has success            ${USER_PASSWORD_OLD_INPUT}
    Input validation has success            ${USER_PASSWORD_NEW_INPUT}
    Input validation has error              ${USER_PASSWORD_CONFIRM_INPUT}
    Input validation has error help         ${USER_PASSWORD_CONFIRM_INPUT}      This value should be the same.

*** Keywords ***
Fill Form Wrapper
    [Arguments]    ${old}=password    ${new}=newpass    ${confirm}=${new}
    Sign In ${USER_1.name} Fast
    Go To User 1 url
    Click Link                              ${USER_PROFILE_PASSWORD_EDIT_BUTTON}
    Wait Until Modal                        Change your password
    Fill Password Change Form               ${old}    ${new}    ${confirm}
    Click Button                            ${MODAL_DIALOG_SUBMIT_BUTTON}
