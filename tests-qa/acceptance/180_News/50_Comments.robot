*** Settings ***
Library         SeleniumLibrary  timeout=10  implicit_wait=0
Library         RequestsLibrary
Resource        ../functions/FunctionsGlobal.robot
Resource        ../vars/users.resource
Force Tags      News    News Details    News Subscription
Test Setup      Seed

*** Variables ***

${COMMENT} =    Hello!

*** Test Cases ***

Comment Must Have A Content
    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_NEWS_URL}                    newsid=1
    Click Button                                    ${MODAL_PANEL_SUBMIT_BUTTON}
    Input validation has error help                 ${NEWS_COMMENT_FORM_MESSAGE_INPUT}  This value is required.

Users Can Post Comments
    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_NEWS_URL}                    newsid=1
    Input Inscrybmde                                \#content                           ${COMMENT}
    Click Button                                    ${MODAL_PANEL_SUBMIT_BUTTON}
    Flash message shown                             Your comment has been saved.
    Element Should Contain                          ${NEWS_COMMENTS_COUNT}              1
    Element Count Should Be                         ${NEWS_COMMENTS}                    1
    Element Should Contain                          ${NEWS_COMMENT_AUTHOR}              ${USER_1.name}
    Page Should Contain Element                     ${NEWS_COMMENT_DELETE_BUTTON}
    Element Should Contain                          ${NEWS_COMMENT_PANEL_CONTENT}       ${COMMENT}

Users Can Subscribe Via Post Comments
    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_NEWS_URL}                    newsid=1
    Input Inscrybmde                                \#content                           ${COMMENT}
    Select Checkbox                                 ${NEWS_COMMENT_FORM_SUBSCRIBE_CHECKBOX}
    Click Button                                    ${MODAL_PANEL_SUBMIT_BUTTON}
    Element Attribute Should Be                     ${NEWS_SUBSCRIPTION_BUTTON}         data-subscribed         1

Users May Choose To Not Subscribe Via Post Comments
    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_NEWS_URL}                    newsid=1
    Input Inscrybmde                                \#content                           ${COMMENT}
    Unselect Checkbox                               ${NEWS_COMMENT_FORM_SUBSCRIBE_CHECKBOX}
    Click Button                                    ${MODAL_PANEL_SUBMIT_BUTTON}
    Element Attribute Should Be                     ${NEWS_SUBSCRIPTION_BUTTON}         data-subscribed         0

Users Can Unsubscribe Via Post Comments
    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_NEWS_URL}                    newsid=1
    Input Inscrybmde                                \#content                           ${COMMENT}
    Select Checkbox                                 ${NEWS_COMMENT_FORM_SUBSCRIBE_CHECKBOX}
    Click Button                                    ${MODAL_PANEL_SUBMIT_BUTTON}

    Go To Url                                       ${PAGE_NEWS_URL}                    newsid=1
    Input Inscrybmde                                \#content                           ${COMMENT}
    Unselect Checkbox                               ${NEWS_COMMENT_FORM_SUBSCRIBE_CHECKBOX}
    Click Button                                    ${MODAL_PANEL_SUBMIT_BUTTON}
    Element Attribute Should Be                     ${NEWS_SUBSCRIPTION_BUTTON}         data-subscribed         0

Comments Author See The Delete Button
    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_NEWS_URL}                    newsid=1
    Input Inscrybmde                                \#content                           ${COMMENT}
    Click Button                                    ${MODAL_PANEL_SUBMIT_BUTTON}

    Sign In ${USER_2.name} Fast
    Go To Url                                       ${PAGE_NEWS_URL}                    newsid=1
    Input Inscrybmde                                \#content                           ${COMMENT}
    Click Button                                    ${MODAL_PANEL_SUBMIT_BUTTON}

    Element Count Should Be                         ${NEWS_COMMENT_DELETE_BUTTON}       1

Comments Author Can Delete Their Comments
    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_NEWS_URL}                    newsid=1
    Input Inscrybmde                                \#content                           ${COMMENT}
    Click Button                                    ${MODAL_PANEL_SUBMIT_BUTTON}

    Click Button                                    ${NEWS_COMMENT_DELETE_BUTTON}
    Wait Until Modal                                Do you really want to delete this news comment?
    Element Should Contain                          ${MODAL_DIALOG}${NEWS_COMMENT_AUTHOR}              ${USER_1.name}
    Page Should Contain Element                     ${MODAL_DIALOG}${NEWS_COMMENT_DELETE_BUTTON}
    Element Should Contain                          ${MODAL_DIALOG}${NEWS_COMMENT_PANEL_CONTENT}       ${COMMENT}
    Click Button                                    ${MODAL_DIALOG_SUBMIT_BUTTON}
    Element Should Contain                          ${NEWS_COMMENTS_COUNT}              0
    Element Count Should Be                         ${NEWS_COMMENTS}                    0

Delete Comment Leave Subscription
    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_NEWS_URL}                    newsid=1
    Input Inscrybmde                                \#content                           ${COMMENT}
    Select Checkbox                                 ${NEWS_COMMENT_FORM_SUBSCRIBE_CHECKBOX}
    Click Button                                    ${MODAL_PANEL_SUBMIT_BUTTON}

    Click Button                                    ${NEWS_COMMENT_DELETE_BUTTON}
    Wait Until Modal                                Do you really want to delete this news comment?
    Click Button                                    ${MODAL_DIALOG_SUBMIT_BUTTON}
    Element Attribute Should Be                     ${NEWS_SUBSCRIPTION_BUTTON}         data-subscribed         1

Check csrf
    ${params.newsid}    Set Variable        ${1}
    Create Session                          gk      ${GK_URL}
    ${auth} =           GET On Session      gk      /devel/
    ${auth} =           GET On Session      gk      /devel/users/${USER_1.name}/login
    ${url_} =           Replace Variables   ${PAGE_NEWS_URL}
    ${resp} =           POST On Session     gk      url=${url_}?skip_csrf=False    expected_status=200
    ${body} =           Convert To String   ${resp.content}
    Should Contain                          ${body}    CSRF error, please try again.
    Delete All Sessions


*** Keywords ***

Seed
    Clear DB And Seed 2 users
    Go To Url                                       ${PAGE_SEED_NEWS}/1
    Sign Out Fast
