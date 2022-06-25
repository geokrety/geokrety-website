*** Settings ***
Library         RobotEyes
Resource        ../functions/FunctionsGlobal.robot
Resource        ../functions/PageMoves.robot
Resource        ../vars/waypoints.resource
Force Tags      Moves    Location    RobotEyes
Suite Setup     Seed

*** Test Cases ***

Form Initial Status
    Go To Url                               ${PAGE_MOVES_URL}
    Open Panel                              ${MOVE_NEW_LOCATION_PANEL}
    Element Should Be Visible               ${MOVE_NEW_LOCATION_WAYPOINT_INPUT}
    Element Should Be Visible               ${MOVE_NEW_LOCATION_OC_BUTTON}
    Element Should Be Visible               ${MOVE_NEW_LOCATION_SEARCH_BUTTON}
    Element Should Not Be Visible           ${MOVE_NEW_LOCATION_OC_INPUT}
    Element Should Not Be Visible           ${MOVE_NEW_LOCATION_MAP_PANEL}

Empty Waypoint
    Go To Url                               ${PAGE_MOVES_URL}
    Open Panel                              ${MOVE_NEW_LOCATION_PANEL}
    Input Text                              ${MOVE_NEW_LOCATION_WAYPOINT_INPUT}         ${EMPTY}
    Simulate Event                          ${MOVE_NEW_LOCATION_WAYPOINT_INPUT}         blur
    Panel validation has error              ${MOVE_NEW_LOCATION_PANEL}
    Element Text Should Be                  ${MOVE_NEW_LOCATION_PANEL_HEADER_TEXT}      ${EMPTY}
    Input validation has error help         ${MOVE_NEW_LOCATION_WAYPOINT_INPUT}         Waypoint seems empty.
    Element Should Be Visible               ${MOVE_NEW_LOCATION_MAP_PANEL}

Invalid Waypoint
    Go To Url                               ${PAGE_MOVES_URL}
    Open Panel                              ${MOVE_NEW_LOCATION_PANEL}
    Input Text                              ${MOVE_NEW_LOCATION_WAYPOINT_INPUT}         ${INVALID_WPT}
    Simulate Event                          ${MOVE_NEW_LOCATION_WAYPOINT_INPUT}         blur
    Panel validation has error              ${MOVE_NEW_LOCATION_PANEL}
    Input validation has error help         ${MOVE_NEW_LOCATION_WAYPOINT_INPUT}         View the cache page.
    Input validation has error help         ${MOVE_NEW_LOCATION_WAYPOINT_INPUT}         Sorry, but this waypoint is not (yet) in our database.
    Element Should Be Visible               ${MOVE_NEW_LOCATION_MAP_PANEL}
    Element Should Not Be Visible           ${MOVE_NEW_LOCATION_MAP_COORDINATES_INPUT}

Coordinates Field Can Be Manually Open
    Go To Url                               ${PAGE_MOVES_URL}
    Open Panel                              ${MOVE_NEW_LOCATION_PANEL}
    Input Text                              ${MOVE_NEW_LOCATION_WAYPOINT_INPUT}         ${INVALID_WPT}
    Simulate Event                          ${MOVE_NEW_LOCATION_WAYPOINT_INPUT}         blur
    Panel validation has error              ${MOVE_NEW_LOCATION_PANEL}
    Element Should Not Be Visible           ${MOVE_NEW_LOCATION_MAP_COORDINATES_INPUT}
    Click Element                           ${MOVE_NEW_LOCATION_MAP_PANEL_HEADER}
    Element Should Be Visible               ${MOVE_NEW_LOCATION_MAP_COORDINATES_INPUT}

Invalid GC Waypoint
    Go To Url                               ${PAGE_MOVES_URL}
    Open Panel                              ${MOVE_NEW_LOCATION_PANEL}
    Input Text                              ${MOVE_NEW_LOCATION_WAYPOINT_INPUT}         ${INVALID_GC_WPT}
    Simulate Event                          ${MOVE_NEW_LOCATION_WAYPOINT_INPUT}         blur
    Panel validation has error              ${MOVE_NEW_LOCATION_PANEL}
    Input validation has error help         ${MOVE_NEW_LOCATION_WAYPOINT_INPUT}         View the cache page.
    Input validation has error help         ${MOVE_NEW_LOCATION_WAYPOINT_INPUT}         This is a Geocaching.com cache that no one logged yet on GeoKrety.org.
    Element Should Be Visible               ${MOVE_NEW_LOCATION_MAP_PANEL}
    Element Should Be Visible               ${MOVE_NEW_LOCATION_MAP_COORDINATES_INPUT}

Fill Valid Waypoint Validate The Form
    [Template]    Fill Valid Waypoint Validate The Form
    ${WPT_OC_1.id}      ${WPT_OC_1.coords}
    ${WPT_GC_1.id}      ${WPT_GC_1.coords}

Fill Coordinates Validate The Form
    [Template]    Fill Coordinates Validate The Form
    ${INVALID_WPT}      ${WPT_GC_1.coords}
    ${INVALID_GC_WPT}   ${WPT_GC_1.coords}

Fill Coordinates With Invalid Coordinates
    [Template]    Fill Coordinates Validate The Form As Error
    ${INVALID_GC_WPT}    A
    ${INVALID_GC_WPT}    1
    ${INVALID_GC_WPT}    1111111111 222222222

Fill Coordinates Show Map Centered
    Fill Coordinates                        ${INVALID_GC_WPT}                   ${WPT_GC_1.coords}
    Wait Until Page Contains Element        //*[@id="mapid" and @data-map-loaded="true"]    timeout=30
    Check Image                             ${MOVE_NEW_LOCATION_MAP_MAP}

No Selection Should Show Error
    Go To Url                               ${PAGE_MOVES_URL}
    Open Panel                              ${MOVE_NEW_LOCATION_PANEL}
    Click Button                            ${MOVE_NEW_LOCATION_NEXT_BUTTON}
    Panel validation has error              ${MOVE_NEW_LOCATION_PANEL}

Start Typing A GC Waypoint Remove The OC Button
    [Tags]    TODO
    Go To Url                               ${PAGE_MOVES_URL}
    Open Panel                              ${MOVE_NEW_LOCATION_PANEL}
    Element Should Be Visible               ${MOVE_NEW_LOCATION_OC_BUTTON}
    Input Text                              ${MOVE_NEW_LOCATION_WAYPOINT_INPUT}         GC
    Simulate Event                          ${MOVE_NEW_LOCATION_WAYPOINT_INPUT}         blur
    Element Should Not Be Visible           ${MOVE_NEW_LOCATION_OC_BUTTON}

Open OC Search Field
    Go To Url                               ${PAGE_MOVES_URL}
    Open Panel                              ${MOVE_NEW_LOCATION_PANEL}
    Element Should Not Be Visible           ${MOVE_NEW_LOCATION_OC_INPUT}
    Click Button                            ${MOVE_NEW_LOCATION_OC_BUTTON}
    Element Should Be Visible               ${MOVE_NEW_LOCATION_OC_INPUT}

OC Autocomplete Displayed After 4th Character
    Go To Url                               ${PAGE_MOVES_URL}
    Open Panel                              ${MOVE_NEW_LOCATION_PANEL}
    Click Button                            ${MOVE_NEW_LOCATION_OC_BUTTON}
    Input Text                              ${MOVE_NEW_LOCATION_OC_INPUT}               Way
    Run Keyword And Expect Error    missing    Wait Until Page Contains Element    ${MOVE_NEW_LOCATION_OC_INPUT_TYPEAHEAD}    timeout=0.5    error=missing

    Input Text                              ${MOVE_NEW_LOCATION_OC_INPUT}               Wayp
    Wait Until Page Contains Element        ${MOVE_NEW_LOCATION_OC_INPUT_TYPEAHEAD}
    Element Should Be Visible               ${MOVE_NEW_LOCATION_OC_INPUT_TYPEAHEAD}

OC Autocomplete Responses
    Go To Url                               ${PAGE_MOVES_URL}
    Open Panel                              ${MOVE_NEW_LOCATION_PANEL}
    Click Button                            ${MOVE_NEW_LOCATION_OC_BUTTON}
    Input Text                              ${MOVE_NEW_LOCATION_OC_INPUT}                   Wayp
    Wait Until Page Contains Element        ${MOVE_NEW_LOCATION_OC_INPUT_TYPEAHEAD}
    Element Count Should Be                 ${MOVE_NEW_LOCATION_OC_INPUT_TYPEAHEAD_ITEMS}   3

OC Autocomplete Responses Exact Match
    Go To Url                               ${PAGE_MOVES_URL}
    Open Panel                              ${MOVE_NEW_LOCATION_PANEL}
    Click Button                            ${MOVE_NEW_LOCATION_OC_BUTTON}
    Input Text                              ${MOVE_NEW_LOCATION_OC_INPUT}                   ${WPT_OC_1.name}
    Wait Until Page Contains Element        ${MOVE_NEW_LOCATION_OC_INPUT_TYPEAHEAD}
    Element Count Should Be                 ${MOVE_NEW_LOCATION_OC_INPUT_TYPEAHEAD_ITEMS}   1

Select Cache Name From OC Autocomplete Fill Waypoint
    Go To Url                               ${PAGE_MOVES_URL}
    Open Panel                              ${MOVE_NEW_LOCATION_PANEL}
    Click Button                            ${MOVE_NEW_LOCATION_OC_BUTTON}
    Input Text                              ${MOVE_NEW_LOCATION_OC_INPUT}                   ${WPT_OC_1.name}
    Wait Until Page Contains Element        ${MOVE_NEW_LOCATION_OC_INPUT_TYPEAHEAD}
    Click Element                           ${MOVE_NEW_LOCATION_OC_INPUT_TYPEAHEAD_ITEMS}\[1]
    Textfield Value Should Be               ${MOVE_NEW_LOCATION_WAYPOINT_INPUT}             ${WPT_OC_1.id}
    Panel validation has success            ${MOVE_NEW_LOCATION_PANEL}
    Element Text Should Be                  ${MOVE_NEW_LOCATION_PANEL_HEADER_TEXT}          ${WPT_OC_1.id}
    Textfield Value Should Be               ${MOVE_NEW_LOCATION_MAP_COORDINATES_INPUT}      ${WPT_OC_1.coords}
    Wait Until Page Contains Element        //*[@id="mapid" and @data-map-loaded="true"]    timeout=30
    Check Image                             ${MOVE_NEW_LOCATION_MAP_MAP}

Click Next With Known Waypoint Raise Validation Success
    Go To Url                               ${PAGE_MOVES_URL}
    Open Panel                              ${MOVE_NEW_LOCATION_PANEL}
    Input Text                              ${MOVE_NEW_LOCATION_WAYPOINT_INPUT}      ${WPT_OC_1.id}
    Click Button                            ${MOVE_NEW_LOCATION_NEXT_BUTTON}
    Panel validation has success            ${MOVE_NEW_LOCATION_PANEL}

Click Next With Empty Waypoint Raise Validation Error
    Go To Url                               ${PAGE_MOVES_URL}
    Open Panel                              ${MOVE_NEW_LOCATION_PANEL}
    Click Button                            ${MOVE_NEW_LOCATION_NEXT_BUTTON}
    Panel validation has error              ${MOVE_NEW_LOCATION_PANEL}

Click Next With UnKnown GC Waypoint Raise Validation Error
    Go To Url                               ${PAGE_MOVES_URL}
    Open Panel                              ${MOVE_NEW_LOCATION_PANEL}
    Input Text                              ${MOVE_NEW_LOCATION_WAYPOINT_INPUT}      ${INVALID_GC_WPT}
    Click Button                            ${MOVE_NEW_LOCATION_NEXT_BUTTON}
    Panel validation has error              ${MOVE_NEW_LOCATION_PANEL}

*** Keywords ***

Seed
    Clear DB And Seed 1 users
    Seed 1 geokrety owned by 1
    Seed 3 waypoints OC
    Seed 1 waypoints GC
    Sign Out Fast

Fill Valid Waypoint Validate The Form
    [Arguments]    ${wpt}    ${coords}
    Go To Url                               ${PAGE_MOVES_URL}
    Open Panel                              ${MOVE_NEW_LOCATION_PANEL}
    Input Text                              ${MOVE_NEW_LOCATION_WAYPOINT_INPUT}         ${wpt}
    Panel validation has success            ${MOVE_NEW_LOCATION_PANEL}
    Input validation has success            ${MOVE_NEW_LOCATION_WAYPOINT_INPUT}
    Input validation has success            ${MOVE_NEW_LOCATION_MAP_COORDINATES_INPUT}
    Element Should Be Visible               ${MOVE_NEW_LOCATION_MAP_PANEL}

Fill Coordinates Validate The Form
    [Arguments]    ${wpt}    ${coords}
    Fill Coordinates                        ${wpt}    ${coords}
    Panel validation has success            ${MOVE_NEW_LOCATION_PANEL}
    Input validation has success            ${MOVE_NEW_LOCATION_WAYPOINT_INPUT}
    Input validation has success            ${MOVE_NEW_LOCATION_MAP_COORDINATES_INPUT}

Fill Coordinates Validate The Form As Error
    [Arguments]    ${wpt}    ${coords}
    Fill Coordinates                        ${wpt}    ${coords}
    Panel validation has error              ${MOVE_NEW_LOCATION_PANEL}
    Input validation has error              ${MOVE_NEW_LOCATION_WAYPOINT_INPUT}











#
