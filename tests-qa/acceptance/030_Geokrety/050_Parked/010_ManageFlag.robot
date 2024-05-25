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

Parked Happy Path
    Sign In ${USER_1.name} Fast

    Go To Url                           ${PAGE_GEOKRETY_EDIT_URL}
    Checkbox Should Not Be Selected     ${GEOKRET_CREATE_PARKED_CHECKBOX}
    Select Checkbox                     ${GEOKRET_CREATE_PARKED_CHECKBOX}
    Element Attribute Should Be         ${GEOKRET_CREATE_COLLECTIBLE_CHECKBOX}    disabled    true
    Element Attribute Should Be         ${GEOKRET_CREATE_COLLECTIBLE_CHECKBOX}    checked     None
    Click Button                        ${GEOKRET_CREATE_CREATE_BUTTON}
    Location Should Be                  ${PAGE_GEOKRETY_1_DETAILS_URL}
    Element Should Contain              ${GEOKRET_DETAILS_PARKED}         Parked
    Page Should Not Contain Element     ${GEOKRET_DETAILS_COLLECTIBLE}
    ${date1} =    Browser.Get Element Attribute    ${GEOKRET_DETAILS_PARKED}    title

    Go To Url                           ${PAGE_GEOKRETY_EDIT_URL}
    Checkbox Should Be Selected         ${GEOKRET_CREATE_PARKED_CHECKBOX}
    Unselect Checkbox                   ${GEOKRET_CREATE_PARKED_CHECKBOX}
    Element Attribute Should Be         ${GEOKRET_CREATE_COLLECTIBLE_CHECKBOX}    disabled    None
    Element Attribute Should Be         ${GEOKRET_CREATE_COLLECTIBLE_CHECKBOX}    checked     true
    Click Button                        ${GEOKRET_CREATE_CREATE_BUTTON}
    Location Should Be                  ${PAGE_GEOKRETY_1_DETAILS_URL}
    Page Should Not Contain Element     ${GEOKRET_DETAILS_COLLECTIBLE}
    Page Should Not Contain Element     ${GEOKRET_DETAILS_PARKED}
    Page Should Not Contain Element     ${GEOKRET_DETAILS_COLLECTIBLE}

    Go To Url                           ${PAGE_GEOKRETY_EDIT_URL}
    Checkbox Should Not Be Selected     ${GEOKRET_CREATE_PARKED_CHECKBOX}
    Select Checkbox                     ${GEOKRET_CREATE_PARKED_CHECKBOX}
    Click Button                        ${GEOKRET_CREATE_CREATE_BUTTON}
    Location Should Be                  ${PAGE_GEOKRETY_1_DETAILS_URL}
    Element Should Contain              ${GEOKRET_DETAILS_PARKED}         Parked
    Page Should Not Contain Element     ${GEOKRET_DETAILS_COLLECTIBLE}
    ${date2} =    Browser.Get Element Attribute    ${GEOKRET_DETAILS_PARKED}    title

    Should Not Be Equal As Strings      ${date1}    ${date2}

Changing other value should not change the parked date
    Sign In ${USER_1.name} Fast

    Go To Url                           ${PAGE_GEOKRETY_EDIT_URL}
    Checkbox Should Not Be Selected     ${GEOKRET_CREATE_PARKED_CHECKBOX}
    Input Text                          ${GEOKRET_CREATE_NAME_INPUT}        GKNewName
    Click Button                        ${GEOKRET_CREATE_CREATE_BUTTON}
    Location Should Be                  ${PAGE_GEOKRETY_1_DETAILS_URL}

    Go To Url                           ${PAGE_GEOKRETY_EDIT_URL}
    Checkbox Should Not Be Selected     ${GEOKRET_CREATE_PARKED_CHECKBOX}

    Checkbox Should Not Be Selected     ${GEOKRET_CREATE_PARKED_CHECKBOX}
    Select Checkbox                     ${GEOKRET_CREATE_PARKED_CHECKBOX}
    Click Button                        ${GEOKRET_CREATE_CREATE_BUTTON}

    Go To Url                           ${PAGE_GEOKRETY_EDIT_URL}
    Input Text                          ${GEOKRET_CREATE_NAME_INPUT}        GKNewName2
    Click Button                        ${GEOKRET_CREATE_CREATE_BUTTON}
    Location Should Be                  ${PAGE_GEOKRETY_1_DETAILS_URL}

    Go To Url                           ${PAGE_GEOKRETY_EDIT_URL}
    Checkbox Should Be Selected         ${GEOKRET_CREATE_PARKED_CHECKBOX}


Can be enabled only if owner is the holder
    Sign In ${USER_1.name} Fast

    Post Move Fast    &{MOVE_22}
    Go To Url                           ${PAGE_GEOKRETY_EDIT_URL}
    Page Should Not Contain Element     ${GEOKRET_CREATE_PARKED_CHECKBOX}

    Post Move Fast    &{MOVE_2}
    Go To Url                           ${PAGE_GEOKRETY_EDIT_URL}
    Page Should Contain Element         ${GEOKRET_CREATE_PARKED_CHECKBOX}


*** Keywords ***

Test Setup
    Clear Database And Seed ${2} users
    Seed ${1} geokrety owned by ${1}
