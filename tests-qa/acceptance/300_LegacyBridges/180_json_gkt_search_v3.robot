*** Settings ***
Library         RequestsLibrary
Library         JSONLibrary
Resource        ../functions/FunctionsGlobal.robot
Resource        ../functions/PageMoves.robot
Resource        ../vars/moves.resource
Resource        ../vars/geokrety.resource
Suite Setup     Seed
Force Tags      json    legacy    gkt    export_v3

*** Variables ***
&{second_move}     gkid=2    move_type=0    author=1    waypoint=GC0001      lat=43.60000    lon=7.00000     comment=Hello    app=robotframework    app_ver=3.2.1


*** Test Cases ***

No parameters
    Create Session                        geokrety                    ${GK_URL}
    ${resp} =    GET On Session           geokrety                    url=/gkt/search_v3.php
    Should Be Empty    ${resp.content}

Validate Format
    Create Session                        geokrety                    ${GK_URL}
    ${resp} =    GET On Session           geokrety                    url=/gkt/search_v3.php?lat=${MOVE_1.lat}&lon=${MOVE_1.lon}
    ${json} =    Convert String to JSON 	                            ${resp.content}

    ${value}= 	Get Value From Json 	    ${json}     	              $[0].id
    Should Be Equal As Strings            ${value[0]}                 1

    ${value}= 	Get Value From Json 	    ${json}     	              $[1].id
    Should Be Equal As Strings            ${value[0]}                 2

    ${value}= 	Get Value From Json 	    ${json} 	                  $[0].n
    Should Be Equal As Strings            ${value[0]}                 ${GEOKRETY_1.name}

    ${value}= 	Get Value From Json 	    ${json} 	                  $[1].n
    Should Be Equal As Strings            ${value[0]}                 ${GEOKRETY_2.name}

*** Keywords ***

Seed
    Clear DB And Seed 2 users
    Seed 1 geokrety owned by 1
    Seed 1 geokrety owned by 2
    Post Move                             ${MOVE_1}
    Post Move                             ${second_move}
    Sign Out Fast
