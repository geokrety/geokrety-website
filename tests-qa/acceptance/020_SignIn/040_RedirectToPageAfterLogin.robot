*** Settings ***
Library         RequestsLibrary
Resource        ../ressources/Authentication.robot
Variables       ../ressources/vars/users.yml

*** Test Cases ***

No redirect urls
    [Documentation]                     Redirect back to
    [Template]    Redirect to
    ${PAGE_NEWS_LIST_URL}                               ${PAGE_NEWS_LIST_URL}
    ${PAGE_MOVES_URL}                                   ${PAGE_MOVES_URL}
    ${PAGE_SIGN_IN_URL}                                 ${PAGE_USER_1_PROFILE_URL}
    ${PAGE_REGISTER_URL}                                ${PAGE_USER_1_PROFILE_URL}

*** Keywords ***

Test Setup
    Clear Database And Seed ${1} users
    Sign Out Fast
    Empty Dev Mailbox Fast

Redirect to
    [Arguments]    ${url}   ${expected}
    Test Setup
    Go To Url                           ${url}    redirect=${NO_REDIRECT_CHECK}
    Sign In User From Here              ${USER_1.name}
    Location Should Be                  ${expected}
