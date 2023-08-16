*** Settings ***
Library         RequestsLibrary
Resource        ../../ressources/Authentication.robot
Resource        ../../ressources/Users.robot
Variables       ../../ressources/vars/users.yml
Test Setup      Test Setup

*** Variables ***

${NEW_MAIL} =    somethingelse+qa@geokrety.org

*** Test Cases ***

Dismiss button doesn't invalidate the token
    Dismiss Button

    Mailbox Open Message ${1}
    Click Link With Text                    Do not change!
    Page Should Not Contain                 Sorry this token is not valid, already used or expired.

Once decision taken, token is disabled - new user
    Dismiss Button

    Mailbox Open Message ${2}
    Click Link With Text                    Validate your new email address
    Page Should Not Contain                 Sorry this token is not valid, already used or expired.

*** Keyword ***
Dismiss Button
    Email Change                            ${NEW_MAIL}    ${TRUE}
    Go To Url                               ${PAGE_DEV_MAILBOX_SECOND_MAIL_URL}
    Click Link With Text                    Validate your new email address
    Click Link                              ${USER_EMAIL_VALIDATION_DISMISS_BUTTON}

*** Keyword ***

Test Setup
    Clear Database And Seed ${1} users
    Sign In ${USER_1.name} Fast
