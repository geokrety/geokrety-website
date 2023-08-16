*** Settings ***
Library         RequestsLibrary
Resource        ../../ressources/Authentication.robot
Resource        ../../ressources/Users.robot
Variables       ../../ressources/vars/users.yml
Test Setup      Test Setup

*** Test Cases ***

Enable daily mails
    Register User    ${USER_1}
    Activate user account
    Email Change                            ${FALSE}    ${TRUE}
    # Create User                             ${USER_1.name}    daily_mail=${FALSE}
    # Change daily mail preferences           ${TRUE}
    Flash message shown                     Your email preferences were saved.

Disable daily mails
    Register User    ${USER_5}
    Activate user account
    Email Change                            ${FALSE}    ${FALSE}
    # Create User                             ${USER_1.name}    daily_mail=${TRUE}
    # Change daily mail preferences           ${FALSE}
    Flash message shown                     Your email preferences were saved.

*** Keyword ***

Test Setup
    Clear Database
    Sign Out Fast
