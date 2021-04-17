*** Settings ***
Library         SeleniumLibrary  timeout=10  implicit_wait=0
Library         DependencyLibrary
Resource        ../functions/PageRegistration.robot
Suite Setup     Clear Database
Force Tags      CreateAccount

*** Test Cases ***
Create first account
    [Documentation]                     Create an account
    !Go To GeoKrety
    Page Should Contain Link            ${NAVBAR_REGISTER_LINK}
    Click Element                       ${NAVBAR_REGISTER_LINK}
    Page ShouldShow Registration Form
    Fill Registration Form              admin
    Click Button                        ${REGISTRATION_REGISTER_BUTTON}
    Location Should Be                  ${PAGE_USER_1_PROFILE_URL}
    Page Should Not Contain             No such item!
    Page Should Contain                 A confirmation email has been sent to your address
    Element Text Should Be              ${NAVBAR_DEV_MAILBOX_COUNTER}    1

User is not connected after registration
    Depends on test                     Create first account
    [Documentation]                     User is not connected after registration
    !Go To GeoKrety
    Page Should Contain Link            ${NAVBAR_REGISTER_LINK}

User cannot connect as account is not yet active
    Depends on test                     User is not connected after registration
    [Documentation]                     User cannot connect as account is not yet active
    Go To Url                           ${PAGE_HOME_URL}
    Sign In User                        admin
    Page Should Not Contain             Welcome on board
    Location Should Be                  ${PAGE_HOME_URL}
    Page Should Contain Link            ${NAVBAR_REGISTER_LINK}
    Delete Second Mail in Mailbox

Activate account
    Depends on test                     Create first account
    [Documentation]                     Activate account (mail link)
    Go To Url                           ${PAGE_DEV_MAILBOX_URL}
    ${rowCount}=                        Get Element Count     ${DEV_MAILBOX_MAILS_TABLE_ROWS}
    Should Be Equal As Integers         1   ${rowCount}
    Activate user account

User is connected after activation
    Depends on test                     Activate account
    [Documentation]                     User is authenticated after registration
    !Go To GeoKrety
    Page Should Not Contain Link        ${NAVBAR_REGISTER_LINK}

Email activation confirmation received
    Depends on test                     Activate account
    [Documentation]                     Validated account should be confirmed via email
    Go To Url                           ${PAGE_DEV_MAILBOX_URL}
    ${rowCount}=                        Get Element Count     ${DEV_MAILBOX_MAILS_TABLE_ROWS}
    Should Be Equal As Integers         1   ${rowCount}

    Click Link                          ${DEV_MAILBOX_SECOND_MAIL_LINK}
    Location Should Be                  ${PAGE_DEV_MAILBOX_SECOND_MAIL_URL}
    Page Should Contain                 Your account on GeoKrety.org is now fully functional.
    Click Link With Text                Login
    Location Should Be                  ${PAGE_SIGN_IN_URL}
    Delete Second Mail in Mailbox

Sign In user
    Depends on test                     Email activation confirmation received
    [Documentation]                     User can sign in
    Sign Out Fast
    Go To Url                           ${PAGE_HOME_URL}
    Sign In User                        admin
    Page Should Not Contain             Username and password doesn't match.
    Location Should Be                  ${PAGE_HOME_URL}
    Page Should Contain                 Welcome on board

Sign Out user
    Depends on test                     Sign In user
    [Documentation]                     User can sign out
    Go To Url                           ${PAGE_NEWS_LIST_URL}
    Sign Out User
    Location Should Be                  ${PAGE_HOME_URL}
    Page Should Not Contain Element     ${NAVBAR_PROFILE_LINK}
    Page Should Contain Element         ${NAVBAR_SIGN_IN_LINK}
