*** Settings ***
Library         RequestsLibrary
Resource        ../ressources/Authentication.robot
Resource        ../ressources/Geokrety.robot
Variables       ../ressources/vars/users.yml
Test Setup      Test Setup

*** Test Cases ***

No link present in navbar for anonymous users
    Go To Home
    Click Link                          ${NAVBAR_ACTIONS_LINK}
    Page Should Not Contain Element     ${NAVBAR_ACTIONS_CREATE_GEOKRET_LINK}

No link present in profile actions for signed in users
    Go To User ${USER_1.id}
    Page Should Not Contain Element     ${USER_PROFILE_CREATE_GEOKRET_BUTTON}

Link present in navbar for signed in users
    Sign In ${USER_1.name} Fast
    Go To Home
    Click Link                          ${NAVBAR_ACTIONS_LINK}
    Page Should Contain Element         ${NAVBAR_ACTIONS_CREATE_GEOKRET_LINK}
    Click Link                          ${NAVBAR_ACTIONS_CREATE_GEOKRET_LINK}
    Location Should Be                  ${PAGE_GEOKRETY_CREATE_URL}
    Page WithoutWarningOrFailure
    Page Should Show Creation Form

Link present on user's profile actions for signed in users
    Sign In ${USER_1.name} Fast
    Go To User ${USER_1.id}
    Page Should Contain Element         ${USER_PROFILE_CREATE_GEOKRET_BUTTON}
    Click Link                          ${USER_PROFILE_CREATE_GEOKRET_BUTTON}
    Location Should Be                  ${PAGE_GEOKRETY_CREATE_URL}
    Page WithoutWarningOrFailure
    Page Should Show Creation Form

Link absent on someone else profile actions
    Sign In ${USER_1.name} Fast
    Go To User ${USER_2.id}
    Page Should Not Contain Element     ${USER_PROFILE_CREATE_GEOKRET_BUTTON}

Direct link access to form
    Sign In ${USER_1.name} Fast
    Go To Url                           ${PAGE_GEOKRETY_CREATE_URL}
    Page WithoutWarningOrFailure
    Page Should Show Creation Form

Direct link access to form - anonymous forbiden
    Go To Url                           ${PAGE_GEOKRETY_CREATE_URL}    redirect=${PAGE_SIGN_IN_URL}

*** Keywords ***

Test Setup
    Clear Database And Seed ${2} users
    Sign Out Fast
    Empty Dev Mailbox Fast
