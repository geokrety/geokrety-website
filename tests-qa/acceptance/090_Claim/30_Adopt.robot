*** Settings ***
Resource        ../ressources/Authentication.robot
Resource        ../ressources/Geokrety.robot
Variables       ../ressources/vars/users.yml
Test Setup      Test Setup

*** Test Cases ***

Owner Can't Claim It's Own GeoKrety
    Sign In ${USER_1.name} Fast
    Offer GeoKret For Adoption                      gkid=${1}
    Page Should Contain                             You have set this GeoKret available for adoption.
    ${oc} =    Get Text                             ${CLAIM_OWNER_CODE}
    Claim GeoKret                                   ${GEOKRETY_1.tc}                    ${oc}
    Flash message shown                             You are already the owner.

Other Users Can Claim GeoKrety
    Sign In ${USER_1.name} Fast
    Offer GeoKret For Adoption                      gkid=${1}
    Page Should Contain                             You have set this GeoKret available for adoption.
    ${oc} =    Get Text                             ${CLAIM_OWNER_CODE}
    Sign In ${USER_2.name} Fast
    Claim GeoKret                                   ${GEOKRETY_1.tc}                    ${oc}
    Flash message shown                             You are now the owner of ${GEOKRETY_1.name}

    Go To GeoKrety ${1}
    Element Should Contain                          ${GEOKRET_DETAILS_OWNER}            ${USER_2.name}

On Adoption Mail Should Be Sent To Old Owner
    Sign In ${USER_1.name} Fast
    Offer GeoKret For Adoption                      gkid=${1}
    ${oc} =    Get Text                             ${CLAIM_OWNER_CODE}
    Sign In ${USER_2.name} Fast
    Claim GeoKret                                   ${GEOKRETY_1.tc}                    ${oc}

    Mailbox Should Contain 1 Messages
    Go To Url                                       ${PAGE_DEV_MAILBOX_URL}
    Element Should Contain                          ${DEV_MAILBOX_FIRST_MAIL_LINK}      🎉 Your GeoKret '${GEOKRETY_1.name}' has been adopted
    Go To Url                                       ${PAGE_DEV_MAILBOX_FIRST_MAIL_URL}
    Page Should Contain                             Hi ${USER_1.name}
    Page Should Contain                             Good news, your GeoKret ${GEOKRETY_1.name} was just adopted by user ${USER_2.name}.

Adoption Code Already Used
    Sign In ${USER_1.name} Fast
    Offer GeoKret For Adoption                      gkid=${1}
    ${oc} =    Get Text                             ${CLAIM_OWNER_CODE}
    Sign In ${USER_2.name} Fast
    Claim GeoKret                                   ${GEOKRETY_1.tc}                    ${oc}
    Sign In ${USER_3.name} Fast
    Claim GeoKret                                   ${GEOKRETY_1.tc}                    ${oc}
    Page Should Contain                             Sorry, this owner code has already been used.

Check Adoption For GeoKrety non having an owner
    Seed ${1} geokrety owned by ${0}
    Seed owner code 123456 for geokret ${GEOKRETY_2.id}
    Sign In ${USER_2.name} Fast
    Claim GeoKret                                   ${GEOKRETY_2.tc}                    123456
    Flash message shown                             You are now the owner of ${GEOKRETY_2.name}

Invalid Input
    [Template]    Invalid Input
    TC0001      000000
    TC0000      000000
    ABCDEF      ABCDEF

*** Keywords ***

Test Setup
    Clear Database And Seed ${3} users
    Seed ${1} geokrety owned by ${USER_1.id}

Claim GeoKret
    [Arguments]    ${tc}    ${oc}
    Go To Url                                       ${PAGE_GEOKRETY_CLAIM_URL}
    Input Text                                      ${CLAIM_TRACKING_CODE_INPUT}        ${tc}
    Input Text                                      ${CLAIM_OWNER_CODE_INPUT}           ${oc}
    Click Button                                    ${MODAL_PANEL_SUBMIT_BUTTON}

Invalid Input
    [Arguments]    ${tc}    ${oc}
    Sign In ${USER_1.name} Fast
    Offer GeoKret For Adoption                      gkid=${1}
    Claim GeoKret                                   ${tc}                               ${oc}
    Page Should Contain                             Sorry, the provided Owner Code and Tracking Code doesn't match.
