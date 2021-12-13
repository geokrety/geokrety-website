*** Settings ***
Library         DependencyLibrary
Resource        ../functions/PageGeoKretyCreate.robot
Resource        ../vars/users.resource
Force Tags      Create GeoKrety
Suite Setup     Seed

*** Test Cases ***

No link present in navbar for anonymous users
    Go To Url                           ${PAGE_HOME_URL}
    Click Link                          ${NAVBAR_ACTIONS_LINK}
    Page Should Not Contain Element     ${NAVBAR_ACTIONS_CREATE_GEOKRET_LINK}

No link present in profile actions for signed in users
    Go To Url                           ${PAGE_USER_1_PROFILE_URL}
    Page Should Not Contain Element     ${USER_PROFILE_CREATE_GEOKRET_BUTTON}

Forbidden for anonymous users
    [Tags]    TODO
    Go To Url                           ${PAGE_GEOKRETY_CREATE_URL}
    Location Should Contain             ${PAGE_SIGN_IN_URL}

Sign in
    Sign In ${USER_1.name} Fast

Link present in navbar for signed in users
    Depends on test                     Sign in
    Go To Url                           ${PAGE_HOME_URL}
    Click Link                          ${NAVBAR_ACTIONS_LINK}
    Page Should Contain Element         ${NAVBAR_ACTIONS_CREATE_GEOKRET_LINK}

Link present on user's profile actions for signed in users
    Depends on test                     Sign in
    Go To Url                           ${PAGE_USER_1_PROFILE_URL}
    Page Should Contain Element         ${USER_PROFILE_CREATE_GEOKRET_BUTTON}

Link absent on someone else profile actions
    Depends on test                     Sign in
    Go To Url                           ${PAGE_USER_2_PROFILE_URL}
    Page Should Not Contain Element     ${USER_PROFILE_CREATE_GEOKRET_BUTTON}

Use navbar link to access form
    Depends on test                     Sign in
    Go To Url                           ${PAGE_HOME_URL}
    Click Link                          ${NAVBAR_ACTIONS_LINK}
    Click Link                          ${NAVBAR_ACTIONS_CREATE_GEOKRET_LINK}
    Location Should Be                  ${PAGE_GEOKRETY_CREATE_URL}
    Page WithoutWarningOrFailure
    Page Should Show Creation Form

Use profile action link to access form
    Depends on test                     Sign in
    Go To Url                           ${PAGE_USER_1_PROFILE_URL}
    Click Link                          ${USER_PROFILE_CREATE_GEOKRET_BUTTON}
    Location Should Be                  ${PAGE_GEOKRETY_CREATE_URL}
    Page WithoutWarningOrFailure
    Page Should Show Creation Form

Direct link access to form
    Depends on test                     Sign in
    Go To Url                           ${PAGE_GEOKRETY_CREATE_URL}
    Location Should Be                  ${PAGE_GEOKRETY_CREATE_URL}
    Page WithoutWarningOrFailure
    Page Should Show Creation Form

*** Keywords ***

Seed
    Clear Database
    Seed 2 users
