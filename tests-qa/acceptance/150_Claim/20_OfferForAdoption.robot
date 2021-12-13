*** Settings ***
Resource        ../functions/FunctionsGlobal.robot
Resource        ../functions/PageClaim.robot
Resource        ../vars/users.resource
Resource        ../vars/geokrety.resource
Force Tags      Claim    Access
Suite Setup     Seed

*** Test Cases ***

Anonymous Don't Have Link To Offer
    Sign Out Fast
    Go To Url                                       ${PAGE_GEOKRETY_1_DETAILS_URL}
    Page Should Not Contain Link                    ${GEOKRET_DETAILS_TRANSFER_OWNERSHIP_LINK}

GeoKret Owner Has Link To Offer
    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_GEOKRETY_1_DETAILS_URL}
    Page Should Contain Link                        ${GEOKRET_DETAILS_TRANSFER_OWNERSHIP_LINK}

Other Authenticated Users Don't Have Link To Offer
    Sign In ${USER_2.name} Fast
    Go To Url                                       ${PAGE_GEOKRETY_1_DETAILS_URL}
    Page Should Not Contain Link                    ${GEOKRET_DETAILS_TRANSFER_OWNERSHIP_LINK}

Clic Offer Ask For Confirmation
    Sign In ${USER_1.name} Fast
    Offer GeoKret For Adoption                      gkid=${1}
    Page Should Contain                             You have set this GeoKret available for adoption.

Anonymous Don't See The Adoption Code
    Sign In ${USER_1.name} Fast
    Offer GeoKret For Adoption                      gkid=${1}
    Sign Out Fast
    Go To Url                                       ${PAGE_GEOKRETY_1_DETAILS_URL}
    Page Should Contain                             This GeoKret is available for adoption. Please login first.
    Page Should Not Contain                         You have set this GeoKret available for adoption.

Other Users Don't See The Adoption Code
    Sign In ${USER_1.name} Fast
    Offer GeoKret For Adoption                      gkid=${1}
    Sign In ${USER_2.name} Fast
    Go To Url                                       ${PAGE_GEOKRETY_1_DETAILS_URL}
    Page Should Contain                             This GeoKret is available for adoption. If the current owner gave you the Tracking code plus the Owner code, then you can claim this GeoKret.
    Page Should Not Contain                         This GeoKret is available for adoption. Please login first.
    Page Should Not Contain                         You have set this GeoKret available for adoption.


*** Keywords ***

Seed
    Clear DB And Seed 2 users
    Seed 1 geokrety owned by ${USER_1.id}
