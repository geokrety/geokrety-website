*** Settings ***
Library         RequestsLibrary
Resource        ../ressources/Authentication.robot
Variables       ../ressources/vars/users.yml
Variables       ../ressources/vars/geokrety.yml
Suite Setup     Suite Setup

*** Test Cases ***

Fill Tracking Code Should Load GeoKret
    Go To Move
    Input Text                              ${MOVE_TRACKING_CODE_INPUT}                 ${GEOKRETY_1.tc}
    Wait Until Page Contains Element        ${MOVE_TRACKING_CODE_FIRST_RESULT_ITEM}
    Element Should Be Visible               ${MOVE_TRACKING_CODE_RESULT_LIST}
    Element Should Contain                  ${MOVE_TRACKING_CODE_FIRST_RESULT_ITEM}     ${GEOKRETY_1.name} by ${USER_1.name}
    Element Should Contain                  ${MOVE_TRACKING_CODE_FIRST_RESULT_ITEM}     Never moved

Fill Invalid Tracking Code
    Go To Move
    Input Text                              ${MOVE_TRACKING_CODE_INPUT}                 ${TC_INVALID}
    Input validation has error              ${MOVE_TRACKING_CODE_INPUT}
    Input validation has error help         ${MOVE_TRACKING_CODE_INPUT}                 Sorry, but Tracking Code "${TC_INVALID}" was not found in our database.
    Panel validation has error              ${MOVE_TRACKING_CODE_PANEL}

Fill Tracking Code With Reference Number
    [Template]    Fill Tracking Code With Reference Number
    GK
    GK0
    GK001
    GK0001

Fill Tracking Code With TC Starting By GK
    Seed special geokrety with tracking code starting with GK owned by ${1}
    Go To Move
    Input Text                              ${MOVE_TRACKING_CODE_INPUT}                 ${GEOKRETY_STARTING_WITH_GK.tc}
    Wait Until Page Contains Element        ${MOVE_TRACKING_CODE_FIRST_RESULT_ITEM}
    Element Should Be Visible               ${MOVE_TRACKING_CODE_RESULT_LIST}
    Element Should Contain                  ${MOVE_TRACKING_CODE_FIRST_RESULT_ITEM}     ${GEOKRETY_STARTING_WITH_GK.name} by ${USER_1.name}
    Element Should Contain                  ${MOVE_TRACKING_CODE_FIRST_RESULT_ITEM}     Never moved

Fill Multiple Tracking Code Is Not Possible For Anonymous
    Go To Move
    Input Text                              ${MOVE_TRACKING_CODE_INPUT}                 ${GEOKRETY_1.tc},${GEOKRETY_2.tc}
    Wait Until Page Contains Element        ${MOVE_TRACKING_CODE_FIRST_RESULT_ITEM}
    Element Count Should Be                 ${MOVE_TRACKING_CODE_RESULTS_ITEMS}         1
    Wait Until Page Contains Element        ${MOVE_TRACKING_CODE_FIRST_RESULT_ITEM}
    Element Should Contain                  ${MOVE_TRACKING_CODE_FIRST_RESULT_ITEM}     ${GEOKRETY_1.name} by ${USER_1.name}

# TODO There is a limit
Fill Multiple Tracking Code Should Load GeoKrety
    Sign In ${USER_1.name} Fast
    Go To Move
    Input Text                              ${MOVE_TRACKING_CODE_INPUT}                 ${GEOKRETY_1.tc},${GEOKRETY_2.tc}
    Wait Until Page Contains Element        ${MOVE_TRACKING_CODE_FIRST_RESULT_ITEM}
    Element Count Should Be                 ${MOVE_TRACKING_CODE_RESULTS_ITEMS}         2

    Wait Until Page Contains Element        ${MOVE_TRACKING_CODE_FIRST_RESULT_ITEM}
    Element Should Contain                  ${MOVE_TRACKING_CODE_FIRST_RESULT_ITEM}     ${GEOKRETY_1.name} by ${USER_1.name}

    Wait Until Page Contains Element        ${MOVE_TRACKING_CODE_SECOND_RESULT_ITEM}
    Element Should Contain                  ${MOVE_TRACKING_CODE_SECOND_RESULT_ITEM}    ${GEOKRETY_2.name} by ${USER_1.name}

GeoKret Reference Should Be Displayed In Panel Heading
    Sign In ${USER_1.name} Fast
    Go To Move
    Input Text                              ${MOVE_TRACKING_CODE_INPUT}                 ${GEOKRETY_1.tc}
    Click Button                            ${MOVE_TRACKING_CODE_CHECK_BUTTON}
    Wait Until Page Contains Element        ${MOVE_TRACKING_CODE_FIRST_RESULT_ITEM}
    Element Text Should Be                  ${MOVE_TRACKING_CODE_PANEL_HEADER_TEXT}     ${GEOKRETY_1.ref}

    Input Text                              ${MOVE_TRACKING_CODE_INPUT}                 ${GEOKRETY_1.tc},${GEOKRETY_2.tc}
    Click Button                            ${MOVE_TRACKING_CODE_CHECK_BUTTON}
    Wait Until Page Contains Element        ${MOVE_TRACKING_CODE_SECOND_RESULT_ITEM}
    Element Text Should Be                  ${MOVE_TRACKING_CODE_PANEL_HEADER_TEXT}     ${GEOKRETY_1.ref} ${GEOKRETY_2.ref}

    Input Text                              ${MOVE_TRACKING_CODE_INPUT}                 ${TC_INVALID}
    Click Button                            ${MOVE_TRACKING_CODE_CHECK_BUTTON}
    Element Text Should Be                  ${MOVE_TRACKING_CODE_PANEL_HEADER_TEXT}     ${EMPTY}

# TODO Element may be removed from list with click on check mark

*** Keywords ***

Suite Setup
    Clear Database And Seed ${1} users
    Seed ${2} geokrety owned by ${1}
    Sign Out Fast

Fill Tracking Code With Reference Number
    [Arguments]    ${tc}
    Go To Url                               ${PAGE_MOVES_URL}
    Input Text                              ${MOVE_TRACKING_CODE_INPUT}                 ${tc}
    Click Button                            ${MOVE_TRACKING_CODE_CHECK_BUTTON}
    Input validation has error              ${MOVE_TRACKING_CODE_INPUT}
    Input validation has error help         ${MOVE_TRACKING_CODE_INPUT}                 You seems to have used the GeoKret public identifier "${tc}".
    Panel validation has error              ${MOVE_TRACKING_CODE_PANEL}







    #
