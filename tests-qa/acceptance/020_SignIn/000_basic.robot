*** Settings ***
Library         RequestsLibrary
Resource        ../ressources/Authentication.robot
Variables       ../ressources/vars/users.yml
Test Setup      Test Setup

*** Variables ***

${USER_ACCOUNT_INVALID}     0;
${USER_ACCOUNT_VALID}       1;
${USER_ACCOUNT_IMPORTED}    2;

*** Test Cases ***

User can sign in
    Sign In User                        ${USER_1.name}    ${USER_1.password}
    Page Should Not Contain             Username and password doesn't match.
    Location Should Be                  ${PAGE_USER_1_PROFILE_URL}
    Page Should Contain                 Welcome on board
    User Is Connected

User can sign out
    Sign In ${USER_1.name} Fast
    Go To Url                           ${PAGE_HOME_URL_EN}
    Sign Out
    Location Should Be                  ${PAGE_HOME_URL_EN}
    User Is Not Connected

Sign out fast
    Seed ${1} users without terms of use with status ${USER_ACCOUNT_IMPORTED}

    Sign In ${USER_1.name} Fast
    Go To Url                           about:blank
    Sign Out Fast

*** Keywords ***

Test Setup
    Clear Database And Seed ${1} users
    Sign Out Fast
