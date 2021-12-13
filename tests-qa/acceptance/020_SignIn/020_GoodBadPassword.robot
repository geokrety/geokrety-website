*** Settings ***
Resource        ../functions/PageRegistration.robot
Resource        ../vars/users.resource
Force Tags      Sign In
Suite Setup     Seed

*** Test Cases ***

Sign In user
    [Documentation]                     Good password
    Go To Url                           ${PAGE_HOME_URL}
    Sign In User                        ${USER_1.name}
    Page Should Not Contain             Username and password doesn't match.
    Page Should Contain                 Welcome on board
    Element Should Contain              ${NAVBAR_PROFILE_LINK}    ${USER_1.name}
    Sign Out User

Sign In user with wrong password
    [Documentation]                     Wrong password
    Go To Url                           ${PAGE_HOME_URL}
    Sign In User                        ${USER_1.name}    bad password
    Page Should Contain                 Username and password doesn't match.
    Location Should Contain             ${PAGE_SIGN_IN_URL}
    Page Should Not Contain Element     ${NAVBAR_PROFILE_LINK}

Sign In user with non existent username
    [Documentation]                     Wrong password
    Go To Url                           ${PAGE_HOME_URL}
    Sign In User                        someone innexistent    password
    Page Should Contain                 Username and password doesn't match.
    Location Should Contain             ${PAGE_SIGN_IN_URL}
    Page Should Not Contain Element     ${NAVBAR_PROFILE_LINK}

Fast devel login
    [Documentation]                     Validate the fast sign in function in devel mode
    Sign In ${USER_1.name} Fast
    Go To Url                           ${PAGE_HOME_URL}
    Element Should Contain              ${NAVBAR_PROFILE_LINK}    ${USER_1.name}

Fast devel logout
    [Documentation]                     Validate the fast sign out function in devel mode
    Sign In ${USER_1.name} Fast
    Sign Out Fast
    Go To Url                           ${PAGE_HOME_URL}
    Page Should Not Contain Element     ${NAVBAR_PROFILE_LINK}

*** Keywords ***

Seed
    [Documentation]         Seed an account
    Clear Database
    Seed 1 users
