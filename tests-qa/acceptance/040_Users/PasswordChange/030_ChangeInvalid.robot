*** Settings ***
Library         RequestsLibrary
Resource        ../../ressources/Authentication.robot
Resource        ../../ressources/Users.robot
Variables       ../../ressources/vars/users.yml
Test Setup      Test Setup

*** Test Cases ***

Wrong old password
    Fill Password Change Form               wrongpassword    newpass    newpass
    # Input validation has success            ${USER_PASSWORD_OLD_INPUT}
    # Input validation has success            ${USER_PASSWORD_NEW_INPUT}
    # Input validation has success            ${USER_PASSWORD_CONFIRM_INPUT}
    Click Button                            ${SUBMIT_BUTTON}
    Flash message shown                     Your old password is invalid.
    Mailbox Should Contain ${0} Messages

New password does not match confirm
    Fill Password Change Form               password    newpass    newPASS
    Click Button                            ${SUBMIT_BUTTON}
    Input validation has success            ${USER_PASSWORD_OLD_INPUT}
    Input validation has success            ${USER_PASSWORD_NEW_INPUT}
    Input validation has error              ${USER_PASSWORD_CONFIRM_INPUT}
    Input validation has error help         ${USER_PASSWORD_CONFIRM_INPUT}      This value should be the same.

*** Keywords ***

Test Setup
    Clear Database And Seed ${1} users
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_USER_CHANGE_PASSWORD_URL}
