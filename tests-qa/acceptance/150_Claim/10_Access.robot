*** Settings ***
Resource        ../functions/FunctionsGlobal.robot
Resource        ../vars/users.resource
Resource        ../vars/geokrety.resource
Force Tags      Claim    Access
Suite Setup     Seed

*** Test Cases ***

Anonymous Cannot Acces Form
    [Tags]    TODO
    Sign Out Fast
    Go To Url                                       ${PAGE_GEOKRETY_CLAIM_URL}
    Page Should Contain                             Unauthorized

Authenticated Users Can Access Form
    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_GEOKRETY_CLAIM_URL}
    Page Should Not Contain                         Unauthorized
    Wait Until Panel                                Claim a GeoKret
    Page Should Contain Element                     ${CLAIM_TRACKING_CODE_INPUT}
    Page Should Contain Element                     ${CLAIM_OWNER_CODE_INPUT}
    Page Should Contain Element                     ${MODAL_PANEL_SUBMIT_BUTTON}

*** Keywords ***

Seed
    Clear DB And Seed 2 users
