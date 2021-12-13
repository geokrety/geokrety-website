*** Settings ***
Resource        ../functions/FunctionsGlobal.robot
Resource        ../vars/users.resource
Force Tags      News    Homepage
Suite Setup     Seed

*** Test Cases ***

No News Should Hide Panel
    Go To Url                                       ${PAGE_HOME_URL}
    Element Count Should Be                         ${HOME_NEWS_PANELS}         0

Homepage should hide older news
    Go To Url                                       ${PAGE_SEED_NEWS}/1?publish_date\=2020-07-01T15:48:00%2B00:00
    Go To Url                                       ${PAGE_HOME_URL}
    Element Count Should Be                         ${HOME_NEWS_PANELS}         0

Homepage should show 3 most recent news not older than 31 days
    Go To Url                                       ${PAGE_SEED_NEWS}/4
    Go To Url                                       ${PAGE_HOME_URL}
    Element Count Should Be                         ${HOME_NEWS_PANELS}         3


*** Keywords ***

Seed
    Clear DB And Seed 1 users
