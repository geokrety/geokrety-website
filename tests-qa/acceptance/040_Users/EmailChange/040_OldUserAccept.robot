*** Settings ***
Library         RequestsLibrary
Resource        ../../ressources/Authentication.robot
Resource        ../../ressources/Users.robot
Variables       ../../ressources/vars/users.yml
Test Setup      Test Setup

*** Variables ***

${NEW_MAIL} =    somethingelse+qa@geokrety.org

*** Test Cases ***

OLD mail ACCEPT change
    Email Change                            ${NEW_MAIL}    ${TRUE}
    Mailbox Open Message ${1}
    Click Link With Text                    Do not change!
    Wait Until Panel                        Do you confirm changing your email address?
    Page Should Contain                     ${USER_1.email}
    Page Should Contain                     ${NEW_MAIL}
    Click Button                            ${USER_EMAIL_VALIDATION_ACCEPT_BUTTON}
    Flash message shown                     Your email address has been validated.
    Element Should Contain                  ${USER_PROFILE_EMAIL}    ${NEW_MAIL}

On accept confirmation mails should be sent
    Accept change                           ${NEW_MAIL}    ${USER_1.email}
    Mailbox Should Contain ${4} Messages
    Mailbox Message ${3} Subject Should Contain 📯 Email address changed
    Mailbox Message ${4} Subject Should Contain ✉️ Email address changed

    Mailbox Open Message ${3}
    Page Should Contain                     Congratulation
    Page Should Contain                     Your email address has been successfully changed to: ${NEW_MAIL}.
    Page Should Contain                     Revert this change!
    Page Should Contain                     Change my password!

    Mailbox Open Message ${4}
    Page Should Contain                     Congratulation
    Page Should Contain                     Your email address has been successfully changed.
    Page Should Contain                     Login

Token is then disabled for old email
    Accept change                           ${NEW_MAIL}    ${USER_1.email}
    Mailbox Open Message ${1}
    Click Link With Text                    Do not change!
    Flash message shown                     Sorry this token is not valid, already used or expired.

Token is then disabled for new email
    Accept change                           ${NEW_MAIL}    ${USER_1.email}
    Mailbox Open Message ${2}
    Click Link With Text                    Validate your new email address
    Flash message shown                     Sorry this token is not valid, already used or expired.

*** Keywords ***

Test Setup
    Clear Database And Seed ${1} users
    Sign In ${USER_1.name} Fast
