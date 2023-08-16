*** Settings ***
Resource          CustomActions.robot
Resource          ComponentsLocator.robot
Resource          Geokrety.robot
Variables         vars/users.yml
Library           SeleniumLibrary  timeout=10  implicit_wait=0
Library           RobotEyes

*** Variables ***

${PAGE_USER_RECENT_MOVES_URL}           ${GK_URL}/en/users/\${params.userid}/recent-moves
${PAGE_USER_INVENTORY_URL}              ${GK_URL}/en/users/\${params.userid}/inventory
${PAGE_USER_WATCHED_GEOKRETY_URL}       ${GK_URL}/en/users/\${params.userid}/watched-geokrety
${PAGE_USER_OWNED_GEOKRETY_URL}         ${GK_URL}/en/users/\${params.userid}/owned-geokrety
${PAGE_USER_OWNED_GEOKRETY_RECENT_MOVES_URL}    ${GK_URL}/en/users/\${params.userid}/owned/recent-moves
${PAGE_USER_POSTED_PICTURES_URL}                ${GK_URL}/en/users/\${params.userid}/pictures
${PAGE_USER_OWNED_GEOKRETY_PICTURES_URL}        ${GK_URL}/en/users/\${params.userid}/owned/pictures

*** Keywords ***

Change User Password Via Modal
    [Arguments]    ${old}=password    ${new}=newpass    ${confirm}=${new}
    Location Should Contain                 ${PAGE_USER_PROFILE_BASE_URL}
    Click Link                              ${USER_PROFILE_PASSWORD_EDIT_BUTTON}
    Wait Until Modal                        Change your password
    Change User Password                    ${old}    ${new}    ${confirm}

Change User Password
    [Arguments]    ${old}=password    ${new}=newpass    ${confirm}=${new}
    Fill Password Change Form               ${old}    ${new}    ${confirm}
    Click Button                            ${SUBMIT_BUTTON}

Fill Password Change Form
    [Arguments]    ${old}=password    ${new}=newpass    ${confirm}=${new}
    Input Text    ${USER_PASSWORD_OLD_INPUT}          ${old}
    Input Text    ${USER_PASSWORD_NEW_INPUT}          ${new}
    Input Text    ${USER_PASSWORD_CONFIRM_INPUT}      ${confirm}


Email Change Via Modal
    [Arguments]    ${email}    ${daily_mail}
    Location Should Contain                 ${PAGE_USER_PROFILE_BASE_URL}
    Click Button                            ${USER_PROFILE_EMAIL_EDIT_BUTTON}
    Wait Until Modal                        Update your email address
    Fill Email Change Form                  ${email}    ${daily_mail}
    Click Button                            ${MODAL_DIALOG_SUBMIT_BUTTON}

Email Change
    [Arguments]    ${email}    ${daily_mail}
    Go To Url                               ${PAGE_USER_CHANGE_EMAIL_URL}
    Wait Until Panel                        Update your email address
    Fill Email Change Form                  ${email}    ${daily_mail}
    Click Button                            ${MODAL_PANEL_SUBMIT_BUTTON}

Accept change
    [Arguments]    ${new_mail}    ${old_email}
    Email Change                            ${new_mail}    ${TRUE}
    Element Should Contain                  ${USER_PROFILE_EMAIL}    ${old_email}
    Mailbox Should Contain ${2} Messages
    Mailbox Message ${1} Subject Should Contain 📯 Changing your email address
    Mailbox Message ${2} Subject Should Contain ✉️ Changing your email address
    Mailbox Open Message ${2}
    Click Link With Text                    Validate your new email address
    Click Button                            ${USER_EMAIL_VALIDATION_ACCEPT_BUTTON}
    Flash message shown                     Your email address has been validated.
    Element Should Contain                  ${USER_PROFILE_EMAIL}    ${new_mail}

Refuse change
    [Arguments]    ${new_mail}    ${old_email}
    Email Change                            ${new_mail}    ${TRUE}
    Element Should Contain                  ${USER_PROFILE_EMAIL}    ${old_email}
    Mailbox Should Contain ${2} Messages
    Mailbox Message ${1} Subject Should Contain 📯 Changing your email address
    Mailbox Message ${2} Subject Should Contain ✉️ Changing your email address
    Mailbox Open Message ${2}
    Click Link With Text                    Validate your new email address
    Click Button                            ${USER_EMAIL_VALIDATION_REFUSE_BUTTON}
    Flash message shown                     No change has been processed. This token is now revoked.
    Element Should Contain                  ${USER_PROFILE_EMAIL}    ${old_email}

Daily Mail Change Preferences
    [Arguments]    ${daily_mail}
    Email Change                            ${EMPTY}    ${daily_mail}

Fill Email Change Form
    [Arguments]    ${email}    ${daily_mail}
    Run Keyword If    "${email}" != "${FALSE}"          Input Text           ${USER_EMAIL_EMAIL_INPUT}    ${email}
    Run Keyword If    ${daily_mail} == ${TRUE}      Select Checkbox      ${USER_EMAIL_DAILY_MAIL_CHECKBOX}
    Run Keyword If    ${daily_mail} == ${FALSE}     Unselect Checkbox    ${USER_EMAIL_DAILY_MAIL_CHECKBOX}
