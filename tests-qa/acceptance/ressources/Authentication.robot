*** Settings ***
Resource        Devel.robot
Resource        CustomActions.robot
Resource        vars/Urls.robot

*** Variables ***

${PAGE_SIGN_IN_URL}                     ${PAGE_HOME_URL_EN}/login
${PAGE_SIGN_OUT_URL}                    ${PAGE_HOME_URL_EN}/logout

${PAGE_LOGIN_USER}                      ${GK_URL}/devel/users/\${params.username}/login
${PAGE_LOGOUT_USER}                     ${GK_URL}/devel/users/logout

${PAGE_REGISTER_URL}                    ${PAGE_HOME_URL_EN}/registration
${PAGE_REGISTER_ACTIVATION_URL}         ${GK_URL}\/en\/registration\/[^\/]+\/activate

################
# NAVBAR
################
${NAVBAR}                                   //body/nav
${NAVBAR_SIGN_IN_LINK}                      //*[@id="navbar-profile-login"]
${NAVBAR_REGISTER_LINK}                     //*[@id="navbar-profile-register"]
${NAVBAR_PROFILE_LINK}                      //*[@id="navbar-profile-user"]
${NAVBAR_SIGN_OUT_LINK}                     //*[@id="navbar-profile-user-logout"]

################
# USER REGISTRATION FORM
################
${REGISTRATION_REGISTER_BUTTON}             //*[@id="registerButton"]
${REGISTRATION_USERNAME_INPUT}              //*[@id="usernameInput"]
${REGISTRATION_EMAIL_INPUT}                 //*[@id="emailInput"]
${REGISTRATION_PASSWORD_INPUT}              //*[@id="passwordInput"]
${REGISTRATION_PASSWORD_CONFIRM_INPUT}      //*[@id="passwordConfirmInput"]
${REGISTRATION_PREFERRED_LANGUAGE_SELECT}   //*[@id="preferredLanguageInput"]
${REGISTRATION_DAILY_MAIL_CHECKBOX}         //*[@id="dailyMailsInput"]
${REGISTRATION_TERMS_OF_USE_CHECKBOX}       //*[@id="termsOfUseInput"]

################
# SIGN IN FORM
################
${SIGN_IN_FORM_USERNAME_INPUT}              //div[@class="modal-dialog"]//form//input[@name="login"]
${SIGN_IN_FORM_PASSWORD_INPUT}              //div[@class="modal-dialog"]//form//input[@name="password"]
${SIGN_IN_FORM_REMEMBER_ME_CHECKBOX}        //div[@class="modal-dialog"]//form//input[@name="remember"]
${SIGN_IN_FORM_SIGN_IN_BUTTON}              //div[@class="modal-dialog"]//form//button[@type="submit"]

*** Keywords ***

Sign In User
    [Arguments]     ${username}     ${password}=password
    Go To Home
    Sign In User From Here              ${username}     ${password}

Sign In User From Here
    [Arguments]     ${username}     ${password}=password
    Page Should Contain Link            ${NAVBAR_SIGN_IN_LINK}
    Click Element                       ${NAVBAR_SIGN_IN_LINK}
    Wait Until Modal                    Login
    Input Text                          ${SIGN_IN_FORM_USERNAME_INPUT}    ${username}
    Input Text                          ${SIGN_IN_FORM_PASSWORD_INPUT}    ${password}
    Click Button                        ${SIGN_IN_FORM_SIGN_IN_BUTTON}

Sign In ${username} Fast
    [Documentation]     Login user using special url (doesn't use login form)
    Go To Url                           ${PAGE_LOGIN_USER}    redirect=${NO_REDIRECT_CHECK}    username=${username}
    Page Should Not Contain             Error signing in user

Sign Out
    Click Link                          ${NAVBAR_PROFILE_LINK}
    Click Link                          ${NAVBAR_SIGN_OUT_LINK}

Sign Out Fast
    [Documentation]     Logout user using special url (doesn't use menu, no home page load)
    Go To Url                           ${PAGE_LOGOUT_USER}

Register User
    [Arguments]     &{user}
    Go To Url                           ${PAGE_REGISTER_URL}
    Location Should Be                  ${PAGE_REGISTER_URL}

    Page Should Show Registration Form
    Fill Registration Form              ${user.name}
    ...                                 email=${user.email}
    ...                                 password=${user.password}
    ...                                 language=${user.language}
    ...                                 daily_mail=${user.daily_mail}
    ...                                 terms_of_use=${user.terms_of_use}
    Click Button                        ${REGISTRATION_REGISTER_BUTTON}

    Location Should Be                  ${PAGE_USER_1_PROFILE_URL}
    Page Should Not Contain             This user does not exist
    Page Should Contain                 A confirmation email has been sent to your address
    Mailbox Should Contain 1 Messages

Activate user account
    Mailbox Open Message ${1}
    Page Should Contain                 Welcome to GeoKrety.org
    Page Should Contain                 Activate your account
    Click Link With Text                Activate your account
    Location Should Match Regexp        ${PAGE_REGISTER_ACTIVATION_URL}
    Wait Until Page Contains            Welcome to the GeoKrety.org community!

User Is Connected
    Page Should Not Contain Link        ${NAVBAR_REGISTER_LINK}
    Page Should Contain Link            ${NAVBAR_PROFILE_LINK}

User Is Not Connected
    Page Should Contain Link            ${NAVBAR_REGISTER_LINK}
    Page Should Not Contain Link        ${NAVBAR_PROFILE_LINK}


Page Should Show Registration Form
    Wait Until Element Is Visible       ${REGISTRATION_REGISTER_BUTTON}
    Wait Until Page Contains Element    ${REGISTRATION_USERNAME_INPUT}
    Wait Until Page Contains Element    ${REGISTRATION_EMAIL_INPUT}
    Wait Until Page Contains Element    ${REGISTRATION_PASSWORD_INPUT}
    Wait Until Page Contains Element    ${REGISTRATION_PASSWORD_CONFIRM_INPUT}
    Wait Until Page Contains Element    ${REGISTRATION_PREFERRED_LANGUAGE_SELECT}
    Wait Until Page Contains Element    ${REGISTRATION_DAILY_MAIL_CHECKBOX}
    Wait Until Page Contains Element    ${REGISTRATION_TERMS_OF_USE_CHECKBOX}


Fill Registration Form
    # [Arguments]    &{user}
    [Arguments]    ${username}  ${email}=${username}+qa@geokrety.org    ${password}=password
    ...            ${language}=en    ${daily_mail}=${FALSE}   ${terms_of_use}=${TRUE}
    Input Text                      ${REGISTRATION_USERNAME_INPUT}              ${username}
    Input Text                      ${REGISTRATION_EMAIL_INPUT}                 ${email}
    Input Text                      ${REGISTRATION_PASSWORD_INPUT}              ${password}
    Input Text                      ${REGISTRATION_PASSWORD_CONFIRM_INPUT}      ${password}
    Select From List By Value       ${REGISTRATION_PREFERRED_LANGUAGE_SELECT}   ${language}
    Run Keyword If  ${daily_mail}     Select Checkbox    ${REGISTRATION_DAILY_MAIL_CHECKBOX}
    Run Keyword If  ${terms_of_use}   Select Checkbox    ${REGISTRATION_TERMS_OF_USE_CHECKBOX}
