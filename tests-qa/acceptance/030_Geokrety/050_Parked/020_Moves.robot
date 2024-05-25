*** Settings ***
Library         RequestsLibrary
Resource        ../../ressources/Authentication.robot
Resource        ../../ressources/vars/Urls.robot
Resource        ../../ressources/Moves.robot
Variables       ../../ressources/vars/users.yml
Variables       ../../ressources/vars/geokrety.yml
Variables       ../../ressources/vars/moves.yml
Test Setup      Test Setup

*** Variables ***

*** Test Cases ***

Some logtype should be hidden for non-owner
    Sign In ${USER_1.name} Fast

    # Change collectible flag
    Go To Url                               ${PAGE_GEOKRETY_EDIT_URL}
    Select Checkbox                         ${GEOKRET_CREATE_PARKED_CHECKBOX}
    Click Button                            ${GEOKRET_CREATE_CREATE_BUTTON}
    Location Should Be                      ${PAGE_GEOKRETY_1_DETAILS_URL}
    Element Should Contain                  ${GEOKRET_DETAILS_PARKED}    Parked

    # Only a limited number of move type are shown
    Sign In ${USER_2.name} Fast
    Go To Move
    Input Text                              ${MOVE_TRACKING_CODE_INPUT}                 ${GEOKRETY_1.tc}
    Click Button And Check Panel Validation Has Success    ${MOVE_TRACKING_CODE_NEXT_BUTTON}    ${MOVE_TRACKING_CODE_PANEL}    ${MOVE_LOG_TYPE_PANEL}
    Element Should Be Disabled              ${MOVE_LOG_TYPE_DROPPED_RADIO}
    Element Should Be Disabled              ${MOVE_LOG_TYPE_GRABBED_RADIO}
    Element Should Be Enabled               ${MOVE_LOG_TYPE_MEET_RADIO}
    Element Should Be Disabled              ${MOVE_LOG_TYPE_DIPPED_RADIO}
    Element Should Be Enabled               ${MOVE_LOG_TYPE_COMMENT_RADIO}
    Element Should Be Visible               ${MOVE_LOG_TYPE_NOT_COLLECTIBLE_INFO}

Some logtype should be hidden for owner
    Sign In ${USER_1.name} Fast

    # Change parked flag
    Go To Url                               ${PAGE_GEOKRETY_EDIT_URL}
    Select Checkbox                         ${GEOKRET_CREATE_PARKED_CHECKBOX}
    Click Button                            ${GEOKRET_CREATE_CREATE_BUTTON}
    Location Should Be                      ${PAGE_GEOKRETY_1_DETAILS_URL}
    Element Should Contain                  ${GEOKRET_DETAILS_PARKED}    Parked

    # Only a limited number of move type are shown
    Sign In ${USER_1.name} Fast
    Go To Move
    Input Text                              ${MOVE_TRACKING_CODE_INPUT}                 ${GEOKRETY_1.tc}
    Click Button And Check Panel Validation Has Success    ${MOVE_TRACKING_CODE_NEXT_BUTTON}    ${MOVE_TRACKING_CODE_PANEL}    ${MOVE_LOG_TYPE_PANEL}
    Element Should Be Disabled              ${MOVE_LOG_TYPE_DROPPED_RADIO}
    Element Should Be Disabled              ${MOVE_LOG_TYPE_GRABBED_RADIO}
    Element Should Be Disabled              ${MOVE_LOG_TYPE_MEET_RADIO}
    Element Should Be Enabled               ${MOVE_LOG_TYPE_DIPPED_RADIO}
    Element Should Be Enabled               ${MOVE_LOG_TYPE_COMMENT_RADIO}
    Element Should Be Visible               ${MOVE_LOG_TYPE_NOT_COLLECTIBLE_INFO}


*** Keywords ***

Test Setup
    Clear Database And Seed ${3} users
    Seed ${1} geokrety owned by ${1}

Click Button And Check Panel Validation Has Success
    [Arguments]    ${button}    ${current_panel}    ${next_panel}
    Panel validation has success            ${current_panel}
    Click Button                            ${button}
    Panel Is Collapsed                      ${current_panel}
    Panel Is Open                           ${next_panel}
