*** Settings ***
Library         SeleniumLibrary  timeout=10  implicit_wait=0
Resource        ../functions/FunctionsGlobal.robot
Resource        ../functions/PagePasswordChange.robot
Resource        ../vars/users.resource
Force Tags      Users Details    Security
Test Setup      Clear DB And Seed 1 users

*** Test Cases ***

Invalid email submitted
    [Template]    Invalid Email
    ${EMPTY}
    no${SPACE}an${SPACE}@${SPACE}email


Valid form validation
    [Template]    Valid Email
    ${USER_1.email}


Unknown address found
    Request password Recovery                       not.in@our.database
    Page Should Contain                             Sorry no account using that email address.


Reset Password By Mail
    Request password Recovery                       ${USER_1.email}

    Go To Url                                       ${PAGE_DEV_MAILBOX_URL}
    Element Should Contain                          ${DEV_MAILBOX_FIRST_MAIL_LINK}              🔑 Password reset request

    Go To Url                                       ${PAGE_DEV_MAILBOX_FIRST_MAIL_URL}
    Page Should Contain                             Click the link below to reset your password
    Click Link With Text                            Reset Password

    Input Text                                      ${USER_PASSWORD_RECOVERY_NEW_INPUT}         secretpassword
    Input Text                                      ${USER_PASSWORD_RECOVERY_CONFIRM_INPUT}     secretpassword
    Click Button                                    ${USER_PASSWORD_RECOVERY_CHANGE_BUTTON}

    Location Should Be                              ${PAGE_SIGN_IN_URL}
    Page Should Contain                             Your password has been changed.

    Go To Url                                       ${PAGE_DEV_MAILBOX_URL}
    Element Should Contain                          ${DEV_MAILBOX_SECOND_MAIL_LINK}             🔑 Your password has been changed

    Go To Url                                       ${PAGE_DEV_MAILBOX_SECOND_MAIL_URL}
    Page Should Contain                             Your password has been successfully changed
    Click Link With Text                            Login
    Location Should Be                              ${PAGE_SIGN_IN_URL}

    Go To Url                                       ${PAGE_DEV_MAILBOX_FIRST_MAIL_URL}
    Click Link With Text                            Reset Password
    Page Should Contain                             Sorry this token is not valid, already used or expired.


*** Keywords ***

Request password Recovery
    [Arguments]             ${email}
    Sign Out Fast
    Go To Url                                       ${PAGE_PASSWORD_RECOVERY_URL}
    Input Text                                      ${USER_PASSWORD_RECOVERY_EMAIL_INPUT}     ${email}
    Click Button                                    ${USER_PASSWORD_RECOVERY_END_LINK_BUTTON}

Valid Email
    [Arguments]             ${email}
    Request password Recovery                       ${email}
    Page Should Contain                             An email containing a validation link has been sent to the provided email address.

Invalid Email
    [Arguments]             ${email}
    Request password Recovery                       ${email}
    Input validation has error                      ${USER_PASSWORD_RECOVERY_EMAIL_INPUT}
