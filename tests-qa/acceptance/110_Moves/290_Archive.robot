*** Settings ***
Library         SeleniumLibrary  timeout=10  implicit_wait=0
Library         RequestsLibrary
Library         JSONLibrary
Resource        ../functions/FunctionsGlobal.robot
Resource        ../functions/PageMoves.robot
Resource        ../vars/moves.resource
Resource        ../vars/users.resource
Resource        ../vars/geokrety.resource
Resource        ../vars/waypoints.resource
Force Tags      Moves    Delete
Test Setup      Seed

*** Variables ***

${DEFAULT_ARCHIVE_COMMENT}     Archiving GeoKret
&{CUSTOM_POST_FORM_DATA}       comment=My Archive Comment
&{MOVE_DROP}       tracking_code=${GEOKRETY_1.tc}    logtype=0       waypoint=${MOVE_1.waypoint}    coordinates=${MOVE_1.lat} ${MOVE_1.lon}    comment=Hello    app=robotframework    app_ver=3.2.1    secid=${USER_1.secid}
&{MOVE_COMMENT}    tracking_code=${GEOKRETY_1.tc}    logtype=5                                                                                 comment=Hello    app=robotframework    app_ver=3.2.1    secid=${USER_1.secid}

*** Test Cases ***

Anonymous Do Not See The Archive Link
    Sign Out Fast
    Go To Url                                       ${PAGE_GEOKRETY_1_DETAILS_URL}
    Page Should Not Contain Element                 ${GEOKRET_DETAILS_ARCHIVE_LINK}

Owner See The Archive Link
    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_GEOKRETY_1_DETAILS_URL}
    Page Should Contain Element                     ${GEOKRET_DETAILS_ARCHIVE_LINK}

Other Users Do Not See The Archive Link
    Sign In ${USER_2.name} Fast
    Go To Url                                       ${PAGE_GEOKRETY_1_DETAILS_URL}
    Page Should Not Contain Element                 ${GEOKRET_DETAILS_ARCHIVE_LINK}

Anonymous Cannot Archive Any GeoKret
    Create Session    gk                            ${GK_URL}
    # Init Session
    ${auth} =         GET On Session     gk         /
    ${resp} =         POST On Session    gk         /en/geokrety/${GEOKRETY_1.ref}/archive     expected_status=401
    ${resp} =         POST On Session    gk         /en/geokrety/${GEOKRETY_2.ref}/archive     expected_status=401
    Delete All Sessions

Owner Can Archive Its Own GeoKrety
    Create Session    gk                            ${GK_URL}
    ${auth} =         GET On Session     gk         /devel/users/${USER_1.name}/login
    ${resp} =         POST On Session    gk         /en/geokrety/${GEOKRETY_1.ref}/archive     expected_status=200
    Delete All Sessions

Owner Cannot Archive Others GeoKrety
    Create Session    gk                            ${GK_URL}
    ${auth} =         GET On Session     gk         /devel/users/${USER_1.name}/login
    ${resp} =         POST On Session    gk         /en/geokrety/${GEOKRETY_2.ref}/archive     expected_status=403
    Delete All Sessions

Custom Message Can Be Provided
    Create Session    gk                            ${GK_URL}
    ${auth} =         GET On Session     gk         /devel/users/${USER_1.name}/login
    ${resp} =         POST On Session    gk         /en/geokrety/${GEOKRETY_1.ref}/archive     expected_status=200

    Delete All Sessions
    Go To Url                                       ${PAGE_GEOKRETY_1_DETAILS_URL}
    Check Last Move Comment                         ${DEFAULT_ARCHIVE_COMMENT}

Default Message If None Provided
    Create Session    gk                            ${GK_URL}
    ${auth} =         GET On Session     gk         /devel/users/${USER_1.name}/login
    ${resp} =         POST On Session    gk         /en/geokrety/${GEOKRETY_1.ref}/archive     data=&{CUSTOM_POST_FORM_DATA}    expected_status=200

    Delete All Sessions
    Go To Url                                       ${PAGE_GEOKRETY_1_DETAILS_URL}
    Check Last Move Comment                         ${CUSTOM_POST_FORM_DATA.comment}

Archive Not Authorized If Last Move Is Already Archive
    Create Session    gk                            ${GK_URL}
    ${auth} =         GET On Session     gk         /devel/users/${USER_1.name}/login
    ${resp} =         POST On Session    gk         /en/geokrety/${GEOKRETY_1.ref}/archive     expected_status=200
    ${resp} =         POST On Session    gk         /en/geokrety/${GEOKRETY_1.ref}/archive     expected_status=400
    Delete All Sessions

If Last Move Is Already Archive Then Archive Button Is Hidden
    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_GEOKRETY_1_DETAILS_URL}
    Page Should Contain Element                     ${GEOKRET_DETAILS_ARCHIVE_LINK}

    Archive GeoKret                                 ${GEOKRETY_1}    ${USER_1}

    Go To Url                                       ${PAGE_GEOKRETY_1_DETAILS_URL}
    Page Should Not Contain Element                 ${GEOKRET_DETAILS_ARCHIVE_LINK}

Anonymous Can Not See The Delete Button
    Archive GeoKret                                 ${GEOKRETY_1}    ${USER_1}
    Sign Out Fast
    Go To Url                                       ${PAGE_GEOKRETY_1_DETAILS_URL}
    Element Count Should Be                         ${GEOKRET_DETAILS_MOVES_DELETE_BUTTONS}     0

Owner Can See The Delete Button
    Archive GeoKret                                 ${GEOKRETY_1}    ${USER_1}
    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_GEOKRETY_1_DETAILS_URL}
    Element Count Should Be                         ${GEOKRET_DETAILS_MOVES_DELETE_BUTTONS}     1

Other Users Can Not See The Delete Button
    Archive GeoKret                                 ${GEOKRETY_1}    ${USER_1}
    Sign In ${USER_2.name} Fast
    Go To Url                                       ${PAGE_GEOKRETY_1_DETAILS_URL}
    Element Count Should Be                         ${GEOKRET_DETAILS_MOVES_DELETE_BUTTONS}     0

Owner Can Delete Archive Status
    Archive GeoKret                                 ${GEOKRETY_1}    ${USER_1}

    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_GEOKRETY_1_DETAILS_URL}
    Scroll Into View                                ${GEOKRET_DETAILS_MOVE_1}
    Click Button                                    ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_DETAILS_MOVES_DELETE_BUTTONS}
    Wait Until Modal                                Do you really want to delete this move?
    Click Button                                    ${MODAL_DIALOG_SUBMIT_BUTTON}
    Page Should Not Contain Element                 ${GEOKRET_DETAILS_MOVE_1}
    Location Should Contain                         ${PAGE_GEOKRETY_1_DETAILS_URL}

Owner Can Delete Archive Status - direct link
    Archive GeoKret                                 ${GEOKRETY_1}    ${USER_1}
    Create Session    gk                            ${GK_URL}
    ${auth} =         GET On Session     gk         /devel/users/${USER_1.name}/login
    ${resp} =         POST On Session    gk         /en/moves/1/delete     expected_status=200
    Delete All Sessions


Archive Cannot Be Deleted On Others GeoKrety
    Archive GeoKret                                 ${GEOKRETY_1}    ${USER_1}
    Create Session    gk                            ${GK_URL}
    ${auth} =         GET On Session     gk         /devel/users/${USER_2.name}/login
    ${resp} =         POST On Session    gk         /en/moves/1/delete     expected_status=403
    Delete All Sessions

Anonymous Cannot Deleted Any Archive
    Archive GeoKret                                 ${GEOKRETY_1}    ${USER_1}
    Create Session    gk                            ${GK_URL}
    ${auth} =         GET On Session     gk         /devel/users/logout
    ${resp} =         POST On Session    gk         /en/moves/1/delete     expected_status=401
    Delete All Sessions

Archived GeoKrety Can Still Be Discovered - wake up
    Archive GeoKret                                 ${GEOKRETY_1}    ${USER_1}
    Post Move Request                               ${MOVE_DROP}     ${USER_1}

Archived GeoKrety Are Visually Disabled
    Archive GeoKret                                 ${GEOKRETY_1}    ${USER_1}
    Go To Url                                       ${PAGE_GEOKRETY_1_DETAILS_URL}
    Element should have class                       ${GEOKRET_DETAILS_DETAILS_PANEL_BODY}       panel-body-default
    Element Should Contain                          ${GEOKRET_DETAILS_DETAILS_PANEL_HEADING}    archived

Awaken GeoKrety Are Not Visually Disabled
    Archive GeoKret                                 ${GEOKRETY_1}    ${USER_1}
    Post Move Request                               ${MOVE_DROP}     ${USER_1}
    Go To Url                                       ${PAGE_GEOKRETY_1_DETAILS_URL}
    Element should not have class                   ${GEOKRET_DETAILS_DETAILS_PANEL_BODY}       panel-body-default
    Element Should Not Contain                      ${GEOKRET_DETAILS_DETAILS_PANEL_HEADING}    archived

Awaken GeoKrety Show The Archive Button Again
    Archive GeoKret                                 ${GEOKRETY_1}    ${USER_1}
    Post Move Request                               ${MOVE_DROP}     ${USER_1}
    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_GEOKRETY_1_DETAILS_URL}
    Page Should Contain Element                     ${GEOKRET_DETAILS_ARCHIVE_LINK}

Archived GeoKrety Do Not Appear Present In Cache
    Post Move Request                               ${MOVE_DROP}     ${USER_1}
    Go To Url                                       ${PAGE_GEOKRETY_1_DETAILS_URL}

    Create Session                        gk        ${GK_URL}
    ${resp} =    GET On Session           gk        url=/gkt/search_v3.php?lat=${MOVE_1.lat}&lon=${MOVE_1.lon}
    ${json} =    Convert String to JSON 	          ${resp.content}
    ${value} =   Get Value From Json                ${json}          $[0]
    Length Should Be                                ${value}         1

    Archive GeoKret                                 ${GEOKRETY_1}    ${USER_1}

    Create Session                        gk        ${GK_URL}
    ${resp} =    GET On Session           gk        url=/gkt/search_v3.php?lat=${MOVE_1.lat}&lon=${MOVE_1.lon}
    ${json} =    Convert String to JSON 	          ${resp.content}
    ${value} =   Get Value From Json                ${json}          $[0]
    Length Should Be                                ${value}         0

Archived GeoKrety Do Not Appear In Select From Inventory
    Post Move                                       ${MOVE_2}
    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_MOVES_FROM_INVENTORY_URL}
    Element Count Should Be                         ${MOVE_INVENTORY_TABLE}/tr     1

    Archive GeoKret                                 ${GEOKRETY_1}    ${USER_1}

    Go To Url                                       ${PAGE_MOVES_FROM_INVENTORY_URL}
    Element Count Should Be                         ${MOVE_INVENTORY_TABLE}/tr     0

Archived Log Can Not Be Edited
    Archive GeoKret                                 ${GEOKRETY_1}    ${USER_1}
    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_GEOKRETY_1_DETAILS_URL}
    Element Count Should Be                         ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_DETAILS_MOVES_EDIT_BUTTONS}    0

    Create Session    gk                            ${GK_URL}
    ${auth} =         GET On Session     gk         /devel/
    ${auth} =         GET On Session     gk         /devel/users/${USER_1.name}/login
    ${resp} =         POST On Session    gk         /en/moves/1/edit    expected_status=403

Deleting Last Archive Set The GeoKret As Awaken
    Post Move                                       ${MOVE_1}
    Archive GeoKret                                 ${GEOKRETY_1}    ${USER_1}

    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_GEOKRETY_1_DETAILS_URL}
    Click Button                                    ${GEOKRET_DETAILS_MOVES_DELETE_BUTTONS}\[1]
    Click Button                                    ${MODAL_DIALOG_SUBMIT_BUTTON}
    Page Should Not Contain Element                 ${GEOKRET_DETAILS_MOVE_2}

    Create Session                        gk        ${GK_URL}
    ${resp} =    GET On Session           gk        url=/gkt/search_v3.php?lat=${MOVE_1.lat}&lon=${MOVE_1.lon}
    ${json} =    Convert String to JSON 	          ${resp.content}
    ${value} =   Get Value From Json                ${json}          $[0]
    Length Should Be                                ${value}         1


# TODO: Validate the popup content

*** Keywords ***

Seed
    Clear DB And Seed 2 users
    Seed 1 geokrety owned by ${USER_1.id}
    Seed 1 geokrety owned by ${USER_2.id}
    Sign Out Fast

Check Last Move Comment
    [Arguments]    ${comment}
    Page Should Contain Element    ${GEOKRET_DETAILS_MOVES}\[1]//div[contains(@class, "move-comment")]//*[contains(text(), "${comment}")]

Archive GeoKret
    [Arguments]     ${geokret}    ${user}    ${expect}=200
    Create Session    gk                            ${GK_URL}
    ${auth} =         GET On Session     gk         /devel/
    ${auth} =         GET On Session     gk         /devel/users/${user.name}/login
    ${resp} =         POST On Session    gk         /en/geokrety/${geokret.ref}/archive     expected_status=${expect}
    Delete All Sessions

Post Move Request
    [Arguments]     ${move}    ${user}    ${expect}=200
    Create Session    gk                            ${GK_URL}
    ${auth} =         GET On Session     gk         /devel/
    ${auth} =         GET On Session     gk         /devel/users/${user.name}/login
    ${resp} =         POST On Session    gk         /en/moves    data=${move}     expected_status=${expect}
    Delete All Sessions
