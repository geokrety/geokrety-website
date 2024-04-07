*** Settings ***
Library         String
Resource        ../ressources/Authentication.robot
Resource        ../ressources/Moves.robot
Variables       ../ressources/vars/users.yml
Variables       ../ressources/vars/geokrety.yml
Variables       ../ressources/vars/waypoints.yml
Suite Setup     Suite Setup

*** Test Cases ***

Form Initial Status As Anonymous
    Sign Out Fast
    Go To Move
    Open Panel                              ${MOVE_ADDITIONAL_DATA_PANEL}
    Element Should Be Visible               ${MOVE_ADDITIONAL_DATA_DATE_TIME_INPUT}
    Element Should Be Visible               ${MOVE_ADDITIONAL_DATA_USERNAME_INPUT}
    Element Should Not Be Visible           ${MOVE_ADDITIONAL_DATA_COMMENT_INPUT}
    Element Should Be Visible               ${MOVE_ADDITIONAL_DATA_COMMENT_CODEMIRROR}
    Element Should Be Visible               ${MOVE_ADDITIONAL_DATA_SUBMIT_BUTTON}
    Element Should Not Be Visible           ${MOVE_ADDITIONAL_DATA_DATE_HIDDEN_INPUT}
    Element Should Not Be Visible           ${MOVE_ADDITIONAL_DATA_HOUR_HIDDEN_INPUT}
    Element Should Not Be Visible           ${MOVE_ADDITIONAL_DATA_MINUTE_HIDDEN_INPUT}
    Element Should Not Be Visible           ${MOVE_ADDITIONAL_DATA_TIMEZONE_HIDDEN_INPUT}

Form Initial Status As Authenticated
    Sign In ${USER_1.name} Fast
    Go To Move
    Open Panel                              ${MOVE_ADDITIONAL_DATA_PANEL}
    Element Should Be Visible               ${MOVE_ADDITIONAL_DATA_DATE_TIME_INPUT}
    Element Should Not Be Visible           ${MOVE_ADDITIONAL_DATA_USERNAME_INPUT}
    Element Should Not Be Visible           ${MOVE_ADDITIONAL_DATA_COMMENT_INPUT}
    Element Should Be Visible               ${MOVE_ADDITIONAL_DATA_COMMENT_CODEMIRROR}
    Element Should Be Visible               ${MOVE_ADDITIONAL_DATA_SUBMIT_BUTTON}
    Element Should Not Be Visible           ${MOVE_ADDITIONAL_DATA_DATE_HIDDEN_INPUT}
    Element Should Not Be Visible           ${MOVE_ADDITIONAL_DATA_HOUR_HIDDEN_INPUT}
    Element Should Not Be Visible           ${MOVE_ADDITIONAL_DATA_MINUTE_HIDDEN_INPUT}
    Element Should Not Be Visible           ${MOVE_ADDITIONAL_DATA_TIMEZONE_HIDDEN_INPUT}

Empty Username
    Sign Out Fast
    Go To Move
    Open Panel                              ${MOVE_ADDITIONAL_DATA_PANEL}
    Input Text                              ${MOVE_ADDITIONAL_DATA_USERNAME_INPUT}         ${EMPTY}
    Simulate Event                          ${MOVE_ADDITIONAL_DATA_USERNAME_INPUT}         blur
    Panel validation has error              ${MOVE_ADDITIONAL_DATA_PANEL}
    Element Text Should Be                  ${MOVE_ADDITIONAL_DATA_PANEL_HEADER_TEXT}      ${EMPTY}
    Input validation has error help         ${MOVE_ADDITIONAL_DATA_USERNAME_INPUT}         This value is required.

Check Valid Username
    [Template]    Check Valid Username
    123
    01234567890123456789
    Username
    U S E R N A M E
    ${SPACE*2}USERNAME${SPACE*2}
    🐔🐓🐣🐤🐥
    USERNAME
    Bird 🐦

Check Invalid Username
    [Template]    Check Invalid Username
    ${SPACE*1}
    ${SPACE*2}

Open DatetimePicker Via Button
    Sign Out Fast
    Go To Move
    Open Panel                              ${MOVE_ADDITIONAL_DATA_PANEL}
    Click Element                           ${MOVE_ADDITIONAL_DATA_DATE_TIME_PICKER_BUTTON}
    Wait Until Page Contains Element        ${MOVE_ADDITIONAL_DATA_DATE_TIME_PICKER_WIDGET}
    Element Should Be Visible               ${MOVE_ADDITIONAL_DATA_DATE_TIME_PICKER_WIDGET}

Open DatetimePicker Via Input
    Sign Out Fast
    Go To Move
    Open Panel                              ${MOVE_ADDITIONAL_DATA_PANEL}
    Open DateTimePicker
    Wait Until Page Contains Element        ${MOVE_ADDITIONAL_DATA_DATE_TIME_PICKER_WIDGET}
    Element Should Be Visible               ${MOVE_ADDITIONAL_DATA_DATE_TIME_PICKER_WIDGET}

DateTime Input Is read-only
    Sign Out Fast
    Go To Move
    Open Panel                              ${MOVE_ADDITIONAL_DATA_PANEL}
    Element Should Be Disabled              ${MOVE_ADDITIONAL_DATA_DATE_TIME_INPUT}

DateTime Input In The Future
    Sign Out Fast
    Go To Move
    Open Panel                              ${MOVE_ADDITIONAL_DATA_PANEL}
    Open DateTimePicker
    Click Element                           ${MOVE_ADDITIONAL_DATA_DATE_TIME_PICKER_WIDGET_HOUR_PLUS}
    Simulate Event                          ${MOVE_ADDITIONAL_DATA_DATE_TIME_INPUT}         blur
    Panel validation has error              ${MOVE_ADDITIONAL_DATA_PANEL}
    Element Text Should Be                  ${MOVE_ADDITIONAL_DATA_PANEL_HEADER_TEXT}       ${EMPTY}
    Input validation has error help         ${MOVE_ADDITIONAL_DATA_DATE_TIME_INPUT}         The date cannot be in the future.

Hidden DateTime Fields Should Be Filled
    Sign Out Fast
    Go To Move
    Open Panel                              ${MOVE_ADDITIONAL_DATA_PANEL}
    Set DateTime                            2020-08-12 07:30:22    +00:00
    Input Value Should Be                   ${MOVE_ADDITIONAL_DATA_DATE_HIDDEN_INPUT}           2020-08-12
    Input Value Should Be                   ${MOVE_ADDITIONAL_DATA_HOUR_HIDDEN_INPUT}           7
    Input Value Should Be                   ${MOVE_ADDITIONAL_DATA_MINUTE_HIDDEN_INPUT}         30
    Input Value Should Be                   ${MOVE_ADDITIONAL_DATA_TIMEZONE_HIDDEN_INPUT}       +00:00

Hidden DateTime Fields Should Be Filled With Timezone
    Sign Out Fast
    Go To Move
    Open Panel                              ${MOVE_ADDITIONAL_DATA_PANEL}
    Set DateTime                            2020-08-12 07:30:22    +03:00
    Input Value Should Be                   ${MOVE_ADDITIONAL_DATA_DATE_HIDDEN_INPUT}           2020-08-12
    Input Value Should Be                   ${MOVE_ADDITIONAL_DATA_HOUR_HIDDEN_INPUT}           10
    Input Value Should Be                   ${MOVE_ADDITIONAL_DATA_MINUTE_HIDDEN_INPUT}         30
    Input Value Should Be                   ${MOVE_ADDITIONAL_DATA_TIMEZONE_HIDDEN_INPUT}       +03:00

Fill Comment
    Sign Out Fast
    Go To Move
    Open Panel                              ${MOVE_ADDITIONAL_DATA_PANEL}
    Input Inscrybmde                        \#comment                                   TEST
    Textarea Value Should Be                ${MOVE_ADDITIONAL_DATA_COMMENT_INPUT}       TEST

# Test issue https://github.com/geokrety/geokrety-website/issues/741
Fill Comment Over Limit
    Sign Out Fast
    Go To Move
    Open Panel                              ${MOVE_ADDITIONAL_DATA_PANEL}
    ${text} =    Generate Random String     length=5120

    Input Inscrybmde                        \#comment                                   ${text}
    Textarea Value Should Be                ${MOVE_ADDITIONAL_DATA_COMMENT_INPUT}       ${text}
    Input validation has success            ${MOVE_ADDITIONAL_DATA_COMMENT_INPUT}

    Input Inscrybmde                        \#comment                                   A${text}
    Textarea Value Should Be                ${MOVE_ADDITIONAL_DATA_COMMENT_INPUT}       A${text}
    Input validation has error              ${MOVE_ADDITIONAL_DATA_COMMENT_INPUT}

    Input Inscrybmde                        \#comment                                   ${text}
    Textarea Value Should Be                ${MOVE_ADDITIONAL_DATA_COMMENT_INPUT}       ${text}
    Input validation has success            ${MOVE_ADDITIONAL_DATA_COMMENT_INPUT}

Click Next Without Filling Values Should Trigger Error As Anonymous
    Sign Out Fast
    Go To Move
    Open Panel                              ${MOVE_ADDITIONAL_DATA_PANEL}
    Click Button                            ${MOVE_ADDITIONAL_DATA_SUBMIT_BUTTON}
    Panel validation has error              ${MOVE_ADDITIONAL_DATA_PANEL}
    Input validation has error              ${MOVE_ADDITIONAL_DATA_USERNAME_INPUT}

Click Next Without Filling Values Should Trigger Success As Authenticated
    Sign In ${USER_1.name} Fast
    Go To Move
    Open Panel                              ${MOVE_ADDITIONAL_DATA_PANEL}
    Click Button                            ${MOVE_ADDITIONAL_DATA_SUBMIT_BUTTON}
    Panel validation has success            ${MOVE_ADDITIONAL_DATA_PANEL}

*** Keywords ***

Suite Setup
    Clear Database And Seed ${1} users

Check Valid Username
    [Arguments]    ${username}
    Sign Out Fast
    Go To Move
    Open Panel                              ${MOVE_ADDITIONAL_DATA_PANEL}
    Input Text                              ${MOVE_ADDITIONAL_DATA_USERNAME_INPUT}         ${username}
    Simulate Event                          ${MOVE_ADDITIONAL_DATA_USERNAME_INPUT}         blur
    Panel validation has success            ${MOVE_ADDITIONAL_DATA_PANEL}
    Input validation has success            ${MOVE_ADDITIONAL_DATA_USERNAME_INPUT}
    Element Should Contain                  ${MOVE_ADDITIONAL_DATA_PANEL_HEADER_TEXT}      ${EMPTY}

Check Invalid Username
    [Arguments]    ${username}
    Sign Out Fast
    Go To Move
    Open Panel                              ${MOVE_ADDITIONAL_DATA_PANEL}
    Input Text                              ${MOVE_ADDITIONAL_DATA_USERNAME_INPUT}         ${username}
    Simulate Event                          ${MOVE_ADDITIONAL_DATA_USERNAME_INPUT}         blur
    Panel validation has error              ${MOVE_ADDITIONAL_DATA_PANEL}
    Input validation has error              ${MOVE_ADDITIONAL_DATA_USERNAME_INPUT}
    Input validation has error help         ${MOVE_ADDITIONAL_DATA_USERNAME_INPUT}         This value is required.
    Element Text Should Be                  ${MOVE_ADDITIONAL_DATA_PANEL_HEADER_TEXT}      ${EMPTY}

Open DateTimePicker
    # let activate retry as sometimes the ToolTip is still over element
    Wait Until Keyword Succeeds    2x    200ms    Click Element    ${MOVE_ADDITIONAL_DATA_DATE_TIME_INPUT}
    Wait Until Page Contains Element        ${MOVE_ADDITIONAL_DATA_DATE_TIME_PICKER_WIDGET}
