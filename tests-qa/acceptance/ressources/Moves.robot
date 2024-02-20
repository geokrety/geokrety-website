*** Settings ***
Resource        Devel.robot
Resource        CustomActions.robot
Resource        vars/Urls.robot
Resource        Inscrybmde.robot
Variables       vars/geokrety.yml
Variables       vars/moves.yml
Library    String

*** Variables ***

&{CONTENT_TYPE_FORM_URLENCODED}    Content-Type=application/x-www-form-urlencoded

${GEOKRET_DETAILS_MOVES}                        //div[@data-gk-type="move"]
${GEOKRET_DETAILS_MOVE_X}                       //div[@data-gk-type="move" and @data-id="\${id}"]
${GEOKRET_DETAILS_MOVE_1}                       //div[@data-gk-type="move" and @data-id="1"]
${GEOKRET_DETAILS_MOVE_2}                       //div[@data-gk-type="move" and @data-id="2"]
${GEOKRET_DETAILS_MOVE_3}                       //div[@data-gk-type="move" and @data-id="3"]

${GEOKRET_DETAILS_MOVES_EDIT_BUTTONS}               //a[@data-type="move-edit"]
${GEOKRET_DETAILS_MOVES_DELETE_BUTTONS}             //button[@data-type="move-delete"]
${GEOKRET_DETAILS_MOVES_PICTURE_UPLOAD_BUTTONS}     //button[@data-type="move-picture-upload"]
${GEOKRET_DETAILS_MOVES_COMMENT_BUTTONS}            //button[@data-type="move-comment" and @data-move-comment-type="comment"]
${GEOKRET_DETAILS_MOVES_MISSING_BUTTON}             //button[@data-type="move-comment" and @data-move-comment-type="missing"]


*** Keywords ***

Get Move ${id} XPath
    ${xpath} =    Replace Variables    ${GEOKRET_DETAILS_MOVE_X}
    RETURN    ${xpath}

Post Move
    [Arguments]    ${move}
    Create Session    gk        ${GK_URL}
    ${resp}=          POST On Session    gk    url=/devel/db/geokrety/move/seed    data=${move}    headers=${CONTENT_TYPE_FORM_URLENCODED}
    Request Should Be Successful     ${resp}

Post Move Fast
    [Arguments]    &{move}
    ${resp}=          POST
    ...               url=${GK_URL}/devel/db/geokrety/move/seed
    ...               data=${move}
    ...               headers=${CONTENT_TYPE_FORM_URLENCODED}
    Request Should Be Successful     ${resp}

Delete Move Fast
    [Arguments]    ${moveid}
    ${resp}=          DELETE
    ...               url=${GK_URL}/devel/db/moves/${moveid}
    Request Should Be Successful     ${resp}

Delete Move
    [Arguments]    ${gkid}    ${moveid}    &{move}
    Go To GeoKrety ${gkid}
    Scroll Into View                                //div[@data-gk-type="move" and @data-id="${moveid}"]
    Click Button                                    //div[@data-gk-type="move" and @data-id="${moveid}"]${GEOKRET_DETAILS_MOVES_DELETE_BUTTONS}
    Wait Until Modal                                Do you really want to delete this move?
    Check GeoKret Move                              ${MODAL_PANEL}    ${1}    ${move}
    Click Button                                    ${MODAL_DIALOG_SUBMIT_BUTTON}
    Location Should Be GeoKret ${gkid}
    Page Should Not Contain Element                 //div[@data-gk-type="move" and @data-id="${moveid}"]

Click Move Type
    [Arguments]    ${action}
    # let activate retry as sometimes the ToolTip is still over element
    Wait Until Keyword Succeeds    5x    200ms    Click Element    ${action}/parent::label

Fill Coordinates Only
    [Arguments]    ${coords}
    Click Element                           ${MOVE_NEW_LOCATION_MAP_PANEL_HEADER}
    Element Should Be Visible               ${MOVE_NEW_LOCATION_MAP_COORDINATES_INPUT}
    Input Text                              ${MOVE_NEW_LOCATION_MAP_COORDINATES_INPUT}  ${coords}
    Click Button                            ${MOVE_NEW_LOCATION_MAP_COORDINATES_SEARCH_BUTTON}

Fill Coordinates
    [Arguments]    ${wpt}    ${coords}
    Go To Url                               ${PAGE_MOVES_URL}
    Open Panel                              ${MOVE_NEW_LOCATION_PANEL}
    Input Text                              ${MOVE_NEW_LOCATION_WAYPOINT_INPUT}         ${wpt}
    Simulate Event                          ${MOVE_NEW_LOCATION_WAYPOINT_INPUT}         blur
    Panel validation has error              ${MOVE_NEW_LOCATION_PANEL}

    Fill Coordinates Only                   ${coords}

Set DateTime
    [Arguments]    ${datetime}=2020-08-12 07:30:00    ${timezone}=+00:00
    Execute Javascript                      $("#datetimepicker").data("DateTimePicker").date(moment.utc("${datetime}").zone("${timezone}"));
    Simulate Event                          ${MOVE_ADDITIONAL_DATA_DATE_TIME_INPUT}         blur

Check Move
    [Arguments]    ${table}    ${row}    ${move}    ${gk}=${GEOKRETY_1}    ${comment}=${move.comment}    ${distance}=${EMPTY}    ${author}=username 1
    Page Should Contain Element             ${table}//tr[${row}]/td[1]//img[@data-gk-move-type=${move.move_type}]
    Table Cell Should Contain               ${table}    ${row + 1}    2    ${gk.name}
    Table Cell Should Contain               ${table}    ${row + 1}    2    ${gk.ref}
    Table Cell Should Contain               ${table}    ${row + 1}    3    ${move.waypoint}
    Table Cell Should Contain               ${table}    ${row + 1}    4    ${comment}
    Table Cell Should Contain               ${table}    ${row + 1}    5    ${author}

    Run Keyword If      ${move.move_type} in @{REQUIRE_COORDINATES}     Table Cell Should Contain    ${table}    ${row + 1}    6    ${distance} km
    ...                 ELSE                                            Table Cell Should Contain    ${table}    ${row + 1}    6    ${EMPTY}


Check GeoKret Move
    [Arguments]    ${location}    ${row}    ${move}    ${gk}=${GEOKRETY_1}    ${comment}=${move.comment}    ${distance}=${EMPTY}    ${author}=username 1
    Page Should Contain Element             ${location}\[${row}]//div[contains(@class, "move-type")]//img[@data-gk-move-type="${move.move_type}"]
    Page Should Contain Element             ${location}\[${row}]//div[contains(@class, "move-author")]//*[contains(text(), "${author}")]
    Page Should Contain Element             ${location}\[${row}]//div[contains(@class, "move-comment")]//*[contains(text(), "${comment}")]

    Run Keyword If      ${move.move_type} in @{REQUIRE_COORDINATES}     Page Should Contain Element    ${location}\[${row}]//small[contains(@class, "move-distance") and contains(text(), "${distance} km")]
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
    [Arguments]    ${element}    ${author}=username 1    ${comment}=${EMPTY}
    ${_element} =      Replace Variables    ${element}
    Element Should Contain                  ${_element}/div/a[@data-gk-link="user"]    ${author}
    Element Should Contain                  ${_element}/div/span[@class="move-comment"]    ${comment.strip()}
    Element should have class               ${_element}    list-group-item-info


Check Move Comment Missing
    [Arguments]    ${element}    ${author}=username 1    ${comment}=${EMPTY}
    ${_element} =      Replace Variables    ${element}
    Element Should Contain                  ${_element}/div/a[@data-gk-link="user"]    ${author}
    Element Should Contain                  ${_element}/div/span[@class="move-comment"]    ${comment.strip()}
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


Check Search By GeoKrety
    [Arguments]    ${row}    ${gk}
    # TODO check status icon
    Table Cell Should Contain               ${SEARCH_BY_GEOKRETY_TABLE}    ${row + 1}    2    ${gk.name}
    Table Cell Should Contain               ${SEARCH_BY_GEOKRETY_TABLE}    ${row + 1}    2    ${gk.ref}
