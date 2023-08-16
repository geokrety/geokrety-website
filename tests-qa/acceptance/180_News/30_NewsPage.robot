*** Settings ***
Resource        ../ressources/Authentication.robot
Resource        ../ressources/ComponentsLocator.robot
Variables       ../ressources/vars/users.yml
Suite Setup     Suite Setup

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

Access News By not An Id
    Go To Url                                       ${PAGE_NEWS_URL}                newsid=FOOBAR    redirect=${PAGE_HOME_URL}
    Flash message shown                             This news does not exist.

*** Keywords ***

Suite Setup
    Clear Database And Seed ${1} users
    Go To Url                                       ${PAGE_SEED_NEWS}/2
    Sign Out Fast
