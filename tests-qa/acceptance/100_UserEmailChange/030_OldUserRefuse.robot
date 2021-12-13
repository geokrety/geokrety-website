*** Settings ***
Resource        ../functions/FunctionsGlobal.robot
Resource        ../functions/PageEmailChange.robot
Resource        ../vars/users.resource
Force Tags      Users Details    Email
Test Setup      Clear DB And Seed 1 users

*** Variables ***
${NEW_MAIL} =    somethingelse+qa@geokrety.org

*** Test Cases ***

OLD mail REFUSE change
    Valid email change - page form          ${NEW_MAIL}    ${TRUE}
    Go To Url                               ${PAGE_DEV_MAILBOX_FIRST_MAIL_URL}
    Click Link With Text                    Do not change!
    Wait Until Panel                        Do you confirm changing your email address?
    Page Should Contain                     ${USER_1.email}
    Page Should Contain                     ${NEW_MAIL}
    Click Button                            ${USER_EMAIL_VALIDATION_REFUSE_BUTTON}
    Flash message shown                     No change has been processed. This token is now revoked.

Once decision taken, token is disabled - old user
    Refuse change
    Go To Url                               ${PAGE_DEV_MAILBOX_FIRST_MAIL_URL}
    Click Link With Text                    Do not change!
    Flash message shown                     Sorry this token is not valid, already used or expired.

Once decision taken, token is disabled - new user
    Refuse change
    Go To Url                               ${PAGE_DEV_MAILBOX_SECOND_MAIL_URL}
    Click Link With Text                    Validate your new email address
    Flash message shown                     Sorry this token is not valid, already used or expired.

*** Keyword ***
Refuse change
    Valid email change - page form          ${NEW_MAIL}    ${TRUE}
    Go To Url                               ${PAGE_DEV_MAILBOX_FIRST_MAIL_URL}
    Click Link With Text                    Do not change!
    Click Button                            ${USER_EMAIL_VALIDATION_REFUSE_BUTTON}
