*** Settings ***
Library         RequestsLibrary
Library         XML
Library    DateTime
Resource        ../../ressources/Authentication.robot
Resource        ../../ressources/vars/Urls.robot
Variables       ../../ressources/vars/users.yml
Variables       ../../ressources/vars/geokrety.yml
Variables       ../../ressources/vars/moves.yml
Test Setup      Test Setup

*** Variables ***

*** Test Cases ***

Validate XML - structure
    # Check first GK
    ${date_2_days_old} = 	                Get Current Date            increment=-2d   result_format=%Y%m%d%H%M%d

    Create Session                        geokrety                    ${GK_URL}
    ${xml} =    GET On Session            geokrety                    url=/export.php?modifiedsince=${date_2_days_old}
    Status Should Be                      200                         ${xml}

    ${root} = 	Parse XML 	              ${xml.content}
    Should Be Equal 	                  ${root.tag} 	              gkxml

    ${first_gk} =                         Get Element 	              ${root} 	      geokret
    Should Be Equal 	                  ${first_gk.attrib['id']}    1
    XML.Element Attribute Should Be       ${first_gk}                 id              1

    ${name} =                             Get Element 	              ${first_gk} 	  name
    XML.Element Text Should Be            ${name}                     geokrety01
    ${collectible} =                      Get Element 	              ${first_gk} 	  collectible
    XML.Element Text Should Be            ${collectible}              true
    ${parked} =                           Get Element 	              ${first_gk} 	  parked
    XML.Element Text Should Be            ${parked}                   false

    # Park GK 1
    Sign In ${USER_1.name} Fast
    Go To Url                           ${PAGE_GEOKRETY_EDIT_URL}
    Checkbox Should Not Be Selected     ${GEOKRET_CREATE_PARKED_CHECKBOX}
    Select Checkbox                     ${GEOKRET_CREATE_PARKED_CHECKBOX}
    Input Text                          ${GEOKRET_CREATE_NAME_INPUT}        GKNewName
    Click Button                        ${GEOKRET_CREATE_CREATE_BUTTON}
    Location Should Be                  ${PAGE_GEOKRETY_1_DETAILS_URL}


    ${xml} =    GET On Session            geokrety                    url=/export.php?modifiedsince=${date_2_days_old}
    Status Should Be                      200                         ${xml}

    ${root} = 	Parse XML 	              ${xml.content}
    Should Be Equal 	                  ${root.tag} 	              gkxml

    ${first_gk} =                         Get Element 	              ${root} 	      geokret
    Should Be Equal 	                  ${first_gk.attrib['id']}    1
    XML.Element Attribute Should Be       ${first_gk}                 id              1

    ${name} =                             Get Element 	              ${first_gk} 	  name
    XML.Element Text Should Be            ${name}                     GKNewName
    ${collectible} =                      Get Element 	              ${first_gk} 	  collectible
    XML.Element Text Should Be            ${collectible}              false
    ${parked} =                           Get Element 	              ${first_gk} 	  parked
    XML.Element Text Should Be            ${parked}                   true

*** Keywords ***

Test Setup
    Clear Database And Seed ${3} users
    Seed ${1} geokrety owned by ${1}
