*** Settings ***
Library         RequestsLibrary
Resource        ../../ressources/Authentication.robot
Resource        ../../ressources/Geokrety.robot
Variables       ../../ressources/vars/users.yml
Variables       ../../ressources/vars/geokrety.yml
Test Setup      Test Setup

*** Test Cases ***

Name invalid
    [Template]          Name raise error
    ${EMPTY}            This value is required.
    ${SPACE}            This value is required.
    ${SPACE}            This value length is invalid. It should be between 4 and 75 characters long.
    ${SPACE*4}          This value is required.
    ${SPACE*1}A         This value length is invalid. It should be between 4 and 75 characters long.
    ${SPACE*2}A         This value length is invalid. It should be between 4 and 75 characters long.

Name invalid (check after submit)
    [Template]          Name raise error after submit
    ${SPACE*3}A         The Name field needs to be at least '4' characters
    ${SPACE*4}A         The Name field needs to be at least '4' characters

Name valid
    [Template]          GeoKret is created
    ABCD
    ${SPACE}AAAA${SPACE}                                    AAAA
    ${SPACE}AA${SPACE}AA${SPACE}                            AA AA
    ${SPACE}A${SPACE}A${SPACE}A${SPACE}A${SPACE}            A A A A
    ${SPACE}A${SPACE*2}A${SPACE*2}A${SPACE*2}A${SPACE}      A A A A
    🦇 Bat                                                  🦇 Bat
    🦔🐿🐇🐰                                                 🦔🐿🐇🐰


*** Keywords ***

Test Setup
    Clear Database And Seed ${1} users
    Sign In ${USER_1.name} Fast

GeoKret is created
    [Arguments]    ${name}    ${expected}=${name}
    Go To Url                           ${PAGE_GEOKRETY_CREATE_URL}
    Input Text                          ${GEOKRET_CREATE_NAME_INPUT}        ${name}
    Click Button                        ${GEOKRET_CREATE_CREATE_BUTTON}
    Flash message shown                 Your GeoKret has been created.
    Element Text Should Be              ${GEOKRET_DETAILS_NAME}             ${expected}

Name raise error
    [Arguments]    ${name}    ${expected}
    Go To Url                           ${PAGE_GEOKRETY_CREATE_URL}
    Input Text                          ${GEOKRET_CREATE_NAME_INPUT}        ${name}
    Simulate Event                      ${GEOKRET_CREATE_NAME_INPUT}        blur
    Click Button                        ${GEOKRET_CREATE_CREATE_BUTTON}
    Input validation has error          ${GEOKRET_CREATE_NAME_INPUT}
    Input validation has error help     ${GEOKRET_CREATE_NAME_INPUT}        ${expected}

Name raise error after submit
    [Arguments]    ${name}    ${expected}
    Go To Url                           ${PAGE_GEOKRETY_CREATE_URL}
    Input Text                          ${GEOKRET_CREATE_NAME_INPUT}        ${name}
    Simulate Event                      ${GEOKRET_CREATE_NAME_INPUT}        blur
    Click Button                        ${GEOKRET_CREATE_CREATE_BUTTON}
    Page Should Not Contain             Your GeoKret has been created.
    Flash message shown                 ${expected}
