*** Settings ***
Library         SeleniumLibrary  timeout=10  implicit_wait=0
Library         RobotEyes
Resource        ../functions/FunctionsGlobal.robot
Resource        ../functions/PageMoves.robot
Resource        ../vars/moves.resource
Resource        ../vars/users.resource
Resource        ../vars/geokrety.resource
Force Tags      Moves    GeoKret Details    RobotEyes
Suite Setup     Seed

*** Test Cases ***

Moves By User Should Show Edit Button
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_GEOKRETY_1_DETAILS_URL}
    Element Count Should Be                 ${GEOKRET_DETAILS_MOVES_EDIT_BUTTONS}       3
    Element Count Should Be                 ${GEOKRET_DETAILS_MOVES_EDIT_BUTTONS}       3

Moves By User Should Show Delete Button
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_GEOKRETY_1_DETAILS_URL}
    Element Count Should Be                 ${GEOKRET_DETAILS_MOVES_DELETE_BUTTONS}     3
    Element Count Should Be                 ${GEOKRET_DETAILS_MOVES_DELETE_BUTTONS}     3

Moves By User Should Show Upload Picture Button
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_GEOKRETY_1_DETAILS_URL}
    Element Count Should Be                 ${GEOKRET_DETAILS_MOVES_PICTURE_UPLOAD_BUTTONS}       3
    Element Count Should Be                 ${GEOKRET_DETAILS_MOVES_PICTURE_UPLOAD_BUTTONS}       3

All Moves Should Show Comment Button
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_GEOKRETY_1_DETAILS_URL}
    Element Count Should Be                 ${GEOKRET_DETAILS_MOVES_COMMENT_BUTTONS}    6

Edit Button Are Clickable
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_GEOKRETY_1_DETAILS_URL}
    Click Link                              ${GEOKRET_DETAILS_MOVES_EDIT_BUTTONS}\[1]
    Location With Param Should Be           ${PAGE_MOVES_EDIT_URL}                      moveid=6

Delete Button Are Clickable
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_GEOKRETY_1_DETAILS_URL}
    Click Button                            ${GEOKRET_DETAILS_MOVES_DELETE_BUTTONS}\[1]
    Wait Until Modal                        Do you really want to delete this move?

Comment Button Are Clickable
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_GEOKRETY_1_DETAILS_URL}
    Click Button                            ${GEOKRET_DETAILS_MOVES_COMMENT_BUTTONS}\[1]
    Wait Until Modal                        Commenting a GeoKret move

No Buttons For Anonymous Users
    Sign Out Fast
    Go To Url                               ${PAGE_GEOKRETY_1_DETAILS_URL}
    Element Count Should Be                 ${GEOKRET_DETAILS_MOVES_EDIT_BUTTONS}       0
    Element Count Should Be                 ${GEOKRET_DETAILS_MOVES_DELETE_BUTTONS}     0
    Element Count Should Be                 ${GEOKRET_DETAILS_MOVES_PICTURE_UPLOAD_BUTTONS}       0
    Element Count Should Be                 ${GEOKRET_DETAILS_MOVES_COMMENT_BUTTONS}    0


*** Keywords ***

Seed
    Clear DB And Seed 2 users
    Seed 1 geokrety owned by 2
    Post Move                               ${MOVE_21}
    Post Move                               ${MOVE_2}
    Post Move                               ${MOVE_23}
    Post Move                               ${MOVE_4}
    Post Move                               ${MOVE_25}
    Post Move                               ${MOVE_6}
