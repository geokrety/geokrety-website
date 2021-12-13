*** Settings ***
Resource        ../functions/FunctionsGlobal.robot
Resource        ../functions/PageEmailChange.robot
Resource        ../vars/users.resource
Force Tags      Users Details    Email
Test Setup      Clear DB And Seed 1 users

*** Variables ***
${NEW_MAIL} =    somethingelse+qa@geokrety.org

*** Test Cases ***

NEW mail ACCEPT change
    Valid email change - page form          ${NEW_MAIL}    ${TRUE}
    Go To Url                               ${PAGE_DEV_MAILBOX_SECOND_MAIL_URL}
    Click Link With Text                    Validate your new email address
    Wait Until Panel                        Do you confirm changing your email address?
    Page Should Contain                     ${USER_1.email}
    Page Should Contain                     ${NEW_MAIL}
    Click Button                            ${USER_EMAIL_VALIDATION_ACCEPT_BUTTON}
    Flash message shown                     Your email address has been validated.

Once decision taken, token is disabled - old user
    Accept change
    Go To Url                               ${PAGE_DEV_MAILBOX_FIRST_MAIL_URL}
    Click Link With Text                    Do not change!
    Flash message shown                     Sorry this token is not valid, already used or expired.

Once decision taken, token is disabled - new user
    Accept change
    Go To Url                               ${PAGE_DEV_MAILBOX_SECOND_MAIL_URL}
    Click Link With Text                    Validate your new email address
    Flash message shown                     Sorry this token is not valid, already used or expired.

*** Keyword ***
Accept change
    Valid email change - page form          ${NEW_MAIL}    ${TRUE}
    Go To Url                               ${PAGE_DEV_MAILBOX_SECOND_MAIL_URL}
    Click Link With Text                    Validate your new email address
    Click Button                            ${USER_EMAIL_VALIDATION_ACCEPT_BUTTON}
