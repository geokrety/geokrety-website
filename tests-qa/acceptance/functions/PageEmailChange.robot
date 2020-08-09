*** Settings ***
Resource        FunctionsGlobal.robot

*** Keywords ***

Fill Email Change Form
    [Arguments]    ${email}    ${daily_mail}
    Input Text                                      ${USER_EMAIL_EMAIL_INPUT}           ${email}
    Run Keyword If    ${daily_mail} == ${TRUE}      Select Checkbox                     ${USER_EMAIL_DAILY_MAIL_CHECKBOX}
    Run Keyword If    ${daily_mail} == ${FALSE}     Unselect Checkbox                   ${USER_EMAIL_DAILY_MAIL_CHECKBOX}

Valid email change - page form
    [Arguments]    ${email}    ${daily_mail}
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_USER_CHANGE_EMAIL_URL}
    Wait Until Panel                        Update your email address
    Fill Email Change Form                  ${email}    ${daily_mail}
    Click Button                            ${MODAL_PANEL_SUBMIT_BUTTON}
    Flash message shown                     A confirmation email was sent to your new address.
    Page Should Contain                     You have a pending email validation.

Change daily mail preferences
    [Arguments]    ${daily_mail}
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_USER_CHANGE_EMAIL_URL}
    Wait Until Panel                        Update your email address
    Fill Email Change Form                  ${USER_1.email}    ${daily_mail}
    Click Button                            ${MODAL_PANEL_SUBMIT_BUTTON}
