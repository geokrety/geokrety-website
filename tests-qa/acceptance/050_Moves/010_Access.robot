*** Settings ***
Library         RequestsLibrary
Resource        ../ressources/Authentication.robot
Variables       ../ressources/vars/users.yml
Suite Setup     Suite Setup

*** Test Cases ***

Anonymous users should access form
    Sign Out Fast
    Go To Url                               ${PAGE_MOVES_URL}
    Page Should Contain                     Even if it is - for now - not required, we recommend you to login.

Authenticated users should access form
    Sign In ${USER_1.name} Fast
    Go To User ${1}
    Go To Move
    Page Should Not Contain                 Even if it is - for now - not required, we recommend you to login.

*** keywords ***

Suite Setup
    Clear Database And Seed ${1} users
