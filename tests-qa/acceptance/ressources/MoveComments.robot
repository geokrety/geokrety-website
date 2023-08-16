*** Settings ***
Resource        vars/Urls.robot

*** Variables ***

*** Keywords ***

Post Move Comment
    [Arguments]    ${moveid}=1    ${comment}=${EMPTY}
    Go To Url                               ${PAGE_MOVES_COMMENT_URL}    moveid=${moveid}
    Input Text                              ${GEOKRET_MOVE_COMMENT_COMMENT_INPUT}       ${comment}
    Click Button                            ${MODAL_PANEL_SUBMIT_BUTTON}
