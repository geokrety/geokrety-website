*** Settings ***
Resource        ../functions/FunctionsGlobal.robot
Resource        ../functions/PageEmailChange.robot
Resource        ../vars/users.resource
Force Tags      Users Details    Email
Test Setup      Clear DB And Seed 1 users

*** Variables ***
${NEW_MAIL} =    somethingelse+qa@geokrety.org

*** Test Cases ***

Dismiss button doesn't invalidate the token
    Dismiss Button
    Go To Url                               ${PAGE_DEV_MAILBOX_FIRST_MAIL_URL}
    Click Link With Text                    Do not change!
    Page Should Not Contain                 Sorry this token is not valid, already used or expired.

Once decision taken, token is disabled - new user
    Dismiss Button
    Go To Url                               ${PAGE_DEV_MAILBOX_SECOND_MAIL_URL}
    Click Link With Text                    Validate your new email address
    Page Should Not Contain                 Sorry this token is not valid, already used or expired.

*** Keyword ***
Dismiss Button
    Valid email change - page form          ${NEW_MAIL}    ${TRUE}
    Go To Url                               ${PAGE_DEV_MAILBOX_SECOND_MAIL_URL}
    Click Link With Text                    Validate your new email address
    Click Link                              ${USER_EMAIL_VALIDATION_DISMISS_BUTTON}
