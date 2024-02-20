*** Settings ***
Library         RequestsLibrary
Resource        ../../ressources/Authentication.robot
Resource        ../../ressources/Users.robot
Variables       ../../ressources/vars/users.yml
Test Setup      Test Setup

*** Variables ***

${NEW_MAIL} =    somethingelse+qa@geokrety.org

*** Test Cases ***

NEW mail ACCEPT change
    Email Change                            ${NEW_MAIL}    ${TRUE}
    Mailbox Open Message ${2}
    Click Link With Text                    Validate your new email address
    Wait Until Panel                        Do you confirm changing your email address?
    Page Should Contain                     ${USER_1.email}
    Page Should Contain                     ${NEW_MAIL}
    Click Button                            ${USER_EMAIL_VALIDATION_ACCEPT_BUTTON}
    Flash message shown                     Your email address has been validated.
    Element Should Contain                  ${USER_PROFILE_EMAIL}    ${NEW_MAIL}

Once decision taken, token is disabled - old user
    Accept change                           ${NEW_MAIL}    ${USER_1.email}

    Mailbox Open Message ${1}
    Click Link With Text                    Do not change!
    Flash message shown                     Sorry this token is not valid, already used or expired.

Once decision taken, token is disabled - new user
    Accept change                           ${NEW_MAIL}    ${USER_1.email}

    Mailbox Open Message ${2}
    Click Link With Text                    Validate your new email address
    Flash message shown                     Sorry this token is not valid, already used or expired.

*** Keywords ***

Test Setup
    Clear Database And Seed ${1} users
    Sign In ${USER_1.name} Fast
