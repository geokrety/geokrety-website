*** Settings ***
Resource        ../ressources/Authentication.robot
Variables       ../ressources/vars/users.yml
Suite Setup     Suite Setup

*** Test Cases ***

Anonymous Cannot Acces Form
    Sign Out Fast
    Go To Url                                       ${PAGE_GEOKRETY_CLAIM_URL}    redirect=${PAGE_HOME_URL_EN}
    Flash message shown                             ${UNAUTHORIZED}

Authenticated Users Can Access Form
    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_GEOKRETY_CLAIM_URL}
    Page Should Not Contain                         ${UNAUTHORIZED}
    Wait Until Panel                                Claim a GeoKret
    Page Should Contain Element                     ${CLAIM_TRACKING_CODE_INPUT}
    Page Should Contain Element                     ${CLAIM_OWNER_CODE_INPUT}
    Page Should Contain Element                     ${MODAL_PANEL_SUBMIT_BUTTON}

*** Keywords ***

Suite Setup
    Clear Database And Seed ${2} users
