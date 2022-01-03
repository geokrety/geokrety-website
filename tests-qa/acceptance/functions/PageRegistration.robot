*** Settings ***
Resource        FunctionsGlobal.robot

*** Keywords ***

Create User
    [Arguments]     ${username}  ${email}=${username}+qa@geokrety.org    ${password}=password
    ...            ${language}=en    ${daily_mail}=${FALSE}   ${terms_of_use}=${TRUE}
    Go To Url                         ${PAGE_REGISTER_URL}
    Fill Registration Form            ${username}  ${email}    ${password}
    ...                               ${language}    ${daily_mail}   ${terms_of_use}
    Click Button                      ${REGISTRATION_REGISTER_BUTTON}
    Location Should Contain           ${GK_URL}/${language}/users/
    Page Should Not Contain           This user does not exists


Activate user account
    Go To Url                           ${PAGE_DEV_MAILBOX_URL}
    Click Link                          ${DEV_MAILBOX_FIRST_MAIL_LINK}
    Location Should Be                  ${PAGE_DEV_MAILBOX_FIRST_MAIL_URL}
    Page Should Contain                 Welcome to GeoKrety.org
    Page Should Contain                 Activate your account

    Click Link With Text                Activate your account
    Wait Until Page Contains            Welcome to the GeoKrety.org community!
    Delete First Mail in Mailbox


Page ShouldShow Registration Form
    Wait Until Element Is Visible       ${REGISTRATION_REGISTER_BUTTON}
    Wait Until Page Contains Element    ${REGISTRATION_USERNAME_INPUT}
    Wait Until Page Contains Element    ${REGISTRATION_EMAIL_INPUT}
    Wait Until Page Contains Element    ${REGISTRATION_PASSWORD_INPUT}
    Wait Until Page Contains Element    ${REGISTRATION_PASSWORD_CONFIRM_INPUT}
    Wait Until Page Contains Element    ${REGISTRATION_PREFERRED_LANGUAGE_SELECT}
    Wait Until Page Contains Element    ${REGISTRATION_DAILY_MAIL_CHECKBOX}
    Wait Until Page Contains Element    ${REGISTRATION_TERMS_OF_USE_CHECKBOX}


Fill Registration Form
    [Arguments]    ${username}  ${email}=${username}+qa@geokrety.org    ${password}=password
    ...            ${language}=en    ${daily_mail}=${FALSE}   ${terms_of_use}=${TRUE}
    Input Text                      ${REGISTRATION_USERNAME_INPUT}              ${username}
    Input Text                      ${REGISTRATION_EMAIL_INPUT}                 ${email}
    Input Text                      ${REGISTRATION_PASSWORD_INPUT}              ${password}
    Input Text                      ${REGISTRATION_PASSWORD_CONFIRM_INPUT}      ${password}
    Select From List By Value       ${REGISTRATION_PREFERRED_LANGUAGE_SELECT}   ${language}
    Run Keyword If  ${daily_mail}     Select Checkbox    ${REGISTRATION_DAILY_MAIL_CHECKBOX}
    Run Keyword If  ${terms_of_use}   Select Checkbox    ${REGISTRATION_TERMS_OF_USE_CHECKBOX}
