*** Settings ***
Library         RequestsLibrary
Resource        ../../ressources/Authentication.robot
Resource        ../../ressources/Users.robot
Variables       ../../ressources/vars/users.yml
Test Setup      Test Setup

*** Test Cases ***

Invalid email submitted
    [Template]    Invalid Email
    ${EMPTY}
    no${SPACE}an${SPACE}@${SPACE}email


Valid form validation
    [Template]    Valid Email
    ${USER_1.email}


Unknown address found
    # TODO hum that's not cool... We should not allow to discover email addresses
    Request Password Recovery                       not.in@our.database
    Page Should Contain                             Sorry no account using that email address.


Reset Password By Mail
    Request password Recovery                       ${USER_1.email}

    Mailbox Should Contain ${1} Messages
    Mailbox Message ${1} Subject Should Contain 🔑 Password reset request
    Mailbox Open Message ${1}
    Page Should Contain                             Click the link below to reset your password
    Click Link With Text                            Reset Password

    Location Should Match Regexp                    ${PAGE_PASSWORD_RECOVERY_ACTIVATE_URL}
    Input Text                                      ${USER_PASSWORD_RECOVERY_NEW_INPUT}         secretpassword
    Input Text                                      ${USER_PASSWORD_RECOVERY_CONFIRM_INPUT}     secretpassword
    Click Button                                    ${USER_PASSWORD_RECOVERY_CHANGE_BUTTON}

    Location Should Be                              ${PAGE_SIGN_IN_URL}
    Flash message shown                             Your password has been changed.

    Mailbox Should Contain ${2} Messages
    Mailbox Message ${2} Subject Should Contain 🔑 Your password has been changed
    Mailbox Open Message ${2}
    Page Should Contain                             Your password has been successfully changed
    Click Link With Text                            Login

    Location Should Be                              ${PAGE_SIGN_IN_URL}
    Sign In Fill Form                               ${USER_1.name}    secretpassword
    Click Button                                    ${SIGN_IN_FORM_SIGN_IN_BUTTON}
    User Is Connected

    Sign Out Fast
    Go To Url                                       ${PAGE_DEV_MAILBOX_FIRST_MAIL_URL}
    Click Link With Text                            Reset Password
    Page Should Contain                             Sorry this token is not valid, already used or expired.


*** Keywords ***

Test Setup
    Clear Database And Seed ${1} users
    Sign Out Fast

Request Password Recovery
    [Arguments]             ${email}
    Go To Url                                       ${PAGE_PASSWORD_RECOVERY_URL}
    Input Text                                      ${USER_PASSWORD_RECOVERY_EMAIL_INPUT}     ${email}
    Click Button                                    ${USER_PASSWORD_RECOVERY_END_LINK_BUTTON}

Valid Email
    [Arguments]             ${email}
    Request Password Recovery                       ${email}
    Page Should Contain                             An email containing a validation link has been sent to the provided email address.

Invalid Email
    [Arguments]             ${email}
    Request Password Recovery                       ${email}
    Input validation has error                      ${USER_PASSWORD_RECOVERY_EMAIL_INPUT}
