*** Settings ***
Library         SeleniumLibrary  timeout=10  implicit_wait=0
Resource        ../functions/FunctionsGlobal.robot
Resource        ../vars/users.resource
Force Tags      Sign In
Test Setup     Clean

# const USER_ACCOUNT_INVALID = 0;
# const USER_ACCOUNT_VALID = 1;
# const USER_ACCOUNT_IMPORTED = 2;

*** Test Cases ***

Invalid accounts cannot login
    Seed 1 users with status 0
    Go To Url                           ${PAGE_HOME_URL}
    Sign In User                        ${USER_1.name}
    Page Should Not Contain             Welcome on board
    Page WithoutWarningOrFailure
    Mailbox Should Contain 1 Messages

    Go To Url                           ${PAGE_DEV_MAILBOX_URL}
    Element Should Contain              ${DEV_MAILBOX_FIRST_MAIL_LINK}    Welcome to GeoKrety.org

Valid accounts can login
    Seed 1 users with status 1
    Go To Url                           ${PAGE_HOME_URL}
    Sign In User                        ${USER_1.name}
    Page Should Contain                 Welcome on board
    Mailbox Should Contain 0 Messages

Imported accounts can login
    Seed 1 users with status 2
    Go To Url                           ${PAGE_HOME_URL}
    Sign In User                        ${USER_1.name}
    Page Should Contain                 Welcome on board

    # Mailbox Should Contain 0 Messages
    Mailbox Should Contain 1 Messages

    Go To Url                           ${PAGE_DEV_MAILBOX_URL}
    Element Should Contain              ${DEV_MAILBOX_FIRST_MAIL_LINK}    Account revalidation

*** Keywords ***

Clean
    Clear Database
    Sign Out Fast
