*** Settings ***
Resource        FunctionsGlobal.robot

*** Keywords ***

Offer GeoKret For Adoption
    [Arguments]    ${gkid}=1
    Go To Url With Param                           ${PAGE_GEOKRETY_DETAILS_URL}                gkid=${gkid}
    Page Should Contain Link                        ${GEOKRET_DETAILS_TRANSFER_OWNERSHIP_LINK}
    Click Element                                   ${GEOKRET_DETAILS_TRANSFER_OWNERSHIP_LINK}
    Wait Until Modal                                Offer this GeoKret for adoption?
    Click Button                                    ${MODAL_DIALOG_SUBMIT_BUTTON}
