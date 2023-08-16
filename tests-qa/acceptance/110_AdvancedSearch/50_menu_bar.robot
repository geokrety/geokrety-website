*** Settings ***
Library         String
Resource        ../ressources/Authentication.robot
Resource        ../ressources/Moves.robot
Variables       ../ressources/vars/users.yml
Suite Setup     Suite Setup


*** Variables ***

${PERCENT} =          %25


*** Test Cases ***

Form Present In Navbar
    Go To Home
    Page Should Contain Element             ${SEARCH_NAVBAR_INPUT}

    Go To Url                               ${PAGE_NEWS_LIST_URL}
    Page Should Contain Element             ${SEARCH_NAVBAR_INPUT}

Default Search Is By GeoKrety
    Go To Home
    Input Text                              ${SEARCH_NAVBAR_INPUT}                      ${GEOKRETY_1.name}
    Click Button                            ${SEARCH_NAVBAR_SUBMIT}
    Location With Param Should Be           ${PAGE_SEARCH_BY_GEOKRETY_URL}              geokret=${GEOKRETY_1.name}

    Wait Until Page Contains Element        ${SEARCH_BY_GEOKRETY_TABLE}/tbody/tr
    Element Count Should Be                 ${SEARCH_BY_GEOKRETY_TABLE}/tbody/tr        1
    Check Search By GeoKrety                ${1}    ${GEOKRETY_1}

Click Input Open Typed Choices
    Go To Home

    Page Should Not Contain Element         ${SEARCH_NAVBAR_INPUT_TYPEAHEAD}
    Element Count Should Be                 ${SEARCH_NAVBAR_INPUT_TYPEAHEAD}/li         0

    Click Element                           ${SEARCH_NAVBAR_INPUT}
    Page Should Contain Element             ${SEARCH_NAVBAR_INPUT_TYPEAHEAD}
    Element Count Should Be                 ${SEARCH_NAVBAR_INPUT_TYPEAHEAD}/li         3

Preselect Type Shown On Button
    [Template]          Preselect Type GeoKrety
    GeoKrety
    Users
    Waypoints

Preselect Type Is Effectivelly Used
    [Template]          Preselect Type Is Effectivelly Used
    GeoKrety            ${GEOKRETY_1.name}                  ${SEARCH_BY_GEOKRETY_TABLE}
    Users               ${USER_1.name}                      ${SEARCH_BY_USER_TABLE}
    Waypoints           ${MOVE_1.waypoint}                  ${SEARCH_BY_WAYPOINT_TABLE}


*** Keywords ***

Suite Setup
    Clear Database And Seed ${2} users
    Seed ${2} geokrety owned by ${2}
    Post Move                               ${MOVE_1}
    Sign Out Fast

Preselect Type GeoKrety
    [Arguments]    ${type}
    Go To Home
    Click Element                           ${SEARCH_NAVBAR_INPUT}

    Element Count Should Be                 ${SEARCH_NAVBAR_INPUT_TYPEAHEAD}/li/a[text() = '${type}: ']    1
    Click Element                           ${SEARCH_NAVBAR_INPUT_TYPEAHEAD}/li/a[text() = '${type}: ']
    Element Should Not Be Visible           ${SEARCH_NAVBAR_INPUT_TYPEAHEAD}

    Element Count Should Be                 ${SEARCH_NAVBAR_SUBMIT}\[text() = '${type}']    1

Preselect Type Is Effectivelly Used
    [Arguments]    ${type}    ${search}    ${responseTable}
    Go To Home
    Click Element                           ${SEARCH_NAVBAR_INPUT}
    Click Element                           ${SEARCH_NAVBAR_INPUT_TYPEAHEAD}/li/a[text() = '${type}: ']
    Element Count Should Be                 ${SEARCH_NAVBAR_SUBMIT}\[text() = '${type}']    1
    Click Element                           ${SEARCH_NAVBAR_INPUT}
    Input Text                              ${SEARCH_NAVBAR_INPUT}                      ${search}
    Click Button                            ${SEARCH_NAVBAR_SUBMIT}
    Wait Until Page Contains Element        ${responseTable}/tbody/tr
    Element Count Should Be                 ${responseTable}/tbody/tr                   1
