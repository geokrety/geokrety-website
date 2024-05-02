*** Settings ***
Library         RequestsLibrary
Resource        ../ressources/Authentication.robot
Variables       ../ressources/vars/users.yml
Test Setup      Test Setup
Test Tags       EmailTokenBase

*** Test Cases ***

Happy path
    Register User                       ${USER_1}
    Activate user account
    User Is Connected
    Mailbox Should Contain 2 Messages
    Sign Out Fast
    Mailbox Open Message ${2}
    Page Should Contain                 Your account on GeoKrety.org is now fully functional.
    Click Link With Text                Login
    Location Should Be                  ${PAGE_SIGN_IN_URL}

User cannot connect as account is not yet active
    Register User                       ${USER_1}
    Empty Dev Mailbox Fast
    Sign In But Expect Error

User cannot connect as account is not yet active Fast Path
    Seed ${1} users with status ${USER_ACCOUNT_STATUS_INVALID}
    Sign In But Expect Error

User can receive a new mail by clicking the link on login
    Seed ${1} users with status ${USER_ACCOUNT_STATUS_INVALID}

    Sign In User                        ${USER_1.name}
    User Is Not Connected
    Click Link With Text                new confirmation mail
    Flash message shown                 Mail sent
    Mailbox Should Contain ${1} Messages

Multiple login attempts
    Seed ${1} users with status ${USER_ACCOUNT_STATUS_INVALID}
    Mailbox Should Contain ${0} Messages

    Sign In But Expect Error
    Mailbox Should Contain ${0} Messages
    ${href1} =    Get Element Attribute    //a[text() = "new confirmation mail"]    href

    Sign In But Expect Error
    Mailbox Should Contain ${0} Messages
    ${href2} =    Get Element Attribute    //a[text() = "new confirmation mail"]    href

    Should Not Be Equal As Strings    ${href1}    ${href2}

    Go To    ${href1}
    Flash message shown                 Sorry this link is invalid

    Go To    ${href2}
    Flash message shown                 Mail sent

    Go To    ${href2}
    Flash message shown                 Sorry this link is invalid


*** Keywords ***

Test Setup
    Clear Database
    Sign Out Fast
    Empty Dev Mailbox Fast

Page Should Contain Error account not yet active
    Page Should Contain                 Your account is not yet active.
    Page Should Contain                 You can request a new confirmation mail

Sign In But Expect Error
    Sign In User                        ${USER_1.name}    ${USER_1.password}
    User Is Not Connected
    Location Should Be                  ${PAGE_HOME_URL_EN}
    Page Should Contain Error account not yet active
    Mailbox Should Contain ${0} Messages
