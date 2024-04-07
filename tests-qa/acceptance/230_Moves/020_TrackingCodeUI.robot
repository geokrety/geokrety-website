*** Settings ***
Library         RequestsLibrary
Resource        ../ressources/Authentication.robot
Variables       ../ressources/vars/users.yml
Variables       ../ressources/vars/geokrety.yml
Suite Setup     Suite Setup
Test Setup      Test Setup

*** Test Cases ***

Button Trigger Validation
    [Template]    Button Trigger Validation With Wrong TC
    ${EMPTY}
    ${SPACE}
    A
    ABCDEF

Blur Trigger Validation On 6th Character
    Input Text                              ${MOVE_TRACKING_CODE_INPUT}                 A
    Simulate Event                          ${MOVE_TRACKING_CODE_INPUT}                 blur
    Page Should Not Contain                 Sorry, but Tracking Code
    Wait For Text To Not Appear             Sorry, but Tracking Code

    Input Text                              ${MOVE_TRACKING_CODE_INPUT}                 ABCDE
    Simulate Event                          ${MOVE_TRACKING_CODE_INPUT}                 blur
    Page Should Not Contain                 Sorry, but Tracking Code
    Wait For Text To Not Appear             Sorry, but Tracking Code

    Input Text                              ${MOVE_TRACKING_CODE_INPUT}                 ABCDEF
    Simulate Event                          ${MOVE_TRACKING_CODE_INPUT}                 blur
    Wait Until Page Contains                Sorry, but Tracking Code

Next Trigger Validation - Valid
    Go To Url                               ${PAGE_MOVES_URL}
    Input Text                              ${MOVE_TRACKING_CODE_INPUT}                 ${GEOKRETY_1.tc}
    Click Button And Check Panel Validation Has Success

Next Trigger Validation - Invalid
    Go To Url                               ${PAGE_MOVES_URL}
    Input Text                              ${MOVE_TRACKING_CODE_INPUT}                 ABCDEF
    Click Button                            ${MOVE_TRACKING_CODE_NEXT_BUTTON}
    Panel validation has error              ${MOVE_TRACKING_CODE_PANEL}
    Panel Is Open                           ${MOVE_TRACKING_CODE_PANEL}

*** Keywords ***

Suite Setup
    Clear Database And Seed ${1} users
    Seed ${1} geokrety owned by ${1}
    Sign Out Fast

Test Setup
    Go To Move

Button Trigger Validation With Wrong TC
    [Arguments]    ${tc}
    Input Text                              ${MOVE_TRACKING_CODE_INPUT}                 ${tc}
    Click Button                            ${MOVE_TRACKING_CODE_CHECK_BUTTON}
    Panel validation has error              ${MOVE_TRACKING_CODE_PANEL}

Click Button And Check Panel Validation Has Success
    Click Button                            ${MOVE_TRACKING_CODE_NEXT_BUTTON}
    Panel validation has success            ${MOVE_TRACKING_CODE_PANEL}
    Panel Is Collapsed                      ${MOVE_TRACKING_CODE_PANEL}
