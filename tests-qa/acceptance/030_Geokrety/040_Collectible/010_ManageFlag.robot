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

Changing type to human should change the collectible date
    Sign In ${USER_1.name} Fast

    Go To Url                           ${PAGE_GEOKRETY_1_DETAILS_URL}
    Page Should Not Contain Element     ${GEOKRET_DETAILS_COLLECTIBLE}

    Go To Url                           ${PAGE_GEOKRETY_2_DETAILS_URL}
    Element Should Contain              ${GEOKRET_DETAILS_COLLECTIBLE}    Non-Collectible

    Go To Url                           ${PAGE_GEOKRETY_2_EDIT_URL}
    Select From List By Value           ${GEOKRET_CREATE_TYPE_SELECT}        ${2}
    Click Button                        ${GEOKRET_CREATE_CREATE_BUTTON}
    Element Should Contain              ${GEOKRET_DETAILS_COLLECTIBLE}    Non-Collectible

    Go To Url                           ${PAGE_GEOKRETY_2_EDIT_URL}
    Select From List By Value           ${GEOKRET_CREATE_TYPE_SELECT}        ${6}
    Click Button                        ${GEOKRET_CREATE_CREATE_BUTTON}
    Element Should Contain              ${GEOKRET_DETAILS_COLLECTIBLE}    Non-Collectible

    Go To Url                           ${PAGE_GEOKRETY_2_EDIT_URL}
    Select From List By Value           ${GEOKRET_CREATE_TYPE_SELECT}        ${8}
    Click Button                        ${GEOKRET_CREATE_CREATE_BUTTON}
    Element Should Contain              ${GEOKRET_DETAILS_COLLECTIBLE}    Non-Collectible

    Go To Url                           ${PAGE_GEOKRETY_2_EDIT_URL}
    Select From List By Value           ${GEOKRET_CREATE_TYPE_SELECT}        ${9}
    Click Button                        ${GEOKRET_CREATE_CREATE_BUTTON}
    Page Should Not Contain Element     ${GEOKRET_DETAILS_COLLECTIBLE}


Changing type should fail if not holder
    Sign In ${USER_1.name} Fast
    Post Move Fast    &{MOVE_1}

    Go To Url                           ${PAGE_GEOKRETY_EDIT_URL}
    Select From List By Value           ${GEOKRET_CREATE_TYPE_SELECT}        ${2}
    Click Button                        ${GEOKRET_CREATE_CREATE_BUTTON}
    Flash message shown                 You must hold the Geokrety to change to this type

    Go To Url                           ${PAGE_GEOKRETY_1_DETAILS_URL}
    Page Should Not Contain Element     ${GEOKRET_DETAILS_COLLECTIBLE}



*** Keywords ***

Test Setup
    Clear Database And Seed ${2} users
    Seed ${1} geokrety owned by ${1}
    Seed ${1} geokrety owned by ${1} with type ${2}
