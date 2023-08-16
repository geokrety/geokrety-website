*** Settings ***
Library         RequestsLibrary
Resource        ../../ressources/Authentication.robot
Resource        ../../ressources/Users.robot
Variables       ../../ressources/vars/users.yml
Test Setup      Test Setup

*** Variables ***

${NEW_MAIL} =    somethingelse+qa@geokrety.org

*** Test Cases ***

Email address already PENDING by user himself
    Sign In ${USER_1.name} Fast
    Email Change                            ${NEW_MAIL}    ${TRUE}
    Flash message shown                     A confirmation email was sent to your new address.

    Email Change                            ${NEW_MAIL}    ${TRUE}
    Flash message shown                     The confirmation email was sent again to your new address.
    Mailbox Should Contain 4 Messages

Email address already USED by another user
    Sign In ${USER_1.name} Fast
    Email Change                            ${USER_2.email}    ${TRUE}
    Flash message shown                     Sorry but this mail address is already in use.

Email address already PENDING by another user
    Sign In ${USER_2.name} Fast
    Email Change                            ${USER_3.email}    ${TRUE}
    Flash message shown                     A confirmation email was sent to your new address.

    Sign In ${USER_1.name} Fast
    Email Change                            ${USER_3.email}    ${TRUE}
    Flash message shown                     Sorry but this mail address is already in use.

*** Keyword ***

Test Setup
    Clear Database And Seed ${2} users
