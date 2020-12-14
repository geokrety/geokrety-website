*** Settings ***
Library         SeleniumLibrary  timeout=10  implicit_wait=0
Resource        ../functions/FunctionsGlobal.robot
Resource        ../functions/PageMoves.robot
Resource        ../vars/moves.resource
Resource        ../vars/geokrety.resource
Force Tags      Moves    Home
Test Setup     Seed

*** Test Cases ***

Move Should Be Shown In Recent Moves
    Post Move                               ${MOVE_1}
    Go To Url                               ${PAGE_HOME_URL}
    Check Homepage Recent Move              ${1}                           ${MOVE_1}    comment=Hello

Recent Moves First
    Post Move                               ${MOVE_1}
    Post Move                               ${MOVE_2}
    Go To Url                               ${PAGE_HOME_URL}
    Check Homepage Recent Move              ${1}                           ${MOVE_2}    distance=${EMPTY}    comment=Hello
    Check Homepage Recent Move              ${2}                           ${MOVE_1}    comment=Hello

*** Keywords ***

Seed
    Clear DB And Seed 2 users
    Seed 1 geokrety owned by 2
    Sign Out Fast

Check Homepage Recent Move
    [Arguments]    ${row}    ${move}    ${gk}=${GEOKRETY_1}    ${comment}=${move.comment}    ${distance}=0 km    ${author}=username1
    Page Should Contain Element             ${HOME_LATEST_MOVES_TABLE}//tr[${row}]/td[1]//img[@data-gk-move-type=${move.move_type}]
    Table Cell Should Contain               ${HOME_LATEST_MOVES_TABLE}    ${row + 1}    2    ${gk.name}
    Table Cell Should Contain               ${HOME_LATEST_MOVES_TABLE}    ${row + 1}    2    ${gk.ref}
    Table Cell Should Contain               ${HOME_LATEST_MOVES_TABLE}    ${row + 1}    3    ${move.waypoint}
    Table Cell Should Contain               ${HOME_LATEST_MOVES_TABLE}    ${row + 1}    4    ${comment}
    Table Cell Should Contain               ${HOME_LATEST_MOVES_TABLE}    ${row + 1}    5    ${author}
    Table Cell Should Contain               ${HOME_LATEST_MOVES_TABLE}    ${row + 1}    6    ${distance}
