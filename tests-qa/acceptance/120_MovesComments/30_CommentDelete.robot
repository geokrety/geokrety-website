*** Settings ***
Resource        ../functions/FunctionsGlobal.robot
Resource        ../functions/PageMoves.robot
Resource        ../vars/moves.resource
Resource        ../vars/users.resource
Resource        ../vars/geokrety.resource
Force Tags      Moves    GeoKret Details    Move Comment
Test Setup     Seed

*** Test Cases ***

Comment Is Shown In Modal
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_GEOKRETY_1_DETAILS_URL}
    Set Test Variable                       ${commentid}    ${1}
    Click Element With Param                ${GEOKRET_DETAILS_MOVES_COMMENTS_DELETE_BUTTON}
    Wait Until Modal                        Do you really want to delete this move comment?
    Check Move Comment                      ${MODAL_DIALOG}${GEOKRET_DETAILS_MOVES_COMMENTS_ITEMS}

Author Can Delete Comment
    Sign In ${USER_1.name} Fast
    Post Move Comment                       comment=${COMMENT_2}
    Go To Url                               ${PAGE_GEOKRETY_1_DETAILS_URL}
    Set Test Variable                       ${commentid}    ${1}
    Click Element With Param                ${GEOKRET_DETAILS_MOVES_COMMENTS_DELETE_BUTTON}
    Wait Until Modal                        Do you really want to delete this move comment?
    Click Button                            ${MODAL_DIALOG_SUBMIT_BUTTON}
    Page Should Not Contain Element         ${GEOKRET_DETAILS_MOVES_COMMENTS_FIRST_ITEM}
    Set Test Variable                       ${commentid}    ${2}
    Check Move Comment                      ${GEOKRET_DETAILS_MOVES_COMMENTS_SECOND_ITEM}    comment=${COMMENT_2}


*** Keywords ***

Seed
    Clear DB And Seed 2 users
    Seed 1 geokrety owned by 2
    Sign In ${USER_1.name} Fast
    Post Move                               ${MOVE_1}
    Post Move Comment                       comment=${COMMENT_1}
    Sign Out Fast
