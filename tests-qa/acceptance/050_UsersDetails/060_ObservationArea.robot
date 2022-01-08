*** Settings ***
Library         RobotEyes
Resource        ../functions/FunctionsGlobal.robot
Resource        ../vars/users.resource
Force Tags      Users Details    RobotEyes
Suite Setup     Seed

*** Variables ***
&{COORDS_NEW_YORK}     lat=40.73700    lon=-73.92300

*** Test Cases ***

Anonymous users are refused
    Sign Out Fast
    Go To                                   ${PAGE_USER_1_OBSERVATION_AREA_URL}
    Page Should Contain                     ${UNAUTHORIZED}

# In fact it update currently connected user, whatever the url is
Users cannot edit other user observation area
    [Tags]    TODO
    Sign In ${USER_2.name} Fast
    Go To                                   ${PAGE_USER_1_OBSERVATION_AREA_URL}
    Page Should Contain                     ${UNAUTHORIZED}
    Sign Out Fast


Move pan should update coordinates field
    Sign In ${USER_1.name} Fast
    Go To                                   ${PAGE_USER_1_OBSERVATION_AREA_URL}
    Wait Until Page Contains Element        ${USER_OBSERVATION_AREA_MAP}//div
    Execute Javascript                      $("#mapid").data('map').panTo(new L.LatLng(${COORDS_NEW_YORK.lat}, ${COORDS_NEW_YORK.lon}))
    Textfield Value Should Be               ${USER_OBSERVATION_AREA_COORDINATES_INPUT}    ${COORDS_NEW_YORK.lat} ${COORDS_NEW_YORK.lon}

Observation radius is set to 0 by default
    Sign In ${USER_1.name} Fast
    Go To                                   ${PAGE_USER_1_OBSERVATION_AREA_URL}
    Textfield Value Should Be               ${USER_OBSERVATION_AREA_RADIUS_INPUT}    0

Observation radius between 0-10
    [Template]    Check valid radius
    0
    1
    2
    3
    4
    5
    6
    7
    8
    9
    10

Observation radius outside range
    [Template]    Check valid radius
    -1    5
    11    5

Map should reflect manual coordinates input
    Sign In ${USER_1.name} Fast
    Go To                                   ${PAGE_USER_1_OBSERVATION_AREA_URL}
    Fill form                               ${COORDS_NEW_YORK}    10
    Wait Until Page Contains Element        //*[@id="mapid" and @data-map-loaded="true"]    timeout=30

    Wait Until Keyword Succeeds    2x    200ms    Check Image    ${USER_OBSERVATION_AREA_MAP}

Map circle should reflect observation radius
    [Template]    Check Map circle should reflect observation
    0
    1
    5
    10

Check valid coordinates
    [Template]    Check valid coordinates
    52.1534 21.0539                         52.15342 21.05390
    N 52° 09.204 E 021° 03.234              52.15342 21.05390
    N 52° 9' 12.2400" E 21° 3' 14.0400      52.15342 21.05390

Check invalid coordinates
    [Template]    Check invalid coordinates
    a b
    0
    ${SPACE}

Save Observation Area Preferences
    Sign In ${USER_1.name} Fast
    Go To                                   ${PAGE_USER_1_OBSERVATION_AREA_URL}
    Fill form                               ${COORDS_NEW_YORK}    10
    Click Button                            ${USER_OBSERVATION_AREA_SUBMIT}
    Wait Until Page Contains                Your home coordinates were successfully saved.
    Page Should Not Contain                 No home coordinates have been defined

    Go To                                   ${PAGE_USER_1_PROFILE_URL}
    Wait Until Page Contains Element        //*[@id="mapid" and @data-map-loaded="true"]    timeout=30

    Open Eyes                               SeleniumLibrary  6
    Scroll To Element                       ${USER_PROFILE_MINI_MAP_PANEL}
    Wait Until Element Is Visible           ${USER_PROFILE_MINI_MAP_PANEL}
    Capture Element                         ${USER_PROFILE_MINI_MAP_PANEL}
    Compare Images

Empty Coordinates Clear User's Home Location
    Sign In ${USER_1.name} Fast
    Go To                                   ${PAGE_USER_1_OBSERVATION_AREA_URL}
    Fill form                               ${COORDS_NEW_YORK}    ${EMPTY}
    Input Text                              ${USER_OBSERVATION_AREA_COORDINATES_INPUT}      ${EMPTY}
    Simulate Event                          ${USER_OBSERVATION_AREA_COORDINATES_INPUT}      blur
    Click Button                            ${USER_OBSERVATION_AREA_SUBMIT}
    Wait Until Page Contains                Your home coordinates were successfully saved.
    Page Should Contain                     No home coordinates have been defined

Observation area to 0 show message on save
    Sign In ${USER_1.name} Fast
    Go To                                   ${PAGE_USER_1_OBSERVATION_AREA_URL}
    Fill form                               ${COORDS_NEW_YORK}    0
    Click Button                            ${USER_OBSERVATION_AREA_SUBMIT}
    Page Should Contain                     Observation area is disabled, GeoKrety dropped around you will not be included in your daily mails

*** Keywords ***

Seed
    Clear Database
    Seed 2 users

Check valid coordinates
    [Arguments]    ${input}    ${expect}
    Sign In ${USER_1.name} Fast
    Go To                                   ${PAGE_USER_1_OBSERVATION_AREA_URL}
    Input Text                              ${USER_OBSERVATION_AREA_COORDINATES_INPUT}              ${input}
    Simulate Event                          ${USER_OBSERVATION_AREA_COORDINATES_INPUT}              blur
    Wait Until Page Contains Element        //*[@id="inputCoordinates" and @value="${expect}"]      timeout=30

Check invalid coordinates
    [Arguments]    ${input}
    Sign In ${USER_1.name} Fast
    Go To                                   ${PAGE_USER_1_OBSERVATION_AREA_URL}
    Input Text                              ${USER_OBSERVATION_AREA_COORDINATES_INPUT}              ${input}
    Simulate Event                          ${USER_OBSERVATION_AREA_COORDINATES_INPUT}              blur
    Input validation has error              ${USER_OBSERVATION_AREA_COORDINATES_INPUT}


Check valid radius
    [Arguments]    ${radius}    ${expect}=${radius}
    Sign In ${USER_1.name} Fast
    Go To                                   ${PAGE_USER_1_OBSERVATION_AREA_URL}
    Input Text                              ${USER_OBSERVATION_AREA_RADIUS_INPUT}    ${radius}
    Simulate Event                          ${USER_OBSERVATION_AREA_RADIUS_INPUT}    blur
    Wait Until Keyword Succeeds    5x    200ms    Textfield Value Should Be               ${USER_OBSERVATION_AREA_RADIUS_INPUT}    ${expect}


Check Map circle should reflect observation
    [Arguments]    ${radius}
    Sign In ${USER_1.name} Fast
    Go To                                   ${PAGE_USER_1_OBSERVATION_AREA_URL}
    Fill form                               ${COORDS_NEW_YORK}    ${radius}
    Open Eyes                               SeleniumLibrary  5     template_id=${radius}
    Scroll To Element                       ${USER_OBSERVATION_AREA_MAP}
    Wait Until Element Is Visible           ${USER_OBSERVATION_AREA_MAP}
    Capture Element                         ${USER_OBSERVATION_AREA_MAP}
    Compare Images


Fill form
    [Arguments]    ${coordinates}    ${radius}
    Input Text                              ${USER_OBSERVATION_AREA_RADIUS_INPUT}           ${radius}
    Simulate Event                          ${USER_OBSERVATION_AREA_RADIUS_INPUT}           blur
    Input Text                              ${USER_OBSERVATION_AREA_COORDINATES_INPUT}      ${coordinates.lat} ${coordinates.lon}
    Simulate Event                          ${USER_OBSERVATION_AREA_COORDINATES_INPUT}      blur
    Wait Until Page Contains Element        //*[@id="mapid" and @data-map-loaded="true"]    timeout=30

#
