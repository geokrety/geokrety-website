*** Settings ***
Library         RequestsLibrary
Resource        ../ressources/Authentication.robot
Variables       ../ressources/vars/users.yml
Test Setup      Test Setup

*** Variables ***

${USER_ACCOUNT_INVALID}     0;
${USER_ACCOUNT_VALID}       1;
${USER_ACCOUNT_IMPORTED}    2;

${MSG_ACCOUNT_NOT_YET_ACTIVE}    Your account is not yet active

*** Test Cases ***

Invalid accounts cannot login
    Seed 1 users with status ${USER_ACCOUNT_INVALID}

    Sign In User                        ${USER_1.name}
    User Is Not Connected
    Page Should Contain                 ${MSG_ACCOUNT_NOT_YET_ACTIVE}

    # Another activation mail is sent
    Mailbox Should Contain 1 Messages
    Mailbox Message ${1} Subject Should Contain Welcome to GeoKrety.org

Valid accounts can login
    Seed 1 users with status ${USER_ACCOUNT_VALID}
    Mailbox Should Contain 0 Messages

    Sign In User                        ${USER_1.name}
    User Is Connected

    # No other mail sent
    Mailbox Should Contain 0 Messages

Imported accounts can login
    Seed 1 users without terms of use with status ${USER_ACCOUNT_IMPORTED}
    Sign In User                        ${USER_1.name}
    User Is Connected
    Flash message shown                 A confirmation email has been sent to your address

    Mailbox Should Contain 1 Messages
    Mailbox Message ${1} Subject Should Contain Account revalidation

    Mailbox Open Message ${1}
    Page Should Contain                 Your account has been imported from GKv1
    Click Link With Text                Validate your email address
    Location Should Be                  ${PAGE_TERMS_OF_USE_URL}
    Flash message shown                 You have successfully validated your email address.

*** Keywords ***

Test Setup
    Clear Database
    Sign Out Fast
