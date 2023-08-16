*** Settings ***
Resource        ../ressources/Authentication.robot
Resource        ../ressources/Moves.robot
Variables       ../ressources/vars/users.yml
Test Setup      Test Setup

*** Variables ***
${COMMENT} =    Some comment !

*** Test Cases ***

Not Existent Move Cannot Be Commented
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_MOVES_COMMENT_URL}    moveid=666       redirect=${PAGE_HOME_URL_EN}
    Flash message shown                     This move does not exist

    Go To Url                               ${PAGE_MOVES_COMMENT_URL}    moveid=ABCDEF    redirect=${PAGE_HOME_URL_EN}
    Flash message shown                     This move does not exist

Move Comment Url Only For Authenticated
    Sign Out Fast
    Go To Url                               ${PAGE_MOVES_COMMENT_URL}    moveid=1    redirect=${PAGE_SIGN_IN_URL}

Move Comment Url Has Panel As Authenticated
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_MOVES_COMMENT_URL}    moveid=1
    Location With Param Should Be           ${PAGE_MOVES_COMMENT_URL}    moveid=1
    Wait Until Panel                        Commenting a GeoKret move

Commented Move Should Be Shown
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_MOVES_COMMENT_URL}    moveid=1
    Check GeoKret Move                      ${MODAL_PANEL}    ${1}    ${MOVE_1}

Post Comment Via Direct Url
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_MOVES_COMMENT_URL}    moveid=1
    Input Text                              ${GEOKRET_MOVE_COMMENT_COMMENT_INPUT}       ${COMMENT}
    Click Button                            ${MODAL_PANEL_SUBMIT_BUTTON}
    Location With Param Should Be           ${PAGE_GEOKRETY_1_DETAILS_URL}/page/1#log1
    Check Move Comment                      ${GEOKRET_DETAILS_MOVES_COMMENTS_FIRST_ITEM}    comment=${COMMENT}

Post Comment Via Modal
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_GEOKRETY_1_DETAILS_URL}
    Click Element                           ${GEOKRET_DETAILS_MOVES_COMMENT_BUTTONS}\[1]
    Wait Until Modal                        Commenting a GeoKret move
    Input Text                              ${GEOKRET_MOVE_COMMENT_COMMENT_INPUT}       ${COMMENT}
    Click Button                            ${MODAL_DIALOG_SUBMIT_BUTTON}
    Location With Param Should Be           ${PAGE_GEOKRETY_1_DETAILS_URL}/page/1#log1
    Check Move Comment                      ${GEOKRET_DETAILS_MOVES_COMMENTS_FIRST_ITEM}    comment=${COMMENT}

Check Valid Comments
    [Template]    Submit Valid Comment Template
    1
    Some Comment
    🐍 Snake
    🐲🐉🦕
    ${SPACE}Some Comment${SPACE}

Check Invalid Comments
    [Template]    Submit Invalid Comment Template
    ${EMPTY}
    ${SPACE}
    ${SPACE*3}

*** Keywords ***

Test Setup
    Clear Database And Seed ${2} users
    Seed ${1} geokrety owned by ${2}
    Post Move                               ${MOVE_1}

Submit Valid Comment Template
    [Arguments]    ${comment}
    Test Setup
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_MOVES_COMMENT_URL}    moveid=1
    Input Text                              ${GEOKRET_MOVE_COMMENT_COMMENT_INPUT}       ${comment}
    Input validation has success            ${GEOKRET_MOVE_COMMENT_COMMENT_INPUT}
    Click Button                            ${MODAL_PANEL_SUBMIT_BUTTON}
    Location With Param Should Be           ${PAGE_GEOKRETY_1_DETAILS_URL}/page/1#log1
    Check Move Comment                      ${GEOKRET_DETAILS_MOVES_COMMENTS_FIRST_ITEM}     author=${USER_1.name}    comment=${comment}

Submit Invalid Comment Template
    [Arguments]    ${comment}    ${expect}=This value is required.
    Test Setup
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_MOVES_COMMENT_URL}    moveid=1
    Input Text                              ${GEOKRET_MOVE_COMMENT_COMMENT_INPUT}       ${comment}
    Input validation has error              ${GEOKRET_MOVE_COMMENT_COMMENT_INPUT}
    Input validation has error help         ${GEOKRET_MOVE_COMMENT_COMMENT_INPUT}       ${expect}
    Click Button                            ${MODAL_PANEL_SUBMIT_BUTTON}
    Location With Param Should Be           ${PAGE_MOVES_COMMENT_URL}    moveid=1
