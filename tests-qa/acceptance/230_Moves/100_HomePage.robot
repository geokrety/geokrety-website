*** Settings ***
Resource        ../ressources/Authentication.robot
Resource        ../ressources/Moves.robot
Resource        ../ressources/vars/pages/Home.robot
Test Setup      Test Setup

*** Test Cases ***

Move Should Be Shown In Recent Moves
    Post Move                               ${MOVE_1}
    Go To Home
    Check Homepage Recent Move              ${1}                           ${MOVE_1}    comment=Hello

Recent Moves First
    Post Move                               ${MOVE_1}
    Post Move                               ${MOVE_2}
    Go To Home
    Check Homepage Recent Move              ${1}                           ${MOVE_2}    distance=${EMPTY}    comment=Hello
    Check Homepage Recent Move              ${2}                           ${MOVE_1}    comment=Hello

*** Keywords ***

Test Setup
    Clear Database And Seed ${2} users
    Seed ${1} geokrety owned by ${2}
    Sign Out Fast

Check Homepage Recent Move
    [Arguments]    ${row}    ${move}    ${gk}=${GEOKRETY_1}    ${comment}=${move.comment}    ${distance}=0 km    ${author}=username 1
    Page Should Contain Element             ${HOME_LATEST_MOVES_TABLE}//tr[${row}]/td[1]//img[@data-gk-move-type=${move.move_type}]
    Table Cell Should Contain               ${HOME_LATEST_MOVES_TABLE}    ${row + 1}    2    ${gk.name}
    Table Cell Should Contain               ${HOME_LATEST_MOVES_TABLE}    ${row + 1}    2    ${gk.ref}
    Table Cell Should Contain               ${HOME_LATEST_MOVES_TABLE}    ${row + 1}    3    ${move.waypoint}
    Table Cell Should Contain               ${HOME_LATEST_MOVES_TABLE}    ${row + 1}    4    ${comment}
    Table Cell Should Contain               ${HOME_LATEST_MOVES_TABLE}    ${row + 1}    5    ${author}
    Table Cell Should Contain               ${HOME_LATEST_MOVES_TABLE}    ${row + 1}    6    ${distance}
