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
Variables       ../../ressources/vars/waypoints.yml
Test Setup      Test Setup

*** Variables ***
${FIELD_RESULT_FORMAT} =    %Y-%m-%dT%H:%M:%S+00:00

*** Test Cases ***

Not in the future
    # ${date} =    Get Current Date    increment=3days    result_format=${FIELD_RESULT_FORMAT}
    Sign In ${USER_1.name} Fast
    Go To Url                           ${PAGE_GEOKRETY_EDIT_URL}
    Execute Javascript                  $("#datetimepicker").data("DateTimePicker").date(moment.utc().add(7, 'days').format('L'));
    Simulate Event                      ${GEOKRET_CREATE_BORN_ON_DATETIME_INPUT}         blur
    Input validation has error          ${GEOKRET_CREATE_BORN_ON_DATETIME_INPUT}
    Input validation has error help     ${GEOKRET_CREATE_BORN_ON_DATETIME_INPUT}         The date cannot be in the future.


Ensure date is parsed with right TZ format
    # GH Issue #1015
    # ${date} =    Get Current Date    increment=3days    result_format=${FIELD_RESULT_FORMAT}
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


Edit GeoKret should keep the previous date
    Sign In ${USER_1.name} Fast

    # birthdate is 2020-08-12 00:00:00+00 from test setup
    Go To Url                           ${PAGE_GEOKRETY_EDIT_URL}
    ${datetime} =    Get DateTime
    Should Be Equal   ${datetime}     2020-08-12T00:00:00Z
    Input Text                          ${GEOKRET_CREATE_BORN_ON_DATETIME_INPUT}         12/29/2023 01:00 AM
    Simulate Event                      ${GEOKRET_CREATE_BORN_ON_DATETIME_INPUT}         blur
    Input validation has success        ${GEOKRET_CREATE_BORN_ON_DATETIME_INPUT}
    Click Button                        ${GEOKRET_CREATE_CREATE_BUTTON}
    Location Should Be                  ${PAGE_GEOKRETY_1_DETAILS_URL}
    Element Attribute Should Be         ${GEOKRET_DETAILS_CREATED_ON_DATETIME}/span    data-datetime      2023-12-28T22:00:00+00:00

    Go To Url                           ${PAGE_GEOKRETY_EDIT_URL}
    ${datetime} =    Get DateTime
    Should Be Equal   ${datetime}     2023-12-28T22:00:00Z


New move before GK birth
    Sign In ${USER_1.name} Fast
    # birthdate is 2020-08-12 00:00:00+00 from test setup
    # create date is 2020-08-22 15:30:42+00 from test setup
    Go To Move
    Input Text                              ${MOVE_TRACKING_CODE_INPUT}                 ${GEOKRETY_1.tc}
    Simulate Event                          ${MOVE_TRACKING_CODE_INPUT}                 blur
    Click Button And Check Panel Validation Has Success    ${MOVE_TRACKING_CODE_NEXT_BUTTON}    ${MOVE_TRACKING_CODE_PANEL}    ${MOVE_LOG_TYPE_PANEL}
    Click LogType And Check Panel Validation Has Success    ${MOVE_LOG_TYPE_COMMENT_RADIO}    ${MOVE_LOG_TYPE_PANEL}    ${MOVE_ADDITIONAL_DATA_PANEL}

    Set DateTime                            2020-08-10    00:00:00    +00:00
    # Ensure the date was correctly parsed
    Element Attribute Should Be             ${MOVE_ADDITIONAL_DATA_DATE_HIDDEN_INPUT}        value    ${EMPTY}
    Element Attribute Should Be             ${MOVE_ADDITIONAL_DATA_HOUR_HIDDEN_INPUT}        value    ${EMPTY}
    Element Attribute Should Be             ${MOVE_ADDITIONAL_DATA_MINUTE_HIDDEN_INPUT}      value    ${EMPTY}
    Element Attribute Should Be             ${MOVE_ADDITIONAL_DATA_TIMEZONE_HIDDEN_INPUT}    value    ${EMPTY}

    Input Inscrybmde                        \#comment                                   TEST
    Input validation has error              ${MOVE_ADDITIONAL_DATA_DATE_TIME_INPUT}
    Input validation has error help         ${MOVE_ADDITIONAL_DATA_DATE_TIME_INPUT}          The date cannot be before the GeoKret birthdate.
    Panel validation has error              ${MOVE_ADDITIONAL_DATA_PANEL}


New move between GK birth and GK create
    Sign In ${USER_1.name} Fast

    # birthdate is 2020-08-12 00:00:00+00 from test setup
    # create date is 2020-08-22 15:30:42+00 from test setup
    Go To Move
    Input Text                              ${MOVE_TRACKING_CODE_INPUT}                 ${GEOKRETY_1.tc}
    Simulate Event                          ${MOVE_TRACKING_CODE_INPUT}                 blur
    Click Button And Check Panel Validation Has Success    ${MOVE_TRACKING_CODE_NEXT_BUTTON}    ${MOVE_TRACKING_CODE_PANEL}    ${MOVE_LOG_TYPE_PANEL}
    Click LogType And Check Panel Validation Has Success    ${MOVE_LOG_TYPE_COMMENT_RADIO}    ${MOVE_LOG_TYPE_PANEL}    ${MOVE_ADDITIONAL_DATA_PANEL}

    Set DateTime                            2020-08-20    18:32:00    +02:00
    # Ensure the date was correctly parsed
    Element Attribute Should Be             ${MOVE_ADDITIONAL_DATA_DATE_HIDDEN_INPUT}        value    2020-08-20
    Element Attribute Should Be             ${MOVE_ADDITIONAL_DATA_HOUR_HIDDEN_INPUT}        value    19
    Element Attribute Should Be             ${MOVE_ADDITIONAL_DATA_MINUTE_HIDDEN_INPUT}      value    32
    Element Attribute Should Be             ${MOVE_ADDITIONAL_DATA_TIMEZONE_HIDDEN_INPUT}    value    +03:00    # +03:00 is browser TZ in RobotFramwork context

    Input Inscrybmde                        \#comment                                   TEST
    Panel validation has success            ${MOVE_ADDITIONAL_DATA_PANEL}

    Click Button                            ${MOVE_ADDITIONAL_DATA_SUBMIT_BUTTON}
    Page Should Not Contain                 Moved_on_datetime must be after GeoKret birth
    Wait Until Location Is                  ${PAGE_GEOKRETY_1_DETAILS_URL}/page/1\#log1

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
    Element Attribute Should Be         ${HOME_RECENTLY_CREATED_GK_TABLE}//tr[1]/td[3]/span    data-datetime    ${expected}


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
    ${birthdate} =                        Get Element 	              ${first_gk} 	  birthdate
    XML.Element Text Should Be            ${birthdate}                ${expected}


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

    ${birthdate} =                        Get Element 	              ${first_gk} 	  birthdate
    XML.Element Text Should Be            ${birthdate}                ${expected}


*** Keywords ***

Test Setup
    Clear Database And Seed ${2} users
    Seed ${1} geokrety owned by ${1} with birthdate 2020-08-12T00:00:00

Change Born Date To Now
    [Arguments]    ${result_format}=${FIELD_RESULT_FORMAT}
    Sign In ${USER_1.name} Fast
    Go To Url                           ${PAGE_GEOKRETY_EDIT_URL}
    Execute Javascript                  $("#datetimepicker").data("DateTimePicker").date(moment.utc().local().format('L LT'));
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

Set DateTime
    [Arguments]    ${date}=2020-08-12    ${time}=07:30:00    ${timezone}=+02:00
    Execute Javascript                      $("#datetimepicker").data("DateTimePicker").date(moment("${date}T${time}${timezone}"));
    Simulate Event                          ${MOVE_ADDITIONAL_DATA_DATE_TIME_INPUT}         blur

Get DateTime
    Execute Javascript                      $("#datetimepicker").data("DateTimePicker").date();
    ${result} =   Execute Async JavaScript
    ...           var callback = arguments[arguments.length - 1];
    ...           callback($("#datetimepicker").data("DateTimePicker").date().utc().format());
    RETURN    ${result}

Click Button And Check Panel Validation Has Success
    [Arguments]    ${button}    ${current_panel}    ${next_panel}
    Panel validation has success            ${current_panel}
    Click Button                            ${button}
    Panel Is Collapsed                      ${current_panel}
    Panel Is Open                           ${next_panel}

Click LogType And Check Panel Validation Has Success
    [Arguments]    ${radio_value}    ${current_panel}    ${next_panel}
    Click Move Type    ${radio_value}
    Panel validation has success            ${current_panel}
    # Panel Is Collapsed                      ${current_panel}
    Panel Is Open                           ${next_panel}
