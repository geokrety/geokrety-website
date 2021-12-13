*** Settings ***
Resource        ../functions/FunctionsGlobal.robot
Resource        ../functions/PageMoves.robot
Force Tags      Moves    Log Type
Suite Setup     Seed

*** Test Cases ***

Check Buttons Precence
    Go To Url                               ${PAGE_MOVES_URL}
    Open Panel                              ${MOVE_LOG_TYPE_PANEL}
    Page Should Contain Button              ${MOVE_LOG_TYPE_DROPPED_RADIO}
    Page Should Contain Button              ${MOVE_LOG_TYPE_GRABBED_RADIO}
    Page Should Contain Button              ${MOVE_LOG_TYPE_MEET_RADIO}
    Page Should Contain Button              ${MOVE_LOG_TYPE_DIPPED_RADIO}
    Page Should Contain Button              ${MOVE_LOG_TYPE_COMMENT_RADIO}
    Page Should Contain Button              ${MOVE_TRACKING_CODE_NEXT_BUTTON}

No Selection Should Show Error
    Go To Url                               ${PAGE_MOVES_URL}
    Open Panel                              ${MOVE_LOG_TYPE_PANEL}
    Click Button                            ${MOVE_LOG_TYPE_NEXT_BUTTON}
    Open Panel                              ${MOVE_LOG_TYPE_PANEL}
    Panel validation has error              ${MOVE_LOG_TYPE_PANEL}
    Input validation has error help         ${MOVE_LOG_TYPE_DROPPED_RADIO}         This value is required.
    Element Text Should Be                  ${MOVE_LOG_TYPE_PANEL_HEADER_TEXT}      ${EMPTY}

Select Dropped
    Go To Url                               ${PAGE_MOVES_URL}
    Open Panel                              ${MOVE_LOG_TYPE_PANEL}
    Click Move Type                         ${MOVE_LOG_TYPE_DROPPED_RADIO}
    Panel validation has success            ${MOVE_LOG_TYPE_PANEL}
    Element Text Should Be                  ${MOVE_LOG_TYPE_PANEL_HEADER_TEXT}      Dropped
    Element Should Be Visible               ${MOVE_NEW_LOCATION_PANEL}

Select Grabbed
    Go To Url                               ${PAGE_MOVES_URL}
    Open Panel                              ${MOVE_LOG_TYPE_PANEL}
    Click Move Type                         ${MOVE_LOG_TYPE_GRABBED_RADIO}
    Panel validation has success            ${MOVE_LOG_TYPE_PANEL}
    Element Text Should Be                  ${MOVE_LOG_TYPE_PANEL_HEADER_TEXT}      Grabbed
    Element Should Not Be Visible           ${MOVE_NEW_LOCATION_PANEL}
    Click Move Type                         ${MOVE_LOG_TYPE_DROPPED_RADIO}
    Element Should Be Visible               ${MOVE_NEW_LOCATION_PANEL}

Select Meet
    Go To Url                               ${PAGE_MOVES_URL}
    Open Panel                              ${MOVE_LOG_TYPE_PANEL}
    Click Move Type                         ${MOVE_LOG_TYPE_MEET_RADIO}
    Panel validation has success            ${MOVE_LOG_TYPE_PANEL}
    Element Text Should Be                  ${MOVE_LOG_TYPE_PANEL_HEADER_TEXT}      Met
    Element Should Be Visible               ${MOVE_NEW_LOCATION_PANEL}

Select Dipped
    Go To Url                               ${PAGE_MOVES_URL}
    Open Panel                              ${MOVE_LOG_TYPE_PANEL}
    Click Move Type                         ${MOVE_LOG_TYPE_DIPPED_RADIO}
    Panel validation has success            ${MOVE_LOG_TYPE_PANEL}
    Element Text Should Be                  ${MOVE_LOG_TYPE_PANEL_HEADER_TEXT}      Dipped
    Element Should Be Visible               ${MOVE_NEW_LOCATION_PANEL}

Select Comment
    Go To Url                               ${PAGE_MOVES_URL}
    Open Panel                              ${MOVE_LOG_TYPE_PANEL}
    Click Move Type                         ${MOVE_LOG_TYPE_COMMENT_RADIO}
    Panel validation has success            ${MOVE_LOG_TYPE_PANEL}
    Element Text Should Be                  ${MOVE_LOG_TYPE_PANEL_HEADER_TEXT}      Comment
    Element Should Not Be Visible           ${MOVE_NEW_LOCATION_PANEL}
    Click Move Type                         ${MOVE_LOG_TYPE_DROPPED_RADIO}
    Element Should Be Visible               ${MOVE_NEW_LOCATION_PANEL}


*** Keywords ***

Seed
    Clear DB And Seed 1 users
    Seed 1 geokrety owned by 1
    Sign Out Fast
