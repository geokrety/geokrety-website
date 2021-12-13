*** Settings ***
Resource        ../functions/FunctionsGlobal.robot
Resource        ../vars/users.resource
Resource        ../vars/geokrety.resource
Force Tags      Moves    Inventory
Suite Setup     Seed

*** Variables ***
${start}    ${1}
${gk_seed_count}    ${2}

*** Test Cases ***

Inventory Button Not Displayed For Anonymous
    Sign Out Fast
    Go To Url                               ${PAGE_MOVES_URL}
    Page Should Not Contain Button          ${MOVE_TRACKING_CODE_INVENTORY_BUTTON}

Inventory Button Displayed For Connected
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_MOVES_URL}
    Page Should Contain Button              ${MOVE_TRACKING_CODE_INVENTORY_BUTTON}

Click Inventory Button Show Inventory
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_MOVES_URL}
    Open Inventory

Inventory Should Show GeoKrety
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_MOVES_URL}
    Open Inventory
    Element Count Should Be                 ${MOVE_INVENTORY_TABLE}/tr     ${gk_seed_count}
    Check Item At Row Is                    1    &{GEOKRETY_1}
    Check Item At Row Is                    2    &{GEOKRETY_2}

Checkbox Should Increment Button Badge
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_MOVES_URL}
    Open Inventory
    Element Should Contain                  ${MOVE_INVENTORY_SELECT_BUTTON_BADGE}    0

    FOR    ${index}    IN RANGE    ${start}    ${gk_seed_count} + 1
        Log    ${index}
        Select Checkbox                         ${MOVE_INVENTORY_TABLE}//tr[${index}]//input[@type="checkbox"]
        ${value} =                              Convert To String                        ${index}
        Element Should Contain                  ${MOVE_INVENTORY_SELECT_BUTTON_BADGE}    ${value}
    END

#  TODO: there is a limit
Checkbox ALL Should Check all items
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_MOVES_URL}
    Open Inventory
    Select Checkbox                         ${MOVE_INVENTORY_SELECT_ALL_CHECKBOX}
    ${count} =                              Convert To String                        ${gk_seed_count}
    Element Should Contain                  ${MOVE_INVENTORY_SELECT_BUTTON_BADGE}    ${count}
    Checkbox Should Be Selected             ${MOVE_INVENTORY_ALL_ITEMS_CHECKBOX}

Filter By GeoKrety Names
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_MOVES_URL}
    Open Inventory

    Input Text                              ${MOVE_INVENTORY_FILTER_INPUT}          01
    Element Count Should Be                 ${MOVE_INVENTORY_TABLE}/tr[not(contains(@class, "hidden"))]         1
    Element Should Contain                  ${MOVE_INVENTORY_TABLE}/tr[not(contains(@class, "hidden"))][1]      ${GEOKRETY_1.name}

    Input Text                              ${MOVE_INVENTORY_FILTER_INPUT}          02
    Element Count Should Be                 ${MOVE_INVENTORY_TABLE}/tr[not(contains(@class, "hidden"))]         1
    Element Should Contain                  ${MOVE_INVENTORY_TABLE}/tr[not(contains(@class, "hidden"))][1]      ${GEOKRETY_2.name}

Select Button Should Close Inventory And Fill TC
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_MOVES_URL}
    Open Inventory
    Click Choose Button 1
    Wait Until Modal Close
    Textfield Value Should Be               ${MOVE_TRACKING_CODE_INPUT}     ${GEOKRETY_1.tc}

Select Another Button Should Close Inventory And Append TC
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_MOVES_URL}

    Open Inventory
    Click Choose Button 1
    Textfield Value Should Be               ${MOVE_TRACKING_CODE_INPUT}     ${GEOKRETY_1.tc}

    Open Inventory
    Click Choose Button 2
    Textfield Value Should Be               ${MOVE_TRACKING_CODE_INPUT}     ${GEOKRETY_1.tc},${GEOKRETY_2.tc}

Multiple Append Should Be Deduplicated
    [Tags]    TODO
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_MOVES_URL}

    Open Inventory
    Click Choose Button 1
    Textfield Value Should Be               ${MOVE_TRACKING_CODE_INPUT}     ${GEOKRETY_1.tc}

    Open Inventory
    Click Choose Button 2
    Textfield Value Should Be               ${MOVE_TRACKING_CODE_INPUT}     ${GEOKRETY_1.tc},${GEOKRETY_2.tc}

    Open Inventory
    Click Choose Button 1
    Textfield Value Should Be               ${MOVE_TRACKING_CODE_INPUT}     ${GEOKRETY_1.tc},${GEOKRETY_2.tc}

Select Via CheckBoxes
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_MOVES_URL}
    Open Inventory
    Select Checkbox                         ${MOVE_INVENTORY_SELECT_ALL_CHECKBOX}
    Click Button                            ${MOVE_INVENTORY_SELECT_BUTTON}
    Textfield Value Should Be               ${MOVE_TRACKING_CODE_INPUT}     ${GEOKRETY_1.tc},${GEOKRETY_2.tc}

*** Keywords ***

Seed
    Clear DB And Seed 1 users
    Seed ${gk_seed_count} geokrety owned by 1

Click Choose Button ${id}
    Click Button                            ${MOVE_INVENTORY_TABLE}//tr[${id}]//button[@name="btnChooseGK"]

Open Inventory
    Click Button                            ${MOVE_TRACKING_CODE_INVENTORY_BUTTON}
    Wait Until Modal                        Select GeoKrety from inventory

Check Item At Row Is
    [Arguments]    ${row}     &{gk}
    Table Row Should Contain                ${MOVE_INVENTORY_TABLE}    ${row}    Choose
    Table Row Should Contain                ${MOVE_INVENTORY_TABLE}    ${row}    ${gk.name}
    Table Row Should Contain                ${MOVE_INVENTORY_TABLE}    ${row}    ${gk.ref}
    Table Row Should Contain                ${MOVE_INVENTORY_TABLE}    ${row}    ${USER_1.name}




    #
