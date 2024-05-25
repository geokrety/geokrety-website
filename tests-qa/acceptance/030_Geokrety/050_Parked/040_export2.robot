*** Settings ***
Library         RequestsLibrary
Library         XML
Resource        ../../ressources/Authentication.robot
Resource        ../../ressources/vars/Urls.robot
Variables       ../../ressources/vars/users.yml
Variables       ../../ressources/vars/geokrety.yml
Variables       ../../ressources/vars/moves.yml
Test Setup      Test Setup

*** Variables ***

*** Test Cases ***

Validate XML - structure
    Create Session                        geokrety                    ${GK_URL}

    ${xml} =    GET On Session            geokrety                    url=/export2.php?gkid=${GEOKRETY_1.id}
    Status Should Be                      200                         ${xml}

    ${root} =   Parse Xml                 ${xml.content}
    Should Be Equal                       ${root.tag}                 gkxml

    ${first_gk} =                         Get Element                 ${root}         geokrety/geokret
    ${gkid} =         Convert To String    ${GEOKRETY_1.id}
    XML.Element Attribute Should Be       ${first_gk}                 id              ${gkid}
    XML.Element Attribute Should Be       ${first_gk}                 owner_id        1
    XML.Element Attribute Should Be       ${first_gk}                 ownername       ${USER_1.name}
    XML.Element Text Should Be            ${first_gk}                 ${GEOKRETY_1.name}
    # New fields
    XML.Element Attribute Should Be       ${first_gk}                 collectible     true
    XML.Element Should Not Have Attribute       ${first_gk}           parked


    # Park GK 1
    Sign In ${USER_1.name} Fast
    Go To Url                           ${PAGE_GEOKRETY_EDIT_URL}
    Checkbox Should Not Be Selected     ${GEOKRET_CREATE_PARKED_CHECKBOX}
    Select Checkbox                     ${GEOKRET_CREATE_PARKED_CHECKBOX}
    Input Text                          ${GEOKRET_CREATE_NAME_INPUT}        GKNewName
    Click Button                        ${GEOKRET_CREATE_CREATE_BUTTON}
    Location Should Be                  ${PAGE_GEOKRETY_1_DETAILS_URL}


    ${xml} =    GET On Session            geokrety                    url=/export2.php?gkid=${GEOKRETY_1.id}
    Status Should Be                      200                         ${xml}

    ${root} =   Parse Xml                 ${xml.content}
    Should Be Equal                       ${root.tag}                 gkxml

    ${first_gk} =                         Get Element                 ${root}         geokrety/geokret
    ${gkid} =         Convert To String    ${GEOKRETY_1.id}
    XML.Element Attribute Should Be       ${first_gk}                 id              ${gkid}
    XML.Element Attribute Should Be       ${first_gk}                 owner_id        1
    XML.Element Attribute Should Be       ${first_gk}                 ownername       ${USER_1.name}
    XML.Element Text Should Be            ${first_gk}                 GKNewName
    # New fields
    XML.Element Attribute Should Be       ${first_gk}                 collectible     false
    XML.Element Attribute Should Be       ${first_gk}                 parked          true


    ${xml} =    GET On Session            geokrety                    url=/export2.php?gkid=${GEOKRETY_1.id}&details=1
    Status Should Be                      200                         ${xml}

    ${root} =   Parse Xml                 ${xml.content}
    Should Be Equal                       ${root.tag}                 gkxml

    ${first_gk} =                         Get Element 	              ${root} 	      geokrety/geokret
    Should Be Equal 	                  ${first_gk.attrib['id']}    1
    XML.Element Attribute Should Be       ${first_gk}                 id              1

    ${name} =                             Get Element 	              ${first_gk} 	  name
    XML.Element Text Should Be            ${name}                     GKNewName

    ${collectible} =                      Get Element 	              ${first_gk} 	  collectible
    XML.Element Text Should Be            ${collectible}              false

    ${parked} =                           Get Element 	              ${first_gk} 	  parked
    XML.Element Text Should Be            ${parked}                   true

Search By UserId And Inventory
    Count GeoKrety Element              /export2.php?userid=${USER_1.id}&inventory=1    1

    # Park GK 1
    Sign In ${USER_1.name} Fast
    Go To Url                           ${PAGE_GEOKRETY_EDIT_URL}
    Checkbox Should Not Be Selected     ${GEOKRET_CREATE_PARKED_CHECKBOX}
    Select Checkbox                     ${GEOKRET_CREATE_PARKED_CHECKBOX}
    Input Text                          ${GEOKRET_CREATE_NAME_INPUT}        GKNewName
    Click Button                        ${GEOKRET_CREATE_CREATE_BUTTON}
    Location Should Be                  ${PAGE_GEOKRETY_1_DETAILS_URL}

    Count GeoKrety Element              /export2.php?userid=${USER_1.id}&inventory=1    0

*** Keywords ***

Test Setup
    Clear Database And Seed ${3} users
    Seed ${1} geokrety owned by ${1}


Count GeoKrety Element
    [Arguments]   ${url}    ${compare}
    Create Session                        geokrety                    ${GK_URL}
    ${xml} =    GET On Session            geokrety                    ${url}
    Status Should Be                      200                         ${xml}

    ${root} =   Parse XML                 ${xml.content}
    Should Be Equal                       ${root.tag}                 gkxml

    ${count} =                            XML.Get Element Count       ${root}           geokrety/geokret
    Should Be Equal As Numbers            ${count}                    ${compare}
