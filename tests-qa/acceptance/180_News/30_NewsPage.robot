*** Settings ***
Resource        ../functions/FunctionsGlobal.robot
Resource        ../vars/users.resource
Force Tags      News    News Details
Suite Setup     Seed

*** Test Cases ***

Check News
    Go To Url                                       ${PAGE_NEWS_URL}                newsid=1
    Element Should Contain                          ${NEWS_TITLE}                   News 1 title
    Element Should Contain                          ${NEWS_CONTENT}                 News 1 content
    Element Should Contain                          ${NEWS_AUTHOR}                  ${USER_1.name}
    Element Should Contain                          ${NEWS_COMMENTS_COUNT}          0
    Page Should Not Contain                         ${NEWS_SUBSCRIPTION_BUTTON}
    Page Should Contain Element                     ${NEWS_COMMENT_PANEL}
    Element Count Should Be                         ${NEWS_COMMENTS}                0

Leave Comment Panel - Anonymous
    Go To Url                                       ${PAGE_NEWS_URL}                newsid=1
    Element Should Contain                          ${NEWS_COMMENT_PANEL}           Please login to post a comment

Leave Comment Panel - Authenticated
    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_NEWS_URL}                newsid=1
    Element Should Not Contain                      ${NEWS_COMMENT_PANEL}           Please login to post a comment
    Page Should Contain Element                     ${NEWS_COMMENT_FORM_MESSAGE_INPUT}
    Page Should Contain Element                     ${MODAL_PANEL_SUBMIT_BUTTON}
    Element Attribute Should Be                     ${NEWS_SUBSCRIPTION_BUTTON}     data-subscribed         0

*** Keywords ***

Seed
    Clear DB And Seed 1 users
    Go To Url                                       ${PAGE_SEED_NEWS}/2
    Sign Out Fast
