*** Settings ***
Library         RequestsLibrary
Library         DateTime
Library         XML
Resource        ../functions/FunctionsGlobal.robot
Resource        ../functions/PageMoves.robot
Resource        ../vars/moves.resource
Resource        ../vars/geokrety.resource
Resource        ../vars/users.resource
Suite Setup     Seed
Force Tags      xml    legacy    export

*** Variables ***
${DEFAULT_PARAMS}     =    timezone=Europe%2FParis&compress=0


*** Test Cases ***

ModifiedSince Is Mandatory
    Go To Url                             ${GK_URL}/export.php
    Page Should Contain                   The 'modifiedsince' parameter is missing or incorrect.

ModifiedSince Has Limit
    Go To Url                             url=${GK_URL}/export.php?modifiedsince=20200311210000
    Page Should Contain                   The requested period exceeds the 10 days limit

ModifiedSince Is Limited To Last 10 Days
    ${date_10_days_old} = 	              Get Current Date 	increment=-9d 23h       result_format=%Y%m%d%H%M%d
    ${date_11_days_old} = 	              Get Current Date 	increment=-11d          result_format=%Y%m%d%H%M%d
    Go To Url                             url=${GK_URL}/export.php?modifiedsince=${date_10_days_old}
    Page Should Not Contain               The requested period exceeds the 10 days limit
    Go To Url                             url=${GK_URL}/export.php?modifiedsince=${date_11_days_old}
    Page Should Contain                   The requested period exceeds the 10 days limit

ModifiedSince Is In The Future
    Go To Url                             url=${GK_URL}/export.php?modifiedsince=22220311210000
    Page Should Contain                   The requested period is
    Page Should Contain                   days in the future.

Validate XML - structure
    ${date_2_days_old} = 	                Get Current Date            increment=-2d   result_format=%Y%m%d%H%M%d
    Create Session                        geokrety                    ${GK_URL}
    ${xml} =    GET On Session            geokrety                    url=/export.php?modifiedsince=${date_2_days_old}
    Status Should Be                      200                         ${xml}

    ${root} = 	Parse XML 	              ${xml.content}
    Should Be Equal 	                    ${root.tag} 	              gkxml

    # GeoKrety
    ${first_gk} =                         Get Element 	              ${root} 	      geokret
    Should Be Equal 	                    ${first_gk.attrib['id']}    1
    XML.Element Attribute Should Be       ${first_gk}                 id              1

    ${name} =                             Get Element 	              ${first_gk} 	  name
    XML.Element Text Should Be            ${name}                     geokrety01

    ${description} =                      Get Element 	              ${first_gk} 	  description
    XML.Element Text Should Be            ${description}              ${EMPTY}

    ${description_html} =                 Get Element 	              ${first_gk} 	  description_html
    XML.Element Text Should Be            ${description_html}         ${EMPTY}

    ${description_markdown} =             Get Element 	              ${first_gk} 	  description_markdown
    XML.Element Text Should Be            ${description_markdown}     ${EMPTY}

    ${owner} =                            Get Element 	              ${first_gk} 	  owner
    XML.Element Text Should Be            ${owner}                    ${USER_1.name}
    XML.Element Attribute Should Be       ${owner}                    id              ${USER_1.id}

    ${datecreated} =                      Get Element Text            ${first_gk} 	  datecreated
    ${datecreated_Iso8601} =              Get Element Text            ${first_gk} 	  datecreated_Iso8601

    ${distancetravelled} =                Get Element 	              ${first_gk} 	  distancetravelled
    XML.Element Text Should Be            ${distancetravelled}        0

    ${state} =                            Get Element 	              ${first_gk} 	  state
    XML.Element Text Should Be            ${state}                    0

    ${missing} =                          Get Element 	              ${first_gk} 	  missing
    XML.Element Text Should Be            ${missing}                  0

    ${position} =                         Get Element 	              ${first_gk} 	  position
    XML.Element Text Should Be            ${position}                 ${EMPTY}
    XML.Element Attribute Should Be       ${position}                 latitude        ${MOVE_1.lat}
    XML.Element Attribute Should Be       ${position}                 longitude       ${MOVE_1.lon}

    ${waypoints} =                        Get Element 	              ${first_gk} 	  waypoints
    ${waypoint} =                         Get Element 	              ${waypoints} 	  waypoint
    XML.Element Text Should Be            ${waypoint}                 ${MOVE_1.waypoint}

    ${type} =                             Get Element 	              ${first_gk} 	  type
    XML.Element Text Should Be            ${type}                     Traditional
    XML.Element Attribute Should Be       ${type}                     id              0

    # Moves
    ${first_move} =                       Get Element 	              ${root} 	      moves
    Should Be Equal 	                    ${first_move.attrib['id']}  1
    XML.Element Attribute Should Be       ${first_move}               id              1

    ${geokret} =                          Get Element 	              ${first_move}  geokret
    XML.Element Attribute Should Be       ${geokret}                  id             ${GEOKRETY_1.id}
    XML.Element Text Should Be            ${geokret}                  ${GEOKRETY_1.name}

    ${position} =                         Get Element 	              ${first_move}   	 position
    XML.Element Text Should Be            ${position}                 ${EMPTY}
    XML.Element Attribute Should Be       ${position}                 latitude        ${MOVE_1.lat}
    XML.Element Attribute Should Be       ${position}                 longitude       ${MOVE_1.lon}

    ${waypoints} =                        Get Element 	              ${first_move} 	   waypoints
    ${waypoint} =                         Get Element 	              ${waypoints} 	     waypoint
    XML.Element Text Should Be            ${waypoint}                 ${MOVE_1.waypoint}

    ${date} =                             Get Element                 ${first_move} 	   date
    XML.Get Element Attribute                                         ${date}            moved
    XML.Get Element Attribute                                         ${date}            logged
    XML.Get Element Attribute                                         ${date}            edited
    ${date_Iso8601} =                     Get Element                 ${first_move} 	   date_Iso8601
    XML.Get Element Attribute                                         ${date_Iso8601}    moved
    XML.Get Element Attribute                                         ${date_Iso8601}    logged
    XML.Get Element Attribute                                         ${date_Iso8601}    edited

    ${user} =                             Get Element 	              ${first_move} 	   user
    XML.Element Text Should Be            ${user}                     ${USER_1.name}
    XML.Element Attribute Should Be       ${user}                     id                 ${USER_1.id}

    ${comment} =                          Get Element 	              ${first_move} 	   comment
    XML.Element Text Should Be            ${comment}                  ${MOVE_1.comment}
    ${comment_html} =                     Get Element 	              ${first_move} 	   comment_html
    XML.Element Text Should Be            ${comment_html}             <p>${MOVE_1.comment}</p>
    ${comment_markdown} =                 Get Element 	              ${first_move} 	   comment_markdown
    XML.Element Text Should Be            ${comment_markdown}         ${MOVE_1.comment}

    ${logtype} =                          Get Element 	              ${first_move} 	   logtype
    XML.Element Text Should Be            ${logtype}                  drop
    XML.Element Attribute Should Be       ${logtype}                  id                 ${MOVE_1.move_type}

Validate XML - count
    Seed 2 geokrety owned by 2
    Post Move                             ${MOVE_2}

    ${date_2_days_old} = 	                Get Current Date            increment=-2d   result_format=%Y%m%d%H%M%d
    Create Session                        geokrety                    ${GK_URL}
    ${xml} =    GET On Session            geokrety                    url=/export.php?modifiedsince=${date_2_days_old}
    Status Should Be                      200                         ${xml}

    ${root} = 	Parse XML 	              ${xml.content}
    Should Be Equal 	                    ${root.tag} 	              gkxml

    ${geokrety_count} =                   XML.Get Element Count       ${root}           geokret
    Should Be Equal As Numbers            ${geokrety_count}           3

    ${moves_count} =                      XML.Get Element Count       ${root}           moves
    Should Be Equal As Numbers            ${moves_count}              2


*** Keywords ***

Seed
    Clear DB And Seed 2 users
    Seed 1 geokrety owned by 1
    Post Move                             ${MOVE_1}
    Sign Out Fast
