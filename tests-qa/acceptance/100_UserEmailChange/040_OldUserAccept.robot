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

OLD mail ACCEPT change
    Valid email change - page form          ${NEW_MAIL}    ${TRUE}
    Go To Url                               ${PAGE_DEV_MAILBOX_FIRST_MAIL_URL}
    Click Link With Text                    Do not change!
    Wait Until Panel                        Do you confirm changing your email address?
    Page Should Contain                     ${USER_1.email}
    Page Should Contain                     ${NEW_MAIL}
    Click Button                            ${USER_EMAIL_VALIDATION_ACCEPT_BUTTON}
    Flash message shown                     Your email address has been validated.

On accept confirmation mails should be sent
    Accept change
    Mailbox Should Contain 4 Messages
    Go To Url                               ${PAGE_DEV_MAILBOX_URL}
    Page Should Contain                     📯 Changing your email address
    Page Should Contain                     ✉️ Changing your email address
    Page Should Contain                     📯 Email address changed
    Page Should Contain                     ✉️ Email address changed

    Go To Url                               ${PAGE_DEV_MAILBOX_THIRD_MAIL_URL}
    Page Should Contain                     Congratulation
    Page Should Contain                     Your email address has been successfully changed to: ${NEW_MAIL}.
    Page Should Contain                     Revert this change!
    Page Should Contain                     Change my password!

    Go To Url                               ${PAGE_DEV_MAILBOX_FOURTH_MAIL_URL}
    Page Should Contain                     Congratulation
    Page Should Contain                     Your email address has been successfully changed.
    Page Should Contain                     Login

Token is then disabled for old email
    Accept change
    Go To Url                               ${PAGE_DEV_MAILBOX_FIRST_MAIL_URL}
    Click Link With Text                    Do not change!
    Flash message shown                     Sorry this token is not valid, already used or expired.

Token is then disabled for new email
    Accept change
    Go To Url                               ${PAGE_DEV_MAILBOX_SECOND_MAIL_URL}
    Click Link With Text                    Validate your new email address
    Flash message shown                     Sorry this token is not valid, already used or expired.

*** Keyword ***
Accept change
    Valid email change - page form          ${NEW_MAIL}    ${TRUE}
    Go To Url                               ${PAGE_DEV_MAILBOX_FIRST_MAIL_URL}
    Click Link With Text                    Do not change!
    Click Button                            ${USER_EMAIL_VALIDATION_ACCEPT_BUTTON}
