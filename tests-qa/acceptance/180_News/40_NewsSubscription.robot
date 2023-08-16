*** Settings ***
Resource        ../ressources/Authentication.robot
Resource        ../ressources/ComponentsLocator.robot
Variables       ../ressources/vars/users.yml
Test Setup      Test Setup

*** Test Cases ***


Click Should Show Subscribe Confirmation
    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_NEWS_URL}                newsid=1
    Click Button                                    ${NEWS_SUBSCRIPTION_BUTTON}
    Wait Until Modal                                Subscribe to this news?

User Can Subscribe
    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_NEWS_URL}                newsid=1
    Click Button                                    ${NEWS_SUBSCRIPTION_BUTTON}
    Wait Until Modal                                Subscribe to this news?
    Click Button                                    ${MODAL_DIALOG_SUBMIT_BUTTON}
    Flash message shown                             You will now receive updates on new comments.
    Element Attribute Should Be                     ${NEWS_SUBSCRIPTION_BUTTON}     data-subscribed         1

User Can Unsubscribe
    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_NEWS_URL}                newsid=1
    Click Button                                    ${NEWS_SUBSCRIPTION_BUTTON}
    Wait Until Modal                                Subscribe to this news?
    Click Button                                    ${MODAL_DIALOG_SUBMIT_BUTTON}

    Go To Url                                       ${PAGE_NEWS_URL}                newsid=1
    Click Button                                    ${NEWS_SUBSCRIPTION_BUTTON}
    Wait Until Modal                                Do you really want to unsubscribe from this news?
    Click Button                                    ${MODAL_DIALOG_SUBMIT_BUTTON}
    Flash message shown                             You will not receive updates anymore.
    Element Attribute Should Be                     ${NEWS_SUBSCRIPTION_BUTTON}     data-subscribed         0

Access Subscribe By Unknown Id
    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_NEWS_URL}                newsid=123456    redirect=${PAGE_HOME_URL_EN}

Access Subscribe By Not An Id
    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_NEWS_URL}                newsid=FOOBAR    redirect=${PAGE_HOME_URL_EN}


*** Keywords ***

Test Setup
    Clear Database And Seed ${1} users
    Go To Url                                       ${PAGE_SEED_NEWS}/2
    Sign Out Fast
