*** Settings ***
Resource        ../functions/FunctionsGlobal.robot
Resource        ../functions/PageMoves.robot
Resource        ../vars/moves.resource
Force Tags      Moves
Test Setup      Seed

*** Test Cases ***

Predefined Moves
    [template]    Post Move
    ${MOVE_1}    # Drop
    ${MOVE_2}    # Grab
    ${MOVE_3}    # Comment
    ${MOVE_4}    # Seen
    ${MOVE_5}    # Archive
    ${MOVE_6}    # Dip


*** Keywords ***

Seed
    Clear DB And Seed 1 users
    Seed 1 geokrety owned by 1
    Sign Out Fast
