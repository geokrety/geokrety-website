*** Settings ***
Resource        FunctionsGlobal.robot

*** Keywords ***

Create GeoKret
    [Arguments]     ${gk}
    Go To Url                         ${PAGE_GEOKRETY_CREATE_URL}
    Page Should Show Creation Form
    Fill Creation Form                ${gk}
    Click Button                      ${GEOKRET_CREATE_CREATE_BUTTON}
    Location Should Contain           ${GK_URL}/en/geokrety/
    Page Should Not Contain           This geokret does not exist


Page Should Show Creation Form
    Wait Until Element Is Visible       ${GEOKRET_CREATE_CREATE_BUTTON}
    Wait Until Page Contains Element    ${GEOKRET_CREATE_NAME_INPUT}
    Wait Until Page Contains Element    ${GEOKRET_CREATE_TYPE_SELECT}
    Wait Until Page Contains Element    ${GEOKRET_CREATE_MISSION_INPUT}


Fill Creation Form
    [Arguments]    ${gk}
    Input Text                      ${GEOKRET_CREATE_NAME_INPUT}        ${gk.name}
    Select From List By Value       ${GEOKRET_CREATE_TYPE_SELECT}       ${gk.type}
    Input Inscrybmde                \#inputMission                      ${gk.mission}
