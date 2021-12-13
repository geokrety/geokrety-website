*** Settings ***
Library         RequestsLibrary
Library         JSONLibrary
Library         Collections
Resource        ../functions/FunctionsGlobal.robot
Resource        ../functions/PageMoves.robot
Resource        ../vars/moves.resource
Resource        ../vars/geokrety.resource
Resource        ../vars/users.resource
Suite Setup     Seed
Force Tags      json    legacy    gkt    export_v3

*** Variables ***
&{second_move}     gkid=2    move_type=0    author=1    waypoint=GC0001      lat=43.60000    lon=7.00000     comment=Hello    app=robotframework    app_ver=3.2.1


*** Test Cases ***

Unauthenticated See Empty Results
    Create Session                          geokrety                    ${GK_URL}
    # Init session COOKIE
    ${auth} =     GET On Session            geokrety                    url=/

    ${resp} =     GET On Session            geokrety                    url=/gkt/inventory_v3.php
    Should Not Be Empty    ${resp.content}
    ${json} =     Convert String to JSON                                ${resp.content}

    ${value}=     Get Value From Json       ${json}                     $.loggedin
    Should Be Equal                         ${value[0]}                 ${FALSE}

    ${value}=     Get Value From Json       ${json}                     $.list
    Get Length                              ${value[0]}
    Delete All Sessions


Authenticated User See His Inventory - User 1 - Empty inventory
    Create Session                          geokrety                    ${GK_URL}
    # Init session COOKIE
    ${auth} =     GET On Session            geokrety                    url=/

    GET On Session                          geokrety                    url=/devel/users/${USER_1.name}/login
    ${resp} =     GET On Session            geokrety                    url=/gkt/inventory_v3.php
    Should Not Be Empty    ${resp.content}
    ${json} =     Convert String to JSON                                ${resp.content}

    ${value} =    Get Value From Json       ${json}                     $.loggedin
    Should Be Equal                         ${value[0]}                 ${TRUE}
    Delete All Sessions


Authenticated User See His Inventory - User 2 - Owned 2 Inventory 1
    Create Session                          geokrety                    ${GK_URL}
    # Init session COOKIE
    ${auth} =     GET On Session            geokrety                    url=/

    ${auth} =     GET On Session            geokrety                    url=/devel/users/${USER_2.name}/login
    ${resp} =     GET On Session            geokrety                    url=/gkt/inventory_v3.php
    Should Not Be Empty    ${resp.content}
    ${json} =     Convert String to JSON                                ${resp.content}

    ${value} =    Get Value From Json       ${json}                     $.loggedin
    Should Be Equal                         ${value[0]}                 ${TRUE}

    ${value} =    Get Value From Json       ${json}                     $.list
    Length Should Be                        ${value[0]}                 1
    Delete All Sessions


Authenticated User See His Inventory - User 3 - Owned 4 Inventory 4
    Create Session                          geokrety                    ${GK_URL}
    # Init session COOKIE
    ${auth} =     GET On Session            geokrety                    url=/

    ${auth} =     GET On Session            geokrety                    url=/devel/users/${USER_3.name}/login
    ${resp} =     GET On Session            geokrety                    url=/gkt/inventory_v3.php
    Should Not Be Empty    ${resp.content}
    ${json} =     Convert String to JSON                                ${resp.content}

    ${value} =    Get Value From Json       ${json}                     $.loggedin
    Should Be Equal                         ${value[0]}                 ${TRUE}

    ${value} =    Get Value From Json       ${json}                     $.list
    Length Should Be                        ${value[0]}                 4

    Check Geokret In Json List              ${json}                     ${GEOKRETY_4}
    Check Geokret In Json List              ${json}                     ${GEOKRETY_5}
    Check Geokret In Json List              ${json}                     ${GEOKRETY_6}
    Check Geokret In Json List              ${json}                     ${GEOKRETY_7}

    Delete All Sessions


*** Keywords ***

Seed
    Clear DB And Seed 3 users
    Seed 1 geokrety owned by 1
    Seed 2 geokrety owned by 2
    Seed 4 geokrety owned by 3
    Post Move                               ${MOVE_1}
    Post Move                               ${second_move}
    Sign Out Fast


Check Geokret In Json List
  [Arguments]    ${json}   ${gk}
    ${value} =    Get Value From Json       ${json}                     $.list[?(@.tc=="${gk.tc}")]
    Length Should Be                        ${value}                    1
