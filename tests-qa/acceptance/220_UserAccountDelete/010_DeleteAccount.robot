*** Settings ***
Resource        ../functions/FunctionsGlobal.robot
Resource        ../functions/PageMoves.robot
Resource        ../vars/moves.resource
Resource        ../vars/users.resource
Resource        ../vars/geokrety.resource
Force Tags      GeoKrety Label
Test Setup     Seed

*** Variables ***

${OPERATION_INVALID}    1
${OPERATION_VALID}      2

*** Test Cases ***

Wrong Operation Result
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_USER_1_PROFILE_URL}
    Click Button                            ${USER_PROFILE_DELETE_ACCOUNT_BUTTON}
    Wait Until Modal                        Do you really want to delete your account?

    Input Text                              ${USER_PROFILE_DELETE_ACCOUNT_OPERATION_RESULT_INPUT}       ${EMPTY}
    Click Button                            ${MODAL_DIALOG_SUBMIT_BUTTON}

    Input validation has error              ${USER_PROFILE_DELETE_ACCOUNT_OPERATION_RESULT_INPUT}
    Input validation has error help         ${USER_PROFILE_DELETE_ACCOUNT_OPERATION_RESULT_INPUT}       This value is required.

    Input Text                              ${USER_PROFILE_DELETE_ACCOUNT_OPERATION_RESULT_INPUT}       ABC
    Input validation has error              ${USER_PROFILE_DELETE_ACCOUNT_OPERATION_RESULT_INPUT}
    Input validation has error help         ${USER_PROFILE_DELETE_ACCOUNT_OPERATION_RESULT_INPUT}       This value is required.

    Input Text                              ${USER_PROFILE_DELETE_ACCOUNT_OPERATION_RESULT_INPUT}       1
    Input validation has success            ${USER_PROFILE_DELETE_ACCOUNT_OPERATION_RESULT_INPUT}

    Click Button                            ${MODAL_DIALOG_SUBMIT_BUTTON}

    Location Should Be                      ${PAGE_USER_1_PROFILE_URL}
    Page Should Contain                     Wrong operation result.

Real Delete
    Delete User 1

    Sign In User                            ${USER_1.name}
    Page Should Contain                     Username and password doesn't match.

    Go To Url                               ${PAGE_GEOKRETY_1_DETAILS_URL}
    Element Count Should Be                 ${GEOKRET_DETAILS_MOVES}    ${1}
    Check GeoKret Move                      ${GEOKRET_DETAILS_MOVES}    ${1}    ${MOVE_1}    distance=0    author=Deleted user

    Element Should Contain                  ${GEOKRET_DETAILS_MOVES_COMMENTS_FIRST_AUTHOR}      Deleted user


Also Delete Comments
    Delete User 1                           clear_comment=${TRUE}

    Go To Url                               ${PAGE_GEOKRETY_1_DETAILS_URL}
    Element Count Should Be                 ${GEOKRET_DETAILS_MOVES}    ${1}
    Check GeoKret Move                      ${GEOKRET_DETAILS_MOVES}    ${1}    ${MOVE_1}    distance=0    author=Deleted user    comment=Comment suppressed

    Element Should Contain                  ${GEOKRET_DETAILS_MOVES_COMMENTS_FIRST_AUTHOR}      Deleted user
    Element Should Contain                  ${GEOKRET_DETAILS_MOVES_COMMENTS_FIRST_COMMENT}     Comment suppressed



*** Keywords ***

Seed
    Clear DB And Seed 2 users
    Seed 1 geokrety owned by 2
    Sign In ${USER_1.name} Fast
    Post Move                               ${MOVE_1}
    Post Move Comment                       comment=${COMMENT_1}
    Sign Out Fast


Delete User 1
    [Arguments]    ${clear_comment}=${FALSE}

    Sign In ${USER_1.name} Fast
    Page Should Not Contain Link            ${NAVBAR_REGISTER_LINK}
    Go To Url                               ${PAGE_USER_1_PROFILE_URL}
    Click Button                            ${USER_PROFILE_DELETE_ACCOUNT_BUTTON}
    Wait Until Modal                        Do you really want to delete your account?

    Input Text                              ${USER_PROFILE_DELETE_ACCOUNT_OPERATION_RESULT_INPUT}    ${OPERATION_VALID}

    Run Keyword If    ${clear_comment} == ${TRUE}      Select Checkbox                     ${USER_PROFILE_DELETE_ACCOUNT_REMOVE_CONTENT_CHECKBOX}
    Run Keyword If    ${clear_comment} == ${FALSE}     Unselect Checkbox                   ${USER_PROFILE_DELETE_ACCOUNT_REMOVE_CONTENT_CHECKBOX}

    Click Button                            ${MODAL_DIALOG_SUBMIT_BUTTON}
    Location Should Be                      ${PAGE_HOME_URL}
    Page Should Contain                     Your account is now deleted. Thanks for playing with us.
    Page Should Contain Link                ${NAVBAR_REGISTER_LINK}
