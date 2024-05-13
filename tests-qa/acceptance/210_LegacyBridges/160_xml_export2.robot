*** Settings ***
Library         RequestsLibrary
Library         DateTime
Library         XML
Resource        ../ressources/Authentication.robot
Resource        ../ressources/Moves.robot
Resource        ../ressources/vars/Urls.robot
Variables       ../ressources/vars/users.yml
Variables       ../ressources/vars/geokrety.yml
Variables       ../ressources/vars/moves.yml
Suite Setup     Suite Setup

*** Variables ***

${DEFAULT_PARAMS}    timezone=Europe%2FParis&compress=0

*** Test Cases ***

At Least One Parameter Is Required
    Go To Url                             ${GK_URL}/export2.php    redirect=${NO_REDIRECT_CHECK}
    Location Should Be                    ${PAGE_LEGACY_API_EXPORT2_URL}?${DEFAULT_PARAMS}
    Page Should Contain                   The 'modifiedsince' parameter is missing or incorrect.

ModifiedSince Has Limit
    Go To Url                             url=${GK_URL}/export2.php?modifiedsince=20200311210000    redirect=${NO_REDIRECT_CHECK}
    Location Should Be                    ${PAGE_LEGACY_API_EXPORT2_URL}?modifiedsince=20200311210000&${DEFAULT_PARAMS}
    Page Should Contain                   The requested period exceeds the 10 days limit

ModifiedSince Is Limited To Last 10 Days
    Go To Url                               url=https://webbrowsertools.com/timezone/
    Capture Page Screenshot

    ${date_10_days_old} =                 Get Current Date   increment=-9d 23h       result_format=%Y%m%d%H%M%d
    ${date_11_days_old} =                 Get Current Date   increment=-11d          result_format=%Y%m%d%H%M%d
    Go To Url                             url=${GK_URL}/export2.php?modifiedsince=${date_10_days_old}    redirect=${NO_REDIRECT_CHECK}
    Location Should Be                    ${PAGE_LEGACY_API_EXPORT2_URL}?modifiedsince=${date_10_days_old}&${DEFAULT_PARAMS}
    Page Should Not Contain               The requested period exceeds the 10 days limit
    Go To Url                             url=${GK_URL}/export2.php?modifiedsince=${date_11_days_old}    redirect=${NO_REDIRECT_CHECK}
    Location Should Be                    ${PAGE_LEGACY_API_EXPORT2_URL}?modifiedsince=${date_11_days_old}&${DEFAULT_PARAMS}
    Page Should Contain                   The requested period exceeds the 10 days limit

ModifiedSince Is In The Future
    Go To Url                             url=${GK_URL}/export2.php?modifiedsince=22220311210000    redirect=${NO_REDIRECT_CHECK}
    Location Should Be                    ${PAGE_LEGACY_API_EXPORT2_URL}?modifiedsince=22220311210000&${DEFAULT_PARAMS}
    Page Should Contain                   The requested period is
    Page Should Contain                   days in the future.

Search By UserId
    Count GeoKrety Element                /export2.php?userid=${USER_1.id}    3
    Count GeoKrety Element                /export2.php?userid=${USER_2.id}    1
    Count GeoKrety Element                /export2.php?userid=${USER_3.id}    2

Search By UserId And Inventory
    Count GeoKrety Element                /export2.php?userid=${USER_1.id}&inventory=1    2
    Count GeoKrety Element                /export2.php?userid=${USER_2.id}&inventory=1    1
    Count GeoKrety Element                /export2.php?userid=${USER_3.id}&inventory=1    2

Search By UserId + Invalid SecId
    Go To Url                             url=${GK_URL}/export2.php?userid=${USER_1.id}&secid=invalidxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx1    redirect=${NO_REDIRECT_CHECK}
    Location Should Be                    ${PAGE_LEGACY_API_EXPORT2_URL}?${DEFAULT_PARAMS}&userid=${USER_1.id}&secid=invalidxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx1
    Page Should Contain                   This "secid" does not exist

Search By UserId + SecId
    Count GeoKrety Element                /export2.php?userid=${USER_1.id}&secid=${USER_1.secid}    3
    Count GeoKrety Element                /export2.php?userid=${USER_2.id}&secid=${USER_2.secid}    1
    Count GeoKrety Element                /export2.php?userid=${USER_3.id}&secid=${USER_3.secid}    2

    @{gk_list} =                          Create List    ${GEOKRETY_1}    ${GEOKRETY_2}    ${GEOKRETY_3}
    Check Tracking Code                   /export2.php?userid=${USER_1.id}&secid=${USER_1.secid}    @{gk_list}

    @{gk_list} =                          Create List    ${GEOKRETY_4}
    Check Tracking Code                   /export2.php?userid=${USER_2.id}&secid=${USER_2.secid}    @{gk_list}

    @{gk_list} =                          Create List    ${GEOKRETY_5}    ${GEOKRETY_6}
    Check Tracking Code                   /export2.php?userid=${USER_3.id}&secid=${USER_3.secid}    @{gk_list}

Search By UserId And Inventory + SecId
    Count GeoKrety Element                /export2.php?userid=${USER_1.id}&inventory=1&secid=${USER_1.secid}    2
    Count GeoKrety Element                /export2.php?userid=${USER_2.id}&inventory=1&secid=${USER_2.secid}    1
    Count GeoKrety Element                /export2.php?userid=${USER_3.id}&inventory=1&secid=${USER_3.secid}    2

    @{gk_list} =                          Create List    ${GEOKRETY_2}    ${GEOKRETY_3}
    Check Tracking Code                   /export2.php?userid=${USER_1.id}&inventory=1&secid=${USER_1.secid}    @{gk_list}

    @{gk_list} =                          Create List    ${GEOKRETY_4}
    Check Tracking Code                   /export2.php?userid=${USER_2.id}&inventory=1&secid=${USER_2.secid}    @{gk_list}

    @{gk_list} =                          Create List    ${GEOKRETY_5}    ${GEOKRETY_6}
    Check Tracking Code                   /export2.php?userid=${USER_3.id}&inventory=1&secid=${USER_3.secid}    @{gk_list}

Search By GKid
    Count GeoKrety Element                /export2.php?gkid=${GEOKRETY_1.id}    1
    Count GeoKrety Element                /export2.php?gkid=${GEOKRETY_2.id}    1
    Count GeoKrety Element                /export2.php?gkid=${GEOKRETY_3.id}    1

Search By Tracking Code
    Count GeoKrety Element                /api/v1/export2?tracking_code=${GEOKRETY_1.tc}    1
    Count GeoKrety Element                /api/v1/export2?tracking_code=${GEOKRETY_2.tc}    1
    Count GeoKrety Element                /api/v1/export2?tracking_code=${GEOKRETY_3.tc}    1

    Count GeoKrety Element                /export2.php?tracking_code=${GEOKRETY_1.tc}    1
    Count GeoKrety Element                /export2.php?tracking_code=${GEOKRETY_2.tc}    1
    Count GeoKrety Element                /export2.php?tracking_code=${GEOKRETY_3.tc}    1

Search By Waypoint
    Count GeoKrety Element                /export2.php?wpt=${MOVE_1.waypoint}   1
    Count GeoKrety Element                /export2.php?wpt=${MOVE_4.waypoint}   0
    Count GeoKrety Element                /export2.php?wpt=${MOVE_6.waypoint}   0

    # Ensure dipped GeoKrety are not present (https://github.com/cgeo/cgeo/issues/15263)
    Post Move                             ${MOVE_6}
    Count GeoKrety Element                /export2.php?wpt=${MOVE_1.waypoint}   0
    Count GeoKrety Element                /export2.php?wpt=${MOVE_4.waypoint}   0
    Count GeoKrety Element                /export2.php?wpt=${MOVE_6.waypoint}   0

    # Reset database
    [Teardown]    Suite Setup

Search By Coordinates
    Count GeoKrety Element                /export2.php?lonSW=6.9&latSW=43.5&lonNE=7.1&latNE=43.7    1
    Count GeoKrety Element                /export2.php?lonSW=8&latSW=43.5&lonNE=8.1&latNE=43.7      0

Validate XML - structure
    Create Session                        geokrety                    ${GK_URL}
    ${xml} =    GET On Session            geokrety                    url=/export2.php?gkid=${GEOKRETY_1.id}
    Status Should Be                      200                         ${xml}

    ${root} =   Parse XML                 ${xml.content}
    Should Be Equal                       ${root.tag}                 gkxml

    # GeoKrety
    ${first_gk} =                         Get Element                 ${root}         geokrety/geokret
    ${gkid} =         Convert To String    ${GEOKRETY_1.id}
    ${gk_type} =      Convert To String    ${GEOKRETY_1.type}
    ${move_type} =    Convert To String    ${MOVE_1.move_type}
    XML.Element Attribute Should Be       ${first_gk}                 id              ${gkid}
    XML.Element Attribute Should Be       ${first_gk}                 type            ${gk_type}
    XML.Element Attribute Should Be       ${first_gk}                 owner_id        1
    XML.Element Attribute Should Be       ${first_gk}                 ownername       ${USER_1.name}
    XML.Element Attribute Should Be       ${first_gk}                 dist            0
    XML.Get Element Attribute             ${first_gk}                 date
    XML.Element Attribute Should Be       ${first_gk}                 lat             ${MOVE_1.lat}
    XML.Element Attribute Should Be       ${first_gk}                 lon             ${MOVE_1.lon}
    XML.Element Attribute Should Be       ${first_gk}                 waypoint        ${MOVE_1.waypoint}
    XML.Element Attribute Should Be       ${first_gk}                 state           ${move_type}
    XML.Element Attribute Should Be       ${first_gk}                 last_pos_id     1
    XML.Element Attribute Should Be       ${first_gk}                 last_log_id     1
    XML.Element Attribute Should Be       ${first_gk}                 places          1
    XML.Element Text Should Be            ${first_gk}                 ${GEOKRETY_1.name}

# TODO check retrieve on invalid accounts
*** Keywords ***

Suite Setup
    Clear Database And Seed ${3} users
    Seed ${3} geokrety owned by ${1}
    Seed ${1} geokrety owned by ${2}
    Seed ${2} geokrety owned by ${3}
    Post Move                             ${MOVE_1}
    Sign Out Fast


Count GeoKrety Element
    [Arguments]   ${url}    ${compare}
    Create Session                        geokrety                    ${GK_URL}
    ${xml} =    GET On Session            geokrety                    ${url}
    Status Should Be                      200                         ${xml}

    ${root} =   Parse XML                 ${xml.content}
    Should Be Equal                       ${root.tag}                 gkxml

    ${count} =                            XML.Get Element Count       ${root}           geokrety/geokret
    Should Be Equal As Numbers            ${count}                    ${compare}

Check Tracking Code
    [Arguments]    ${url}    @{geokrety}
    Create Session                        geokrety                    ${GK_URL}
    ${xml} =    GET On Session            geokrety                    url=${url}

    ${root} =   Parse XML                 ${xml.content}
    Should Be Equal                       ${root.tag}                 gkxml

    FOR    ${geokret}    IN    @{geokrety}
        ${gk} =                           Get Element                 ${root}         geokrety/geokret[@id="${geokret.id}"]
        ${gkid} =    Convert To String    ${geokret.id}
        XML.Element Attribute Should Be       ${gk}                       id              ${gkid}
        XML.Element Attribute Should Be       ${gk}                       nr              ${geokret.tc}
    END
