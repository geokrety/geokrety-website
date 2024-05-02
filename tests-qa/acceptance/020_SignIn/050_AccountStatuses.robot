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

Invalid accounts cannot login
    Seed 1 users with status ${USER_ACCOUNT_INVALID}

    Sign In User                        ${USER_1.name}
    User Is Not Connected
    Page Should Contain Error account not yet active

    # No other mail sent
    Mailbox Should Contain ${0} Messages

Valid accounts can login
    Seed 1 users with status ${USER_ACCOUNT_VALID}
    Mailbox Should Contain ${0} Messages

    Sign In User                        ${USER_1.name}
    User Is Connected

    # No other mail sent
    Mailbox Should Contain ${0} Messages

Imported accounts can login
    Seed ${1} users with status ${USER_ACCOUNT_IMPORTED}

    Sign In User                        ${USER_1.name}
    User Is Connected

    # No other mail sent
    Mailbox Should Contain ${0} Messages


Imported accounts mail notification
    [Tags]    sendIntervalValid
    Seed ${1} users with status ${USER_ACCOUNT_IMPORTED}
    Seed ${1} users with status ${USER_ACCOUNT_VALID}    start_at=2

    Sign In ${USER_1.name} Fast

    Go To Home
    Page Should Not Contain             A confirmation email has been sent to your address
    Page Should Not Contain             You can request a new confirmation mail
    Mailbox Should Contain ${0} Messages

    # Link is only shown on user's details page
    Go To User ${USER_1.id}
    Page Should Not Contain             A confirmation email has been sent to your address
    Page Should Contain                 You can request a new confirmation mail
    Mailbox Should Contain ${0} Messages

    # click the link send the mail
    Click Link With Text                new confirmation mail
    Location Should Be                  ${PAGE_USER_1_PROFILE_URL}
    Page Should Contain                 Mail sent
    Mailbox Should Contain ${1} Messages

    # Execute Manual Step    foo

    # Mail can't be sent too quickly
    Click Link With Text                new confirmation mail
    Page Should Contain                 You can request a new confirmation mail
    Mailbox Should Contain ${1} Messages

    # Link is not shown from someone else detail page
    Go To User ${USER_2.id}
    Page Should Not Contain             A confirmation email has been sent to your address
    Page Should Contain                 You can request a new confirmation mail

    # Validated users don't see the message
    Sign In ${USER_2.name} Fast
    Location Should Be                  ${PAGE_USER_2_PROFILE_URL}
    Page Should Not Contain             A confirmation email has been sent to your address
    Page Should Not Contain             You can request a new confirmation mail


Imported accounts validation
    Seed ${1} users with status ${USER_ACCOUNT_IMPORTED}
    Sign In ${USER_1.name} Fast
    Click Link With Text                new confirmation mail
    Mailbox Should Contain ${1} Messages

    Mailbox Message ${1} Subject Should Contain Account revalidation
    Mailbox Open Message ${1}
    Page Should Contain                 Your account has been imported from GKv1
    Click Link With Text                Validate your email address
    Flash message shown                 You have successfully validated your email address.
    Go To User ${USER_1.id}
    Page Should Not Contain             You can request a new confirmation mail

*** Keywords ***

Test Setup
    Clear Database
    Sign Out Fast

Page Should Contain Error account not yet active
    Page Should Contain                 Your account is not yet active.
    Page Should Contain                 You can request a new confirmation mail
