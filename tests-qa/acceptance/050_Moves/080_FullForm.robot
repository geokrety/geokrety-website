*** Settings ***
Library         DateTime
Resource        ../ressources/Authentication.robot
Resource        ../ressources/Inscrybmde.robot
Resource        ../ressources/Moves.robot
Resource        ../ressources/vars/pages/Home.robot
Variables       ../ressources/vars/users.yml
Variables       ../ressources/vars/geokrety.yml
Variables       ../ressources/vars/waypoints.yml
Test Setup      Test Setup

*** Test Cases ***

Fill Form Naturally Require Coordinates
    Sign Out Fast
    Go To Move

    Input Text                              ${MOVE_TRACKING_CODE_INPUT}                 ${GEOKRETY_1.tc}
    Click Button And Check Panel Validation Has Success    ${MOVE_TRACKING_CODE_NEXT_BUTTON}    ${MOVE_TRACKING_CODE_PANEL}    ${MOVE_LOG_TYPE_PANEL}

    Click LogType And Check Panel Validation Has Success    ${MOVE_LOG_TYPE_DIPPED_RADIO}    ${MOVE_LOG_TYPE_PANEL}    ${MOVE_NEW_LOCATION_PANEL}

    Input Text                              ${MOVE_NEW_LOCATION_WAYPOINT_INPUT}         ${WPT_OC_1.id}
    Simulate Event                          ${MOVE_NEW_LOCATION_WAYPOINT_INPUT}         blur
    Click Button And Check Panel Validation Has Success    ${MOVE_NEW_LOCATION_NEXT_BUTTON}    ${MOVE_NEW_LOCATION_PANEL}    ${MOVE_ADDITIONAL_DATA_PANEL}

    Input Text                              ${MOVE_ADDITIONAL_DATA_USERNAME_INPUT}      ${USER_1.name}
    Input Inscrybmde                        \#comment                                   TEST
    Panel validation has success            ${MOVE_ADDITIONAL_DATA_PANEL}

    ${before}=    Get Current Date    result_format=epoch
    Click Button                            ${MOVE_ADDITIONAL_DATA_SUBMIT_BUTTON}
    Wait Until Location Is                  ${PAGE_GEOKRETY_1_DETAILS_URL}/page/1\#log1
    ${after}=       Get Current Date    result_format=epoch
    Should be True    ${after} - ${before} < 1     msg=The total page load time was more than 1s!


Fill Form Naturally Doesn t Require Coordinates
    Sign Out Fast
    Go To Home

    Input Text                              ${GEOKRET_DETAILS_FOUND_IT_TRACKING_CODE}   ${GEOKRETY_1.tc}
    Click Button                            ${HOME_FOUND_GK_TRACKING_CODE_BUTTON}
    Location Should Be                      ${PAGE_MOVES_URL}?tracking_code=${GEOKRETY_1.tc}

    Click Button And Check Panel Validation Has Success     ${MOVE_TRACKING_CODE_NEXT_BUTTON}     ${MOVE_TRACKING_CODE_PANEL}     ${MOVE_LOG_TYPE_PANEL}
    Click LogType And Check Panel Validation Has Success    ${MOVE_LOG_TYPE_GRABBED_RADIO}        ${MOVE_LOG_TYPE_PANEL}          ${MOVE_ADDITIONAL_DATA_PANEL}

    Input Text                              ${MOVE_ADDITIONAL_DATA_USERNAME_INPUT}      ${USER_1.name}
    Input Inscrybmde                        \#comment                                   TEST
    Click Button                            ${MOVE_ADDITIONAL_DATA_SUBMIT_BUTTON}
    Wait Until Location Is                  ${PAGE_GEOKRETY_1_DETAILS_URL}/page/1\#log1

Found It Log It From Home Page
    Sign Out Fast
    Go To Home

    Input Text                              ${GEOKRET_DETAILS_FOUND_IT_TRACKING_CODE}   ${GEOKRETY_1.tc}
    Click Button                            ${HOME_FOUND_GK_TRACKING_CODE_BUTTON}
    Location Should Be                      ${PAGE_MOVES_URL}?tracking_code=${GEOKRETY_1.tc}
    Input Value Should Be                   ${MOVE_TRACKING_CODE_INPUT}   ${GEOKRETY_1.tc}

Found It Log It From GeoKret Page
    Sign Out Fast
    Go To GeoKrety ${1}

    Input Text                              ${GEOKRET_DETAILS_FOUND_IT_TRACKING_CODE}   ${GEOKRETY_1.tc}
    Click Button                            ${GEOKRET_DETAILS_FOUND_IT_BUTTON}
    Location Should Be                      ${PAGE_MOVES_URL}?tracking_code=${GEOKRETY_1.tc}
    Input Value Should Be                   ${MOVE_TRACKING_CODE_INPUT}   ${GEOKRETY_1.tc}


Check csrf
    Create Session                          gk      ${GK_URL}
    ${auth} =           GET On Session      gk      /devel/
    ${auth} =           GET On Session      gk      /devel/users/${USER_1.name}/login
    ${resp} =           POST On Session     gk      url=/en/moves?skip_csrf=False       expected_status=200
    ${body} =           Convert To String   ${resp.content}
    Should Contain                          ${body}    CSRF error, please try again.
    Delete All Sessions



*** Keywords ***

Test Setup
    Clear Database And Seed ${1} users
    Seed 2 geokrety owned by 1
    Seed 3 waypoints OC

Set DateTime
    [Arguments]    ${datetime}=2020-08-12 07:30:00    ${timezone}=+00:00
    Execute Javascript                      $("#datetimepicker").data("DateTimePicker").date(moment.utc("${datetime}").zone("${timezone}"));
    Simulate Event                          ${MOVE_ADDITIONAL_DATA_DATE_TIME_INPUT}         blur

Click Button And Check Panel Validation Has Success
    [Arguments]    ${button}    ${current_panel}    ${next_panel}
    Panel validation has success            ${current_panel}
    Click Button                            ${button}
    Panel Is Collapsed                      ${current_panel}
    Panel Is Open                           ${next_panel}

Click LogType And Check Panel Validation Has Success
    [Arguments]    ${radio_value}    ${current_panel}    ${next_panel}
    Click Move Type    ${radio_value}
    Panel validation has success            ${current_panel}
    # Panel Is Collapsed                      ${current_panel}
    Panel Is Open                           ${next_panel}
