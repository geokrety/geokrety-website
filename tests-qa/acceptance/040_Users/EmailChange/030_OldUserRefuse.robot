*** Settings ***
Library         RequestsLibrary
Resource        ../../ressources/Authentication.robot
Resource        ../../ressources/Users.robot
Variables       ../../ressources/vars/users.yml
Test Setup      Test Setup

*** Variables ***
${NEW_MAIL} =    somethingelse+qa@geokrety.org

*** Test Cases ***

OLD mail REFUSE change
    Email Change                            ${NEW_MAIL}    ${TRUE}
    Mailbox Open Message ${1}
    Click Link With Text                    Do not change!
    Wait Until Panel                        Do you confirm changing your email address?
    Page Should Contain                     ${USER_1.email}
    Page Should Contain                     ${NEW_MAIL}
    Click Button                            ${USER_EMAIL_VALIDATION_REFUSE_BUTTON}
    Flash message shown                     No change has been processed. This token is now revoked.

Once decision taken, token is disabled - old user
    Refuse change
    Mailbox Open Message ${1}
    Click Link With Text                    Do not change!
    Flash message shown                     Sorry this token is not valid, already used or expired.

Once decision taken, token is disabled - new user
    Refuse change
    Mailbox Open Message ${2}
    Click Link With Text                    Validate your new email address
    Flash message shown                     Sorry this token is not valid, already used or expired.

*** Keyword ***
Refuse change
    Email Change                            ${NEW_MAIL}    ${TRUE}
    Go To Url                               ${PAGE_DEV_MAILBOX_FIRST_MAIL_URL}
    Click Link With Text                    Do not change!
    Click Button                            ${USER_EMAIL_VALIDATION_REFUSE_BUTTON}

*** Keywords ***

Test Setup
    Clear Database And Seed ${1} users
    Sign In ${USER_1.name} Fast
