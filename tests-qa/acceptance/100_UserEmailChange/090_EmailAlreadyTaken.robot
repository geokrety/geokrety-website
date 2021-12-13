*** Settings ***
Resource        ../functions/FunctionsGlobal.robot
Resource        ../functions/PageEmailChange.robot
Resource        ../vars/users.resource
Force Tags      Users Details    Email
Test Setup      Clear DB And Seed 2 users

*** Test Cases ***

Email address already PENDING by user himself
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_USER_CHANGE_EMAIL_URL}
    Wait Until Panel                        Update your email address
    Fill Email Change Form                  ${USER_3.email}    ${TRUE}
    Click Button                            ${MODAL_PANEL_SUBMIT_BUTTON}

    Go To Url                               ${PAGE_USER_CHANGE_EMAIL_URL}
    Wait Until Panel                        Update your email address
    Fill Email Change Form                  ${USER_3.email}    ${TRUE}
    Click Button                            ${MODAL_PANEL_SUBMIT_BUTTON}
    Flash message shown                     The confirmation email was sent again to your new address.
    Mailbox Should Contain 4 Messages

Email address already USED by another user
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_USER_CHANGE_EMAIL_URL}
    Wait Until Panel                        Update your email address
    Fill Email Change Form                  ${USER_2.email}    ${TRUE}
    Click Button                            ${MODAL_PANEL_SUBMIT_BUTTON}
    Flash message shown                     Sorry but this mail address is already in use.

Email address already PENDING by another user
    Sign In ${USER_2.name} Fast
    Go To Url                               ${PAGE_USER_CHANGE_EMAIL_URL}
    Wait Until Panel                        Update your email address
    Fill Email Change Form                  ${USER_3.email}    ${TRUE}
    Click Button                            ${MODAL_PANEL_SUBMIT_BUTTON}

    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_USER_CHANGE_EMAIL_URL}
    Wait Until Panel                        Update your email address
    Fill Email Change Form                  ${USER_3.email}    ${TRUE}
    Click Button                            ${MODAL_PANEL_SUBMIT_BUTTON}
    Flash message shown                     Sorry but this mail address is already in use.
