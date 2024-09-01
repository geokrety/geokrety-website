*** Settings ***
Resource        ../ressources/Authentication.robot
Resource        ../ressources/vars/Urls.robot
Resource        ../ressources/Moves.robot
Variables       ../ressources/vars/users.yml
Variables       ../ressources/vars/geokrety.yml
Variables       ../ressources/vars/waypoints.yml
Suite Setup     Suite Setup

*** Variables ***

*** Test Cases ***

Anonymous users cannot impersonate
    Go To Url                               ${PAGE_HOME_URL_EN}
    Page Should Not Contain Element         ${NAVBAR_ACTIONS_IMPERSONATE_USER_START_LINK}
    Page Should Not Contain Element         ${NAVBAR_ACTIONS_IMPERSONATE_USER_STOP_LINK}

    Go To Url                               ${PAGE_USER_3_PROFILE_URL}
    Page Should Not Contain Element         ${NAVBAR_ACTIONS_IMPERSONATE_USER_START_LINK}
    Page Should Not Contain Element         ${NAVBAR_ACTIONS_IMPERSONATE_USER_STOP_LINK}

    Go To Url                               ${PAGE_ADMIN_IMPERSONATE_USER_START}    userid=${USER_3.id}    redirect=${PAGE_SIGN_IN_URL}
    Go To Url                               ${PAGE_ADMIN_IMPERSONATE_USER_STOP}     userid=${USER_3.id}    redirect=${PAGE_SIGN_IN_URL}


Other users cannot impersonate
    Sign In ${USER_2.name} Fast

    Go To Url                               ${PAGE_HOME_URL_EN}
    Page Should Not Contain Element         ${NAVBAR_ACTIONS_IMPERSONATE_USER_START_LINK}
    Page Should Not Contain Element         ${NAVBAR_ACTIONS_IMPERSONATE_USER_STOP_LINK}

    Go To Url                               ${PAGE_USER_3_PROFILE_URL}
    Page Should Not Contain Element         ${NAVBAR_ACTIONS_IMPERSONATE_USER_START_LINK}
    Page Should Not Contain Element         ${NAVBAR_ACTIONS_IMPERSONATE_USER_STOP_LINK}

    Go To Url                               ${PAGE_ADMIN_IMPERSONATE_USER_START}    userid=${USER_3.id}    redirect=${PAGE_HOME_URL_EN}
    Page Should Contain                     HTTP 403
    Go To Url                               ${PAGE_ADMIN_IMPERSONATE_USER_STOP}     userid=${USER_3.id}    redirect=${PAGE_HOME_URL_EN}
    Page Should Contain                     HTTP 403


Admin users can impersonate
    Sign In ${USER_1.name} Fast

    Go To Url                               ${PAGE_HOME_URL_EN}
    Page Should Not Contain Element         ${NAVBAR_ACTIONS_IMPERSONATE_USER_START_LINK}
    Page Should Not Contain Element         ${NAVBAR_ACTIONS_IMPERSONATE_USER_STOP_LINK}

    Go To Url                               ${PAGE_USER_1_PROFILE_URL}
    Page Should Not Contain Element         ${NAVBAR_ACTIONS_IMPERSONATE_USER_START_LINK}
    Page Should Not Contain Element         ${NAVBAR_ACTIONS_IMPERSONATE_USER_STOP_LINK}

    Go To Url                               ${PAGE_USER_3_PROFILE_URL}
    Page Should Contain Element             ${NAVBAR_ACTIONS_IMPERSONATE_USER_START_LINK}
    Page Should Not Contain Element         ${NAVBAR_ACTIONS_IMPERSONATE_USER_STOP_LINK}
    Element Text Should Be                  ${NAVBAR_PROFILE_LINK}    ${USER_1.name}

    Go To Url                               ${PAGE_ADMIN_IMPERSONATE_USER_START}    userid=${USER_3.id}    redirect=${PAGE_USER_3_PROFILE_URL}
    Page Should Not Contain Element         ${NAVBAR_ACTIONS_IMPERSONATE_USER_START_LINK}
    Page Should Contain Element             ${NAVBAR_ACTIONS_IMPERSONATE_USER_STOP_LINK}
    Element Text Should Be                  ${NAVBAR_PROFILE_LINK}    ${USER_3.name}

    Go To Url                               ${PAGE_HOME_URL_EN}
    Page Should Not Contain Element         ${NAVBAR_ACTIONS_IMPERSONATE_USER_START_LINK}
    Page Should Contain Element             ${NAVBAR_ACTIONS_IMPERSONATE_USER_STOP_LINK}

    Go To Url                               ${PAGE_USER_1_PROFILE_URL}
    Page Should Contain Element             ${NAVBAR_ACTIONS_IMPERSONATE_USER_START_LINK}
    Page Should Contain Element             ${NAVBAR_ACTIONS_IMPERSONATE_USER_STOP_LINK}

    Go To Url                               ${PAGE_USER_2_PROFILE_URL}
    Page Should Contain Element             ${NAVBAR_ACTIONS_IMPERSONATE_USER_START_LINK}
    Page Should Contain Element             ${NAVBAR_ACTIONS_IMPERSONATE_USER_STOP_LINK}

    Click Element                           ${NAVBAR_ACTIONS_IMPERSONATE_USER_START_LINK}
    Element Text Should Be                  ${NAVBAR_PROFILE_LINK}    ${USER_2.name}
    Page Should Not Contain Element         ${NAVBAR_ACTIONS_IMPERSONATE_USER_START_LINK}
    Page Should Contain Element             ${NAVBAR_ACTIONS_IMPERSONATE_USER_STOP_LINK}

    Go To Url                               ${PAGE_USER_3_PROFILE_URL}
    Element Text Should Be                  ${NAVBAR_PROFILE_LINK}    ${USER_2.name}
    Page Should Contain Element             ${NAVBAR_ACTIONS_IMPERSONATE_USER_START_LINK}
    Page Should Contain Element             ${NAVBAR_ACTIONS_IMPERSONATE_USER_STOP_LINK}

    Click Element                           ${NAVBAR_ACTIONS_IMPERSONATE_USER_STOP_LINK}
    Location Should Be                      ${PAGE_USER_2_PROFILE_URL}
    Element Text Should Be                  ${NAVBAR_PROFILE_LINK}    ${USER_1.name}
    Page Should Contain Element             ${NAVBAR_ACTIONS_IMPERSONATE_USER_START_LINK}
    Page Should Not Contain Element         ${NAVBAR_ACTIONS_IMPERSONATE_USER_STOP_LINK}

*** Keywords ***

Suite Setup
    Clear Database And Seed ${3} users
    Seed ${1} geokrety owned by ${1}
    Sign Out Fast
