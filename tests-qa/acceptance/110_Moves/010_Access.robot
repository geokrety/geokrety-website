*** Settings ***
Resource        ../functions/FunctionsGlobal.robot
Resource        ../vars/users.resource
Force Tags      Moves    Inventory
Suite Setup     Seed

*** Test Cases ***

Anonymous users should access form
    Sign Out Fast
    Go To Url                               ${PAGE_MOVES_URL}
    Page Should Contain                     Identify GeoKret
    Page Should Contain                     Even if it is - for now - not required, we recommend you to login.

Authenticated users should access form
    Sign In ${USER_1.name} Fast
    Go To User 1 url
    Go To Url                               ${PAGE_MOVES_URL}
    Page Should Contain                     Identify GeoKret
    Page Should Not Contain                 Even if it is - for now - not required, we recommend you to login.

*** Keywords ***

Seed
    Clear DB And Seed 1 users
