*** Settings ***
Library         SeleniumLibrary  timeout=10  implicit_wait=0
Resource        ../functions/FunctionsGlobal.robot
Resource        ../functions/PageMoves.robot
Resource        ../vars/users.resource
Resource        ../vars/waypoints.resource
Resource        ../vars/geokrety.resource
Force Tags      Moves    Timezone
Test Setup     Seed

*** Test Cases ***

Fill Form Naturally
    Sign Out Fast
    Go To Url                               ${PAGE_MOVES_URL}

    Input Text                              ${MOVE_TRACKING_CODE_INPUT}                 ${GEOKRETY_1.tc}
    Click Button                            ${MOVE_TRACKING_CODE_NEXT_BUTTON}
    Panel validation has success            ${MOVE_TRACKING_CODE_PANEL}

    Click Move Type                         ${MOVE_LOG_TYPE_DROPPED_RADIO}
    Click Button                            ${MOVE_LOG_TYPE_NEXT_BUTTON}
    Panel validation has success            ${MOVE_LOG_TYPE_PANEL}

    Input Text                              ${MOVE_NEW_LOCATION_WAYPOINT_INPUT}         ${WPT_OC_1.id}
    Click Button                            ${MOVE_NEW_LOCATION_NEXT_BUTTON}
    Panel validation has success            ${MOVE_NEW_LOCATION_PANEL}

    Input Text                              ${MOVE_ADDITIONAL_DATA_USERNAME_INPUT}      ${USER_1.name}
    Input Inscrybmde                        \#comment                                   TEST
    Panel validation has success            ${MOVE_ADDITIONAL_DATA_PANEL}
    Click Button                            ${MOVE_ADDITIONAL_DATA_SUBMIT_BUTTON}

    Wait Until Location Is                  ${PAGE_GEOKRETY_1_DETAILS_URL}/page/1\#log1

Found It Log It From Home Page
    Sign Out Fast
    Go To Url                               ${PAGE_HOME_URL}

    Input Text                              ${GEOKRET_DETAILS_FOUND_IT_TRACKING_CODE}   ${GEOKRETY_1.tc}
    Click Button                            ${HOME_FOUND_GK_TRACKING_CODE_BUTTON}
    Location Should Be                      ${PAGE_MOVES_URL}?tracking_code=${GEOKRETY_1.tc}

    Click Button                            ${MOVE_TRACKING_CODE_NEXT_BUTTON}
    Click Move Type                         ${MOVE_LOG_TYPE_GRABBED_RADIO}
    Click Button                            ${MOVE_LOG_TYPE_NEXT_BUTTON}
    Input Text                              ${MOVE_ADDITIONAL_DATA_USERNAME_INPUT}      ${USER_1.name}
    Input Inscrybmde                        \#comment                                   TEST
    Click Button                            ${MOVE_ADDITIONAL_DATA_SUBMIT_BUTTON}
    Wait Until Location Is                  ${PAGE_GEOKRETY_1_DETAILS_URL}/page/1\#log1

Found It Log It From GeoKret Page
    Sign Out Fast
    Go To Url                               ${PAGE_GEOKRETY_1_DETAILS_URL}

    Input Text                              ${GEOKRET_DETAILS_FOUND_IT_TRACKING_CODE}   ${GEOKRETY_1.tc}
    Click Button                            ${GEOKRET_DETAILS_FOUND_IT_BUTTON}
    Location Should Be                      ${PAGE_MOVES_URL}?tracking_code=${GEOKRETY_1.tc}

    Click Button                            ${MOVE_TRACKING_CODE_NEXT_BUTTON}
    Click Move Type                         ${MOVE_LOG_TYPE_DIPPED_RADIO}
    Click Button                            ${MOVE_LOG_TYPE_NEXT_BUTTON}
    Input Text                              ${MOVE_NEW_LOCATION_WAYPOINT_INPUT}         ${WPT_OC_1.id}
    Click Button                            ${MOVE_NEW_LOCATION_NEXT_BUTTON}
    Input Text                              ${MOVE_ADDITIONAL_DATA_USERNAME_INPUT}      ${USER_1.name}
    Input Inscrybmde                        \#comment                                   TEST
    Click Button                            ${MOVE_ADDITIONAL_DATA_SUBMIT_BUTTON}
    Wait Until Location Is                  ${PAGE_GEOKRETY_1_DETAILS_URL}/page/1\#log1

Check csrf
    Create Session                          gk      ${GK_URL}
    ${auth} =           GET On Session      gk      /devel/
    ${auth} =           GET On Session      gk      /devel/users/${USER_1.name}/login
    ${resp} =           POST On Session     gk      url=/en/moves?skip_csrf=False       expected_status=200
    ${body} =           Convert To String   ${resp.content}
    Should Contain                          ${body}    CSRF error, please try again.
    Delete All Sessions



    # TODO Check log on GK page
    # TODO Check log on Home page
    # TODO Check user inventory
    # TODO Check user owned
    # TODO Check user moves owned
    # TODO Check user moves


*** Keywords ***

Seed
    Clear DB And Seed 1 users
    Seed 2 geokrety owned by 1
    Seed 3 waypoints OC

Set DateTime
    [Arguments]    ${datetime}=2020-08-12 07:30:00    ${timezone}=+00:00
    Execute Javascript                      $("#datetimepicker").data("DateTimePicker").date(moment.utc("${datetime}").zone("${timezone}"));
    Simulate Event                          ${MOVE_ADDITIONAL_DATA_DATE_TIME_INPUT}         blur
