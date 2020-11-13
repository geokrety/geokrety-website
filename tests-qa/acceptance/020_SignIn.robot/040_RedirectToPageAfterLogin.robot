*** Settings ***
Library         SeleniumLibrary  timeout=10  implicit_wait=0
Resource        ../functions/PageRegistration.robot
Resource        ../vars/users.resource
Force Tags      Sign In
Suite Setup     Seed

*** Test Cases ***

Redirects to last page
    [Documentation]                     Redirect back on news page
    Go To Url                           ${PAGE_NEWS_LIST_URL}
    Sign In User                        ${USER_1.name}
    Location Should Be                  ${PAGE_NEWS_LIST_URL}
    Sign Out User

No redirect urls
    [Documentation]                     Redirect back to
    [Template]    Redirect to
    ${PAGE_SIGN_IN_URL}                                 ${PAGE_USER_1_PROFILE_URL}
    # ${PAGE_SIGN_OUT_URL}                              ### DISABLED WHILE IT DOESN'T SHOW A NICE PAGE
    ${PAGE_REGISTER_URL}                                ${PAGE_USER_1_PROFILE_URL}
    ${GK_URL}/en/registration/fake_token/activate       ${PAGE_HOME_URL}

*** Keywords ***

Seed
    Clear Database
    Seed 1 users

Redirect to
    [Arguments]    ${url}   ${expected}
    Go To Url                           ${url}
    Sign In User                        ${USER_1.name}
    Location Should Be                  ${expected}
    Sign Out Fast
