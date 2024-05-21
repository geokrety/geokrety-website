*** Settings ***
Library         RequestsLibrary
Library         ../../ressources/libraries/DateTimeTZ.py
Library         DateTime
Library         XML
Resource        ../../ressources/Authentication.robot
Resource        ../../ressources/Geokrety.robot
Resource        ../../ressources/Moves.robot
Resource        ../../ressources/vars/pages/Home.robot
Variables       ../../ressources/vars/users.yml
Variables       ../../ressources/vars/geokrety.yml
Test Setup      Test Setup

*** Variables ***
${FIELD_RESULT_FORMAT} =    %Y-%m-%dT%H:%M:%S+00:00

*** Test Cases ***

Not in the future
    ${date} =    Get Current Date    increment=3days    result_format=${FIELD_RESULT_FORMAT}
    Sign In ${USER_1.name} Fast
    Go To Url                           ${PAGE_GEOKRETY_EDIT_URL}
    Execute Javascript                  $("#datetimepicker").data("DateTimePicker").date(moment.utc().add(7, 'days').format('L'));
    Simulate Event                      ${GEOKRET_CREATE_BORN_ON_DATETIME_INPUT}         blur
    Input validation has error          ${GEOKRET_CREATE_BORN_ON_DATETIME_INPUT}
    Input validation has error help     ${GEOKRET_CREATE_BORN_ON_DATETIME_INPUT}         The date cannot be in the future.


Ensure date is parsed with right TZ format
    # GH Issue #1015
    ${date} =    Get Current Date    increment=3days    result_format=${FIELD_RESULT_FORMAT}
    Sign In ${USER_1.name} Fast

    Go To Url                           ${PAGE_GEOKRETY_EDIT_URL}

    Input Text                          ${GEOKRET_CREATE_BORN_ON_DATETIME_INPUT}         29/12/2023 01:00 AM
    Simulate Event                      ${GEOKRET_CREATE_BORN_ON_DATETIME_INPUT}         blur
    Input validation has error          ${GEOKRET_CREATE_BORN_ON_DATETIME_INPUT}
    Input validation has error help     ${GEOKRET_CREATE_BORN_ON_DATETIME_INPUT}         The date cannot be in the future

    Input Text                          ${GEOKRET_CREATE_BORN_ON_DATETIME_INPUT}         12/29/2023 01:00 AM
    Simulate Event                      ${GEOKRET_CREATE_BORN_ON_DATETIME_INPUT}         blur
    Input validation has success        ${GEOKRET_CREATE_BORN_ON_DATETIME_INPUT}

    Go To Url                           ${PAGE_GEOKRETY_EDIT_URL_FR}

    Input Text                          ${GEOKRET_CREATE_BORN_ON_DATETIME_INPUT}         12/29/2023 01:00
    Simulate Event                      ${GEOKRET_CREATE_BORN_ON_DATETIME_INPUT}         blur
    Input validation has error          ${GEOKRET_CREATE_BORN_ON_DATETIME_INPUT}
    Input validation has error help     ${GEOKRET_CREATE_BORN_ON_DATETIME_INPUT}         La date ne peut pas etre dans le futur

    Input Text                          ${GEOKRET_CREATE_BORN_ON_DATETIME_INPUT}         29/12/2023 01:00
    Simulate Event                      ${GEOKRET_CREATE_BORN_ON_DATETIME_INPUT}         blur
    Input validation has success        ${GEOKRET_CREATE_BORN_ON_DATETIME_INPUT}

    Input Text                          ${GEOKRET_CREATE_BORN_ON_DATETIME_INPUT}         29/12/2023 13:00
    Simulate Event                      ${GEOKRET_CREATE_BORN_ON_DATETIME_INPUT}         blur
    Input validation has success        ${GEOKRET_CREATE_BORN_ON_DATETIME_INPUT}


Not sooner than the oldest move
    Post Move Fast          &{MOVE_1}
    ${expected} =     Change Born Date To Now
    Flash message shown                 GeoKret birth date cannot be greater than its oldest move

Shown On GeoKret Page
    ${expected} =     Change Born Date To Now With Validation
    Go To GeoKrety ${1}
    Element Attribute Should Be         ${GEOKRET_DETAILS_CREATED_ON_DATETIME}/span    data-datetime      ${expected}


Shown On User Inventory Page
    ${expected} =     Change Born Date To Now With Validation
    Go To Url                               ${PAGE_USER_INVENTORY_URL}    userid=${USER_1.id}
    Wait Until Page Contains Element        ${USER_INVENTORY_TABLE}/tbody/tr
    Element Count Should Be                 ${USER_INVENTORY_TABLE}/tbody/tr        1
    Element Attribute Should Be             ${USER_INVENTORY_TABLE}//tr[1]/td[4]/span    data-datetime    ${expected}


Shown On User Owned Page
    ${expected} =     Change Born Date To Now With Validation
    Go To Url                               ${PAGE_USER_OWNED_GEOKRETY_URL}    userid=${USER_1.id}
    Wait Until Page Contains Element        ${USER_OWNED_GEOKRETY_TABLE}/tbody/tr
    Element Count Should Be                 ${USER_OWNED_GEOKRETY_TABLE}/tbody/tr        1
    Element Attribute Should Be             ${USER_OWNED_GEOKRETY_TABLE}//tr[1]/td[4]/span    data-datetime    ${expected}


Shown On User Watched Page
    ${expected} =     Change Born Date To Now With Validation

    Sign In ${USER_2.name} Fast
    Go To GeoKrety ${GEOKRETY_1.id}
    Click Element                           ${GEOKRET_DETAILS_WATCH_LINK}
    Wait Until Modal                        Add this GeoKret to your watch list?
    Click Button                            ${MODAL_DIALOG_SUBMIT_BUTTON}

    Go To Url                               ${PAGE_USER_WATCHED_GEOKRETY_URL}    userid=${USER_2.id}
    Wait Until Page Contains Element        ${USER_WATCHED_TABLE}/tbody/tr
    Element Count Should Be                 ${USER_WATCHED_TABLE}/tbody/tr        1
    Element Attribute Should Be             ${USER_WATCHED_TABLE}//tr[1]/td[5]/span    data-datetime    ${expected}


Shown On Home Page
    ${expected} =     Change Born Date To Now With Validation

    Go To Home
    Element Attribute Should Be         ${HOME_RECENTLY_CREATED_GK_TABLE}//tr[1]/td[4]/span    data-datetime    ${expected}


Shown On Inventory Picker
    [Tags]    TODO
    ${expected} =     Change Born Date To Now With Validation
    Sign In ${USER_1.name} Fast
    Go To Move
    Open Inventory
    Element Attribute Should Be         ${MOVE_INVENTORY_TABLE}//tr[1]/td[3]/span    data-datetime    ${expected}


Shown In Export
    ${expected} =     Change Born Date To Now With Validation    result_format=%Y-%m-%d %H:%M:%S

    ${date_2_days_old} = 	              Get Current Date            increment=-2d   result_format=%Y%m%d%H%M%d
    Create Session                        geokrety                    ${GK_URL}
    ${xml} =    GET On Session            geokrety                    url=/export.php?modifiedsince=${date_2_days_old}
    Status Should Be                      200                         ${xml}

    ${root} = 	Parse Xml 	              ${xml.content}
    Should Be Equal 	                  ${root.tag} 	              gkxml

    ${first_gk} =                         Get Element 	              ${root} 	      geokret
    ${datecreated} =                      Get Element 	              ${first_gk} 	  datecreated
    XML.Element Text Should Be            ${datecreated}              ${expected}


Shown In Export2
    ${expected} =     Change Born Date To Now With Validation    result_format=%Y-%m-%d

    Create Session                        geokrety                    ${GK_URL}
    ${xml} =    GET On Session            geokrety                    url=/export2.php?gkid=${GEOKRETY_1.id}
    Status Should Be                      200                         ${xml}

    ${root} =   Parse XML                 ${xml.content}
    Should Be Equal                       ${root.tag}                 gkxml

    ${first_gk} =                         Get Element                 ${root}         geokrety/geokret
    XML.Element Attribute Should Be       ${first_gk}                 date              ${expected}


Shown In Export2Details
    ${expected} =     Change Born Date To Now With Validation    result_format=%Y-%m-%d %H:%M:%S

    Create Session                        geokrety                    ${GK_URL}
    ${xml} =    GET On Session            geokrety                    url=/export2.php?details=1&gkid=${GEOKRETY_1.id}
    Status Should Be                      200                         ${xml}

    ${root} =   Parse XML                 ${xml.content}
    Should Be Equal                       ${root.tag}                 gkxml

    ${first_gk} =                         Get Element                 ${root}         geokrety/geokret

    ${datecreated} =                      Get Element 	              ${first_gk} 	  datecreated
    XML.Element Text Should Be            ${datecreated}              ${expected}


*** Keywords ***

Test Setup
    Clear Database And Seed ${2} users
    Seed ${1} geokrety owned by ${1} with birthdate 2020-08-12T00:00:00

Change Born Date To Now
    [Arguments]    ${result_format}=${FIELD_RESULT_FORMAT}
    Sign In ${USER_1.name} Fast
    Go To Url                           ${PAGE_GEOKRETY_EDIT_URL}
    Execute Javascript                  $("#datetimepicker").data("DateTimePicker").date(moment.utc().format('L'));
    Simulate Event                      ${GEOKRET_CREATE_BORN_ON_DATETIME_INPUT}         blur
    ${date} =     Browser.Get Element Attribute    ${GEOKRET_CREATE_BORN_ON_DATETIME_HIDDEN_INPUT}    value
    Click Button                        ${GEOKRET_CREATE_CREATE_BUTTON}
    RETURN    ${date}


Change Born Date To Now With Validation
    [Arguments]    ${result_format}=${FIELD_RESULT_FORMAT}
    ${date} =    Change Born Date To Now    result_format=${result_format}
    Location Should Be                  ${PAGE_GEOKRETY_1_DETAILS_URL}
    ${expected} =                       datetime_to_utc     ${date}    date_format=%Y-%m-%dT%H:%M:%S%z    result_format=${result_format}

    RETURN    ${expected}
    Sign Out Fast

Open Inventory
    Click Button                            ${MOVE_TRACKING_CODE_INVENTORY_BUTTON}
    Wait Until Modal                        Select GeoKrety from inventory
