*** Settings ***
Resource        ../functions/FunctionsGlobal.robot
Resource        ../functions/PageMoves.robot
Resource        ../vars/moves.resource
Resource        ../vars/users.resource
Resource        ../vars/geokrety.resource
Force Tags      Moves    GeoKret Details    Move Comment    Missing
Test Setup      Seed

*** Variables ***
${COMMENT_1} =        Some comment !
${COMMENT_2} =        Another one

*** Test Cases ***

Anonymous Cannot Report Missing
    Sign Out Fast
    Go To Url                               ${PAGE_GEOKRETY_1_DETAILS_URL}
    Element Count Should Be                 ${GEOKRET_DETAILS_MOVES_MISSING_BUTTON}     0

Only One Missing Button Can Be Shown
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_GEOKRETY_1_DETAILS_URL}
    Element Count Should Be                 ${GEOKRET_DETAILS_MOVES_MISSING_BUTTON}     1
    Element Count Should Be                 ${GEOKRET_DETAILS_MOVE_2}${GEOKRET_DETAILS_MOVES_MISSING_BUTTON}     1
    Element Count Should Be                 ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_DETAILS_MOVES_MISSING_BUTTON}     0

Missing Button Only For Some Types - Grab
    Post Move                               ${MOVE_2}    # GRAB
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_GEOKRETY_1_DETAILS_URL}
    Element Count Should Be                 ${GEOKRET_DETAILS_MOVE_3}${GEOKRET_DETAILS_MOVES_MISSING_BUTTON}     0
    Element Count Should Be                 ${GEOKRET_DETAILS_MOVE_2}${GEOKRET_DETAILS_MOVES_MISSING_BUTTON}     0
    Element Count Should Be                 ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_DETAILS_MOVES_MISSING_BUTTON}     0

Missing Button Only For Some Types - Comment
    Post Move                               ${MOVE_3}    # COMMENT
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_GEOKRETY_1_DETAILS_URL}
    Element Count Should Be                 ${GEOKRET_DETAILS_MOVE_3}${GEOKRET_DETAILS_MOVES_MISSING_BUTTON}     0
    Element Count Should Be                 ${GEOKRET_DETAILS_MOVE_2}${GEOKRET_DETAILS_MOVES_MISSING_BUTTON}     1
    Element Count Should Be                 ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_DETAILS_MOVES_MISSING_BUTTON}     0

Missing Button Only For Some Types - Seen
    Post Move                               ${MOVE_4}    # SEEN
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_GEOKRETY_1_DETAILS_URL}
    Element Count Should Be                 ${GEOKRET_DETAILS_MOVE_3}${GEOKRET_DETAILS_MOVES_MISSING_BUTTON}     1
    Element Count Should Be                 ${GEOKRET_DETAILS_MOVE_2}${GEOKRET_DETAILS_MOVES_MISSING_BUTTON}     0
    Element Count Should Be                 ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_DETAILS_MOVES_MISSING_BUTTON}     0

Missing Button Only For Some Types - Archive
    Post Move                               ${MOVE_5}    # ARCHIVE
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_GEOKRETY_1_DETAILS_URL}
    Element Count Should Be                 ${GEOKRET_DETAILS_MOVE_3}${GEOKRET_DETAILS_MOVES_MISSING_BUTTON}     0
    Element Count Should Be                 ${GEOKRET_DETAILS_MOVE_2}${GEOKRET_DETAILS_MOVES_MISSING_BUTTON}     0
    Element Count Should Be                 ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_DETAILS_MOVES_MISSING_BUTTON}     0

Missing Button Only For Some Types - Dip
    Post Move                               ${MOVE_6}    # DIP
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_GEOKRETY_1_DETAILS_URL}
    Element Count Should Be                 ${GEOKRET_DETAILS_MOVE_3}${GEOKRET_DETAILS_MOVES_MISSING_BUTTON}     0
    Element Count Should Be                 ${GEOKRET_DETAILS_MOVE_2}${GEOKRET_DETAILS_MOVES_MISSING_BUTTON}     0
    Element Count Should Be                 ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_DETAILS_MOVES_MISSING_BUTTON}     0

Direct Link To Not Last Position Is Forbidden
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_MOVES_COMMENT_MISSING_URL}           moveid=1
    Page Should Contain                     This is forbidden!

Multiple Missing Report Allowed
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_MOVES_COMMENT_MISSING_URL}           moveid=2
    Input Text                              ${GEOKRET_MOVE_COMMENT_COMMENT_INPUT}       ${COMMENT_1}
    Click Button                            ${MODAL_PANEL_SUBMIT_BUTTON}

    Go To Url                               ${PAGE_MOVES_COMMENT_MISSING_URL}           moveid=2
    Input Text                              ${GEOKRET_MOVE_COMMENT_COMMENT_INPUT}       ${COMMENT_2}
    Click Button                            ${MODAL_PANEL_SUBMIT_BUTTON}

    Go To Url                               ${PAGE_GEOKRETY_1_DETAILS_URL}
    Check Move Comment Missing              ${GEOKRET_DETAILS_MOVES_COMMENTS_FIRST_ITEM}    comment=${COMMENT_1}
    Check Move Comment Missing              ${GEOKRET_DETAILS_MOVES_COMMENTS_SECOND_ITEM}   comment=${COMMENT_2}

# TODO: Comment Is Mandatory

# TODO: Missing Can Be Deleted By It's Author

# TODO: Other Users Cannot Delete Missing Reports

# TODO: Recent Logs With Missing Should Be Shown Red On Homepage

# TODO: Recent GeoKrety With Missing Should Be Shown Red On Homepage

# TODO: GeoKrety With Missing Should Be Shown Red On Recently Posted Moves Page

# TODO: GeoKrety With Missing Should Be Shown Red On Owned GeoKrety Moves Page

# TODO: A New Log Position Reset The Missing Status

# TODO: Missing GeoKrety Are Visually Disabled


# Comment Is Shown In Modal
#     Sign In ${USER_1.name} Fast
#     Go To Url                               ${PAGE_GEOKRETY_1_DETAILS_URL}
#     Set Test Variable                       ${commentid}    ${1}
#     Click Element With Param                ${GEOKRET_DETAILS_MOVES_COMMENTS_DELETE_BUTTON}
#     Wait Until Modal                        Do you really want to delete this move comment?
#     Check Move Comment                      ${MODAL_DIALOG}${GEOKRET_DETAILS_MOVES_COMMENTS_ITEMS}
#
# Author Can Delete Comment
#     Sign In ${USER_1.name} Fast
#     Post Move Comment                       comment=${COMMENT_2}
#     Go To Url                               ${PAGE_GEOKRETY_1_DETAILS_URL}
#     Set Test Variable                       ${commentid}    ${1}
#     Click Element With Param                ${GEOKRET_DETAILS_MOVES_COMMENTS_DELETE_BUTTON}
#     Wait Until Modal                        Do you really want to delete this move comment?
#     Click Button                            ${MODAL_DIALOG_SUBMIT_BUTTON}
#     Page Should Not Contain Element         ${GEOKRET_DETAILS_MOVES_COMMENTS_FIRST_ITEM}
#     Set Test Variable                       ${commentid}    ${2}
#     Check Move Comment                      ${GEOKRET_DETAILS_MOVES_COMMENTS_SECOND_ITEM}    comment=${COMMENT_2}


*** Keywords ***

Seed
    Clear DB And Seed 2 users
    Seed 1 geokrety owned by 1
    Sign In ${USER_1.name} Fast
    Post Move                               ${MOVE_1}    # DROP
    Post Move                               ${MOVE_4}    # SEEN
    Sign Out Fast
