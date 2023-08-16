*** Settings ***
Resource        ../ressources/Authentication.robot
Variables       ../ressources/vars/users.yml
Suite Setup     Suite Setup

*** Test Cases ***

Anonymous Cannot Access Authentication History
    Sign Out Fast
    Go To Url                                       ${PAGE_USER_AUTHENTICATION_HISTORY_URL}    redirect=${PAGE_SIGN_IN_URL}
    Flash message shown                             ${UNAUTHORIZED}

Anonymous Don't See Authentication History Link
    Sign Out Fast
    Go To Url                                       ${PAGE_USER_1_PROFILE_URL}
    Page Should Not Contain Link                    ${USER_PROFILE_AUTHENTICATION_HISTORY_BUTTON}

Authenticated Users Don't See Authentication History Link For Other Users
    Sign In ${USER_2.name} Fast
    Go To Url                                       ${PAGE_USER_1_PROFILE_URL}
    Page Should Not Contain Link                    ${USER_PROFILE_AUTHENTICATION_HISTORY_BUTTON}

User Himself Can Use His Authentication History
    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_USER_1_PROFILE_URL}
    Click Link                                      ${USER_PROFILE_AUTHENTICATION_HISTORY_BUTTON}
    Location Should Be                              ${PAGE_USER_AUTHENTICATION_HISTORY_URL}
    Page Should Not Contain                         ${UNAUTHORIZED}
    Page Should Contain                             🙋 Authentication history

User Himself Can Access His Authentication History
    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_USER_AUTHENTICATION_HISTORY_URL}
    Page Should Not Contain                         ${UNAUTHORIZED}
    Page Should Contain                             🙋 Authentication history

User Himself See His Authentication History Link
    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_USER_1_PROFILE_URL}
    Page Should Contain Link                        ${USER_PROFILE_AUTHENTICATION_HISTORY_BUTTON}


*** Keywords ***

Suite Setup
    Clear Database And Seed ${2} users
