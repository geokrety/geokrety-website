*** Settings ***
Library         RequestsLibrary
Resource        ../../ressources/Authentication.robot
Resource        ../../ressources/vars/Urls.robot
Resource        ../../ressources/Moves.robot
Variables       ../../ressources/vars/users.yml
Variables       ../../ressources/vars/geokrety.yml
Variables       ../../ressources/vars/moves.yml
Test Setup      Test Setup

*** Variables ***

*** Test Cases ***

Collectible Happy Path
    Sign In ${USER_1.name} Fast

    Go To Url                           ${PAGE_GEOKRETY_EDIT_URL}
    Checkbox Should Be Selected         ${GEOKRET_CREATE_COLLECTIBLE_CHECKBOX}
    Unselect Checkbox                   ${GEOKRET_CREATE_COLLECTIBLE_CHECKBOX}
    Click Button                        ${GEOKRET_CREATE_CREATE_BUTTON}
    Location Should Be                  ${PAGE_GEOKRETY_1_DETAILS_URL}
    Element Should Contain              ${GEOKRET_DETAILS_COLLECTIBLE}    Non-Collectible
    ${date1} =    Browser.Get Element Attribute    ${GEOKRET_DETAILS_COLLECTIBLE}    title

    Go To Url                           ${PAGE_GEOKRETY_EDIT_URL}
    Checkbox Should Not Be Selected     ${GEOKRET_CREATE_COLLECTIBLE_CHECKBOX}
    Select Checkbox                     ${GEOKRET_CREATE_COLLECTIBLE_CHECKBOX}
    Click Button                        ${GEOKRET_CREATE_CREATE_BUTTON}
    Location Should Be                  ${PAGE_GEOKRETY_1_DETAILS_URL}
    Page Should Not Contain Element     ${GEOKRET_DETAILS_COLLECTIBLE}

    Go To Url                           ${PAGE_GEOKRETY_EDIT_URL}
    Checkbox Should Be Selected         ${GEOKRET_CREATE_COLLECTIBLE_CHECKBOX}
    Unselect Checkbox                   ${GEOKRET_CREATE_COLLECTIBLE_CHECKBOX}
    Click Button                        ${GEOKRET_CREATE_CREATE_BUTTON}
    Location Should Be                  ${PAGE_GEOKRETY_1_DETAILS_URL}
    Element Should Contain              ${GEOKRET_DETAILS_COLLECTIBLE}    Non-Collectible
    ${date2} =    Browser.Get Element Attribute    ${GEOKRET_DETAILS_COLLECTIBLE}    title

    Should Not Be Equal As Strings      ${date1}    ${date2}

Changing other value should not change the collectible date
    Sign In ${USER_1.name} Fast

    Go To Url                           ${PAGE_GEOKRETY_EDIT_URL}
    Unselect Checkbox                   ${GEOKRET_CREATE_COLLECTIBLE_CHECKBOX}
    Click Button                        ${GEOKRET_CREATE_CREATE_BUTTON}
    ${date1} =    Browser.Get Element Attribute    ${GEOKRET_DETAILS_COLLECTIBLE}    title

    Go To Url                           ${PAGE_GEOKRETY_EDIT_URL}
    Input Text                          ${GEOKRET_CREATE_NAME_INPUT}        GKNewName
    Click Button                        ${GEOKRET_CREATE_CREATE_BUTTON}
    ${date2} =    Browser.Get Element Attribute    ${GEOKRET_DETAILS_COLLECTIBLE}    title

    Should Be Equal As Strings          ${date1}    ${date2}

Can be enabled only if it has an holder
    Sign In ${USER_1.name} Fast
    Post Move Fast    &{MOVE_1}

    Go To Url                           ${PAGE_GEOKRETY_EDIT_URL}
    Checkbox Should Be Selected         ${GEOKRET_CREATE_COLLECTIBLE_CHECKBOX}
    Unselect Checkbox                   ${GEOKRET_CREATE_COLLECTIBLE_CHECKBOX}
    Click Button                        ${GEOKRET_CREATE_CREATE_BUTTON}
    Location Should Be                  ${PAGE_GEOKRETY_EDIT_URL}
    Flash message shown                 Cannot set non collectible without an holder

    Post Move Fast    &{MOVE_2}
    Click Button                        ${GEOKRET_CREATE_CREATE_BUTTON}
    Location Should Be                  ${PAGE_GEOKRETY_1_DETAILS_URL}
    Flash message shown                 has been updated


*** Keywords ***

Test Setup
    Clear Database And Seed ${2} users
    Seed ${1} geokrety owned by ${1}
