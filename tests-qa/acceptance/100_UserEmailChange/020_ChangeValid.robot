*** Settings ***
Library         SeleniumLibrary  timeout=10  implicit_wait=0
Resource        ../functions/FunctionsGlobal.robot
Resource        ../functions/PageEmailChange.robot
Resource        ../vars/users.resource
Force Tags      Users Details    Email
Test Setup      Clear DB And Seed 1 users

*** Variables ***
${NEW_MAIL} =    somethingelse+qa@geokrety.org

*** Test Cases ***

Valid email change - modal
    Sign In ${USER_1.name} Fast
    Go To User 1 url
    Click Button                            ${USER_PROFILE_EMAIL_EDIT_BUTTON}
    Wait Until Modal                        Update your email address
    Fill Email Change Form                  ${NEW_MAIL}    ${TRUE}
    Click Button                            ${MODAL_DIALOG_SUBMIT_BUTTON}
    Flash message shown                     A confirmation email was sent to your new address.
    Page Should Contain                     You have a pending email validation.

Valid email change - page form
    Valid email change - page form          ${NEW_MAIL}    ${TRUE}

Confirmation mail should be sent
    Valid email change - page form          ${NEW_MAIL}    ${TRUE}
    Mailbox Should Contain 2 Messages
    Go To Url                               ${PAGE_DEV_MAILBOX_URL}
    ${rowCount}=                            Get Element Count     ${DEV_MAILBOX_MAILS_TABLE_ROWS}
    Should Be Equal As Integers             2   ${rowCount}
    Page Should Contain                     📯 Changing your email address
    Page Should Contain                     ✉️ Changing your email address

    Go To Url                               ${PAGE_DEV_MAILBOX_FIRST_MAIL_URL}
    Page Should Contain                     Someone, hopefully you, has requested a change on your GeoKrety contact email address to: ${NEW_MAIL}.
    Page Should Contain                     Do not change!

    Go To Url                               ${PAGE_DEV_MAILBOX_SECOND_MAIL_URL}
    Page Should Contain                     Someone, hopefully you, has requested to change it's GeoKrety contact email address to yours.
    Page Should Contain                     Validate your new email address
