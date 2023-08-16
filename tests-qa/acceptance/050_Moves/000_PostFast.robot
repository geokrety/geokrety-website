*** Settings ***
Resource        ../ressources/Authentication.robot
Resource        ../ressources/Moves.robot
Test Setup      Test Setup

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

Test Setup
    Clear Database And Seed ${1} users
    Seed ${1} geokrety owned by ${1}
    Sign Out Fast
