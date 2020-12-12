*** Settings ***
Library         RequestsLibrary
Resource        FunctionsGlobal.robot

*** Variables ***
&{CONTENT_TYPE_FORM_URLENCODED}    Content-Type=application/x-www-form-urlencoded

*** Keywords ***

Click Move Type
    [Arguments]    ${action}
    # let activate retry as sometimes the ToolTip is still over element
    Wait Until Keyword Succeeds    2x    200ms    Click Element    ${action}/parent::label

Post Move
    [Arguments]    ${move}
    Create Session    gk        ${GK_URL}
    ${resp}=          POST On Session    gk    /devel/db/geokrety/move/seed    data=${move}    headers=${CONTENT_TYPE_FORM_URLENCODED}
    Request Should Be Successful     ${resp}
 200ms    Click Element    ${action}/parent::label

Post Move Comment
    [Arguments]    ${moveid}=1    ${comment}=${EMPTY}
    Go To Url                               ${PAGE_MOVES_COMMENT_URL}    moveid=${moveid}
    Input Text                              ${GEOKRET_MOVE_COMMENT_COMMENT_INPUT}       ${comment}
    Click Button                            ${MODAL_PANEL_SUBMIT_BUTTON}

Set DateTime
    [Arguments]    ${datetime}=2020-08-12 07:30:00    ${timezone}=+00:00
    Execute Javascript                      $("#datetimepicker").data("DateTimePicker").date(moment.utc("${datetime}").zone("${timezone}"));
    Simulate Event                          ${MOVE_ADDITIONAL_DATA_DATE_TIME_INPUT}         blur

Check Move
    [Arguments]    ${table}    ${row}    ${move}    ${gk}=${GEOKRETY_1}    ${comment}=${move.comment}    ${distance}=${EMPTY}    ${author}=username1
    Page Should Contain Element             ${table}//tr[${row}]/td[1]//img[@data-gk-move-type=${move.move_type}]
    Table Cell Should Contain               ${table}    ${row + 1}    2    ${gk.name}
    Table Cell Should Contain               ${table}    ${row + 1}    2    ${gk.ref}
    Table Cell Should Contain               ${table}    ${row + 1}    3    ${move.waypoint}
    Table Cell Should Contain               ${table}    ${row + 1}    4    ${comment}
    Table Cell Should Contain               ${table}    ${row + 1}    5    ${author}

    Run Keyword If      ${move.move_type} in @{REQUIRE_COORDINATES}     Table Cell Should Contain    ${table}    ${row + 1}    6    ${distance} km
    ...                 ELSE                                            Table Cell Should Contain    ${table}    ${row + 1}    6    ${EMPTY}


Check GeoKret Move
    [Arguments]    ${location}    ${row}    ${move}    ${gk}=${GEOKRETY_1}    ${comment}=${move.comment}    ${distance}=${EMPTY}    ${author}=username1
    Page Should Contain Element             ${location}\[${row}]//div[contains(@class, "move-type")]//img[@data-gk-move-type="${move.move_type}"]
    Page Should Contain Element             ${location}\[${row}]//div[contains(@class, "move-author")]//*[contains(text(), "${author}")]
    Page Should Contain Element             ${location}\[${row}]//div[contains(@class, "move-comment")]//*[contains(text(), "${comment}")]

    Run Keyword If      ${move.move_type} in @{REQUIRE_COORDINATES}     Page Should Contain Element    ${location}\[${row}]//small[contains(@class, "move-distance") and contains(text(), "${distance} km")]
    ...                 ELSE                                            Page Should Contain Element    ${location}\[${row}]//small[contains(@class, "move-distance") and normalize-space(text())=""]
    Run Keyword If      ${move.move_type} in @{REQUIRE_COORDINATES}     Page Should Contain Element    ${location}\[${row}]//div[contains(@class, "move-cache")]//*[contains(text(), "${move.waypoint}")]
    ...                 ELSE                                            Page Should Contain Element    ${location}\[${row}]//div[contains(@class, "move-cache") and normalize-space(text())=""]


Check GeoKrety Owned
    [Arguments]    ${row}    ${gk}    ${move}    ${last_mover}=${EMPTY}    ${distance}=${EMPTY}    ${caches}=${EMPTY}
    # TODO check status icon
    # Page Should Contain Element             ${USER_OWNED_GEOKRETY_TABLE}//tr[${row}]/td[1]//img[@data-gk-move-type=${move.move_type}]
    Table Cell Should Contain               ${USER_OWNED_GEOKRETY_TABLE}    ${row + 1}    2    ${gk.name}
    Table Cell Should Contain               ${USER_OWNED_GEOKRETY_TABLE}    ${row + 1}    2    ${gk.ref}
    Table Cell Should Contain               ${USER_OWNED_GEOKRETY_TABLE}    ${row + 1}    3    ${move.waypoint}
    # TODO check last move type icon
    Table Cell Should Contain               ${USER_OWNED_GEOKRETY_TABLE}    ${row + 1}    4    ${last_mover.name}
    Table Cell Should Contain               ${USER_OWNED_GEOKRETY_TABLE}    ${row + 1}    5    ${distance} km
    Table Cell Should Contain               ${USER_OWNED_GEOKRETY_TABLE}    ${row + 1}    6    ${caches}


Check GeoKrety Inventory
    [Arguments]    ${row}    ${gk}    ${owner}    ${move}    ${last_mover}=${EMPTY}    ${distance}=${EMPTY}    ${caches}=${EMPTY}
    # TODO check status icon
    # Page Should Contain Element             ${USER_INVENTORY_TABLE}//tr[${row}]/td[1]//img[@data-gk-move-type=${move.move_type}]
    Table Cell Should Contain               ${USER_INVENTORY_TABLE}    ${row + 1}    2    ${gk.name}
    Table Cell Should Contain               ${USER_INVENTORY_TABLE}    ${row + 1}    2    ${gk.ref}
    Table Cell Should Contain               ${USER_INVENTORY_TABLE}    ${row + 1}    3    ${owner.name}
    Table Cell Should Contain               ${USER_INVENTORY_TABLE}    ${row + 1}    4    ${last_mover.name}
    # TODO check last move type icon
    Table Cell Should Contain               ${USER_INVENTORY_TABLE}    ${row + 1}    5    ${distance} km
    Table Cell Should Contain               ${USER_INVENTORY_TABLE}    ${row + 1}    6    ${caches}


Check Move Comment
    [Arguments]    ${element}    ${author}=username1    ${comment}=${EMPTY}
    ${_element} =      Replace Variables    ${element}
    Wait Until Element Contains             ${_element}/div/a[@data-gk-link="user"]    ${author}
    Wait Until Element Contains             ${_element}/div/span[@class="move-comment"]    ${comment.strip()}
    Element should have class               ${_element}    list-group-item-info


Check Move Comment Missing
    [Arguments]    ${element}    ${author}=username1    ${comment}=${EMPTY}
    ${_element} =      Replace Variables    ${element}
    Wait Until Element Contains             ${_element}/div/a[@data-gk-link="user"]    ${author}
    Wait Until Element Contains             ${_element}/div/span[@class="move-comment"]    ${comment.strip()}
    Element should have class               ${_element}    list-group-item-danger


Check Search By Waypoint
    [Arguments]    ${row}    ${gk}    ${move}    ${distance}=${EMPTY}
    # TODO check status icon
    Table Cell Should Contain               ${SEARCH_BY_WAYPOINT_TABLE}    ${row + 1}    2    ${gk.name}
    Table Cell Should Contain               ${SEARCH_BY_WAYPOINT_TABLE}    ${row + 1}    2    ${gk.ref}
    Table Cell Should Contain               ${SEARCH_BY_WAYPOINT_TABLE}    ${row + 1}    3    ${move.waypoint}
    Table Cell Should Contain               ${SEARCH_BY_WAYPOINT_TABLE}    ${row + 1}    4    ${move.comment}
    # Table Cell Should Contain               ${SEARCH_BY_WAYPOINT_TABLE}    ${row + 1}    5    <datetime>
    Table Cell Should Contain               ${SEARCH_BY_WAYPOINT_TABLE}    ${row + 1}    6    ${distance} km
