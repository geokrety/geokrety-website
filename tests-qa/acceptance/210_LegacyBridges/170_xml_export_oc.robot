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

ModifiedSince Is Mandatory
    Go To Url                             ${GK_URL}/export_oc.php    redirect=${NO_REDIRECT_CHECK}
    Location Should Be                    ${PAGE_LEGACY_API_EXPORT_OC_URL}?${DEFAULT_PARAMS}
    Page Should Contain                   The 'modifiedsince' parameter is missing or incorrect.

ModifiedSince Has Limit
    Go To Url                             url=${GK_URL}/export_oc.php?modifiedsince=20200311210000    redirect=${NO_REDIRECT_CHECK}
    Location Should Be                    ${PAGE_LEGACY_API_EXPORT_OC_URL}?modifiedsince=20200311210000&${DEFAULT_PARAMS}
    Page Should Contain                   The requested period exceeds the 10 days limit

ModifiedSince Is Limited To Last 10 Days
    ${date_10_days_old} = 	              Get Current Date 	increment=-9d 23h       result_format=%Y%m%d%H%M%d
    ${date_11_days_old} = 	              Get Current Date 	increment=-11d          result_format=%Y%m%d%H%M%d
    Go To Url                             url=${GK_URL}/export_oc.php?modifiedsince=${date_10_days_old}    redirect=${NO_REDIRECT_CHECK}
    Location Should Be                    ${PAGE_LEGACY_API_EXPORT_OC_URL}?modifiedsince=${date_10_days_old}&${DEFAULT_PARAMS}
    Page Should Not Contain               The requested period exceeds the 10 days limit
    Go To Url                             url=${GK_URL}/export_oc.php?modifiedsince=${date_11_days_old}    redirect=${NO_REDIRECT_CHECK}
    Location Should Be                    ${PAGE_LEGACY_API_EXPORT_OC_URL}?modifiedsince=${date_11_days_old}&${DEFAULT_PARAMS}
    Page Should Contain                   The requested period exceeds the 10 days limit

ModifiedSince Is In The Future
    Go To Url                             url=${GK_URL}/export_oc.php?modifiedsince=22220311210000    redirect=${NO_REDIRECT_CHECK}
    Location Should Be                    ${PAGE_LEGACY_API_EXPORT_OC_URL}?modifiedsince=22220311210000&${DEFAULT_PARAMS}
    Page Should Contain                   The requested period is
    Page Should Contain                   days in the future.

Validate XML - structure
    ${date_2_days_old} = 	                Get Current Date            increment=-2d   result_format=%Y%m%d%H%M%d
    Create Session                        geokrety                    ${GK_URL}
    ${xml} =    GET On Session            geokrety                    url=/export_oc.php?modifiedsince=${date_2_days_old}
    Status Should Be                      200                         ${xml}

    ${root} = 	Parse XML 	              ${xml.content}
    Should Be Equal 	                    ${root.tag} 	              gkxml

    # GeoKrety
    ${first_gk} =                         Get Element 	              ${root} 	      geokret
    XML.Element Attribute Should Be       ${first_gk}                 id              1

    ${name} =                             Get Element 	              ${first_gk} 	  name
    XML.Element Text Should Be            ${name}                     geokrety01

    ${distancetravelled} =                Get Element 	              ${first_gk} 	  distancetravelled
    XML.Element Text Should Be            ${distancetravelled}        0

    ${state} =                            Get Element 	              ${first_gk} 	  state
    XML.Element Text Should Be            ${state}                    1

    ${position} =                         Get Element 	              ${first_gk} 	  position
    XML.Element Text Should Be            ${position}                 ${EMPTY}
    XML.Element Attribute Should Be       ${position}                 latitude        ${MOVE_1.lat}
    XML.Element Attribute Should Be       ${position}                 longitude       ${MOVE_1.lon}

    ${waypoints} =                        Get Element 	              ${first_gk} 	  waypoints
    ${waypoint} =                         Get Element 	              ${waypoints} 	  waypoint
    XML.Element Text Should Be            ${waypoint}                 ${MOVE_1.waypoint}

Validate XML - count
    Seed 2 geokrety owned by 2

    ${date_2_days_old} = 	                Get Current Date            increment=-2d   result_format=%Y%m%d%H%M%d
    Create Session                        geokrety                    ${GK_URL}
    ${xml} =    GET On Session            geokrety                    url=/export_oc.php?modifiedsince=${date_2_days_old}
    Status Should Be                      200                         ${xml}

    ${root} = 	Parse XML 	              ${xml.content}
    Should Be Equal 	                    ${root.tag} 	              gkxml

    ${geokrety_count} =                   XML.Get Element Count       ${root}           geokret
    Should Be Equal As Numbers            ${geokrety_count}           3


*** Keywords ***

Suite Setup
    Clear Database And Seed ${2} users
    Seed ${1} geokrety owned by ${1}
    Post Move                             ${MOVE_1}
    Sign Out Fast
