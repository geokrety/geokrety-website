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
    Refuse change                           ${NEW_MAIL}    ${USER_1.email}

Once decision taken, token is disabled - old user
    Refuse change                           ${NEW_MAIL}    ${USER_1.email}

    Mailbox Open Message ${1}
    Click Link With Text                    Do not change!
    Flash message shown                     Sorry this token is not valid, already used or expired.

Once decision taken, token is disabled - new user
    Refuse change                           ${NEW_MAIL}    ${USER_1.email}

    Mailbox Open Message ${2}
    Click Link With Text                    Validate your new email address
    Flash message shown                     Sorry this token is not valid, already used or expired.

*** Keyword ***

Test Setup
    Clear Database And Seed ${1} users
    Sign In ${USER_1.name} Fast
