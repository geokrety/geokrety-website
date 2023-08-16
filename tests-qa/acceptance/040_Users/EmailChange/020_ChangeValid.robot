*** Settings ***
Library         RequestsLibrary
Resource        ../../ressources/Authentication.robot
Resource        ../../ressources/Users.robot
Variables       ../../ressources/vars/users.yml
Test Setup      Test Setup

*** Variables ***

${NEW_MAIL} =    somethingelse+qa@geokrety.org

*** Test Cases ***

Valid email change - modal
    Go To User ${USER_1.id}
    Email Change Via Modal                  ${NEW_MAIL}    ${TRUE}
    Flash message shown                     A confirmation email was sent to your new address.
    Page Should Contain                     You have a pending email validation.

Valid email change - page form
    Email Change                            ${NEW_MAIL}    ${TRUE}
    Flash message shown                     A confirmation email was sent to your new address.
    Page Should Contain                     You have a pending email validation.

Confirmation mail should be sent
    Email Change                            ${NEW_MAIL}    ${TRUE}
    Mailbox Should Contain 2 Messages
    Mailbox Message ${1} Subject Should Contain 📯 Changing your email address
    Mailbox Message ${2} Subject Should Contain ✉️ Changing your email address

    Mailbox Open Message ${1}
    Page Should Contain                     Someone, hopefully you, has requested a change on your GeoKrety contact email address to: ${NEW_MAIL}.
    Page Should Contain                     Do not change!

    Mailbox Open Message ${2}
    Page Should Contain                     Someone, hopefully you, has requested to change it's GeoKrety contact email address to yours.
    Page Should Contain                     Validate your new email address

*** Keywords ***

Test Setup
    Clear Database And Seed ${1} users
    Sign In ${USER_1.name} Fast
