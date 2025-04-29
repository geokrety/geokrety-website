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

*** Variables ***

${NEW_COMMENT} =    THANKS!!!!

*** Test Cases ***

Anonymous Cannot Edit Moves
    Sign Out Fast
    Go To Url                               ${PAGE_MOVES_EDIT_URL}    moveid=1    redirect=${PAGE_SIGN_IN_URL}
    Flash message shown                     ${UNAUTHORIZED}

Author Can Edit It's Moves
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_MOVES_EDIT_URL}    moveid=1
    Page Should Not Contain                 ${FORBIDEN}

Other Users Cannot Edit Others Moves
    Sign In ${USER_2.name} Fast
    Go To Url                               ${PAGE_MOVES_EDIT_URL}    moveid=1    redirect=${PAGE_GEOKRETY_1_DETAILS_URL}
    Flash message shown                     This action is reserved to the author

Information Should Be Loaded
    Go To Url                               url=https://webbrowsertools.com/timezone/
    Capture Page Screenshot

    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_MOVES_EDIT_URL}    moveid=1
    Textfield Value Should Be               ${MOVE_TRACKING_CODE_INPUT}                     ${GEOKRETY_1.tc}
    Radio Button Should Be Set To           ${MOVE_LOG_TYPE_RADIO_GROUP}                    ${MOVE_1.move_type}
    Textfield Value Should Be               ${MOVE_NEW_LOCATION_WAYPOINT_INPUT}             ${MOVE_1.waypoint}
    Textfield Value Should Be               ${MOVE_NEW_LOCATION_MAP_COORDINATES_INPUT}      ${MOVE_1.lat} ${MOVE_1.lon}
    Textarea Value Should Be                ${MOVE_ADDITIONAL_DATA_COMMENT_INPUT}           ${MOVE_1.comment}
    Input Value Should Be                   ${MOVE_ADDITIONAL_DATA_DATE_TIME_INPUT}         Sat, Aug 22, 2020 6:30 PM
    Input Value Should Be                   ${MOVE_ADDITIONAL_DATA_DATE_HIDDEN_INPUT}       2020-08-22
    Input Value Should Be                   ${MOVE_ADDITIONAL_DATA_HOUR_HIDDEN_INPUT}       18
    Input Value Should Be                   ${MOVE_ADDITIONAL_DATA_MINUTE_HIDDEN_INPUT}     30
    Input Value Should Be                   ${MOVE_ADDITIONAL_DATA_TIMEZONE_HIDDEN_INPUT}   +03:00

Change Tracking Code
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_MOVES_EDIT_URL}    moveid=1
    Input Text                              ${MOVE_TRACKING_CODE_INPUT}                     ${GEOKRETY_2.tc}
    Open Panel                              ${MOVE_ADDITIONAL_DATA_PANEL}
    Click Button                            ${MOVE_ADDITIONAL_DATA_SUBMIT_BUTTON}
    Wait Until Location Is                  ${PAGE_GEOKRETY_2_DETAILS_URL}/page/1\#log1

Change Move Type
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_MOVES_EDIT_URL}    moveid=1
    Open Panel                              ${MOVE_LOG_TYPE_PANEL}
    Click Move Type                         ${MOVE_LOG_TYPE_GRABBED_RADIO}
    Open Panel                              ${MOVE_ADDITIONAL_DATA_PANEL}
    Click Button                            ${MOVE_ADDITIONAL_DATA_SUBMIT_BUTTON}
    Wait Until Location Is                  ${PAGE_GEOKRETY_1_DETAILS_URL}/page/1\#log1
    Page Should Contain Element             ${GEOKRET_DETAILS_MOVES}\[${1}]//div[contains(@class, "move-type")]//img[@data-gk-move-type="${1}"]

Change Location
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_MOVES_EDIT_URL}    moveid=1
    Open Panel                              ${MOVE_NEW_LOCATION_PANEL}
    Input Text                              ${MOVE_NEW_LOCATION_WAYPOINT_INPUT}         GC
    Simulate Event                          ${MOVE_NEW_LOCATION_WAYPOINT_INPUT}         blur
    Panel validation has error              ${MOVE_NEW_LOCATION_PANEL}                  timeout=120
    Input Text                              ${MOVE_NEW_LOCATION_WAYPOINT_INPUT}         ${WPT_GC_2.id}
    Fill Coordinates Only                   ${WPT_GC_2.coords}
    Panel validation has success            ${MOVE_NEW_LOCATION_PANEL}
    Open Panel                              ${MOVE_ADDITIONAL_DATA_PANEL}
    Click Button                            ${MOVE_ADDITIONAL_DATA_SUBMIT_BUTTON}
    Wait Until Location Is                  ${PAGE_GEOKRETY_1_DETAILS_URL}/page/1\#log1
    Page Should Contain Element             ${GEOKRET_DETAILS_MOVES}\[${1}]//div[contains(@class, "move-cache")]//*[contains(text(), "${WPT_GC_2.id}")]

Change Date
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_MOVES_EDIT_URL}    moveid=1
    Open Panel                              ${MOVE_ADDITIONAL_DATA_PANEL}
    Set DateTime                            2020-08-23 07:30:22    +00:00
    Click Button                            ${MOVE_ADDITIONAL_DATA_SUBMIT_BUTTON}
    Wait Until Location Is                  ${PAGE_GEOKRETY_1_DETAILS_URL}/page/1\#log1
    Page Should Contain Element             ${GEOKRET_DETAILS_MOVES}\[${1}]//span[@data-datetime="2020-08-23T07:30:22+00:00"]

Change Comment
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_MOVES_EDIT_URL}    moveid=1
    Open Panel                              ${MOVE_ADDITIONAL_DATA_PANEL}
    Input Inscrybmde                        \#comment                                       ${NEW_COMMENT}
    Click Button                            ${MOVE_ADDITIONAL_DATA_SUBMIT_BUTTON}
    Wait Until Location Is                  ${PAGE_GEOKRETY_1_DETAILS_URL}/page/1\#log1
    Page Should Contain Element             ${GEOKRET_DETAILS_MOVES}\[${1}]//div[contains(@class, "move-comment")]//*[contains(text(), "${NEW_COMMENT}")]

Access Move Edit By Unknown Id
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_MOVES_EDIT_URL}    moveid=123456    redirect=${PAGE_HOME_URL}
    Flash message shown                     This move does not exist.

Access Move Edit By Not An Id
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_MOVES_EDIT_URL}    moveid=FOOBAR    redirect=${PAGE_HOME_URL}
    Flash message shown                     This move does not exist.


*** Keywords ***

Test Setup
    Clear Database And Seed ${2} users
    Seed ${1} geokrety owned by ${1}
    Seed ${1} geokrety owned by ${2}
    Post move    ${MOVE_1}
