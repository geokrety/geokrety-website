*** Settings ***
Library         RequestsLibrary
Resource        ../ressources/Authentication.robot
Variables       ../ressources/vars/users.yml
Test Setup      Test Setup

*** Test Cases ***

User cannot connect as account is not yet active
    Register User                       ${USER_1}
    Sign In User                        ${USER_1.name}    ${USER_1.password}
    Page Should Contain                 Your account is not yet active.
    User Is Not Connected
    Page Should Not Contain             Welcome on board
    Location Should Be                  ${PAGE_HOME_URL_EN}

Activate account via email
    Register User                       ${USER_1}
    Activate user account
    User Is Connected
    Mailbox Should Contain 2 Messages

After activation a confirmation mail is sent
    Register User                       ${USER_1}
    Activate user account
    Sign Out Fast
    Mailbox Open Message ${2}
    Page Should Contain                 Your account on GeoKrety.org is now fully functional.
    Click Link With Text                Login
    Location Should Be                  ${PAGE_SIGN_IN_URL}

Confirmation mail sent again
    Register User                       ${USER_1}
    Mailbox Should Contain 1 Messages
    Sign In User                        ${USER_1.name}
    Mailbox Should Contain 2 Messages

User can sign in
    Seed ${1} users
    Sign In User                        ${USER_1.name}    ${USER_1.password}
    Page Should Not Contain             Username and password doesn't match.
    Location Should Be                  ${PAGE_HOME_URL_EN}
    Page Should Contain                 Welcome on board

User can sign out
    Seed ${1} users
    Sign In ${USER_1.name} Fast
    Go To Url                           ${PAGE_HOME_URL_EN}
    Sign Out
    Location Should Be                  ${PAGE_HOME_URL_EN}
    User Is Not Connected

*** Keywords ***

Test Setup
    Clear Database
    Sign Out Fast
    Empty Dev Mailbox Fast
