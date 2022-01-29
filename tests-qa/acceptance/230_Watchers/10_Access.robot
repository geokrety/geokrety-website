*** Settings ***
Resource        ../functions/FunctionsGlobal.robot
Resource        ../vars/users.resource
Resource        ../vars/geokrety.resource
Force Tags      Watch    Access
Suite Setup     Seed


*** Test Cases ***

Anonymous Cannot Acces Watch
    Sign Out Fast
    Go To Url With Param                            ${PAGE_GEOKRETY_WATCH_URL}          gkid=${GEOKRETY_1.id}
    Page Should Contain                             ${UNAUTHORIZED}

Anonymous Cannot Acces Unwatch
    Sign Out Fast
    Go To Url With Param                            ${PAGE_GEOKRETY_UNWATCH_URL}        gkid=${GEOKRETY_1.id}
    Page Should Contain                             ${UNAUTHORIZED}

Anonymous Can Acces Watchers
    Sign Out Fast
    Go To Url With Param                            ${PAGE_GEOKRETY_WATCHERS_URL}       gkid=${GEOKRETY_1.id}
    Page Should Not Contain                         ${UNAUTHORIZED}

Anonymous Don't See Watch Link
    Sign Out Fast
    Go To Url With Param                            ${PAGE_GEOKRETY_DETAILS_URL}        gkid=${GEOKRETY_1.id}
    Page Should Not Contain Link                    ${GEOKRET_DETAILS_WATCHERS_LINK}
    Page Should Not Contain Link                    ${GEOKRET_DETAILS_WATCH_LINK}

Owner Don't See Watch Link
    Sign In ${USER_1.name} Fast
    Go To Url With Param                            ${PAGE_GEOKRETY_DETAILS_URL}        gkid=${GEOKRETY_1.id}
    Page Should Contain Link                        ${GEOKRET_DETAILS_WATCHERS_LINK}
    Page Should Not Contain Link                    ${GEOKRET_DETAILS_WATCH_LINK}

Authenticated Users Can See Watch Link
    Sign In ${USER_2.name} Fast
    Go To Url With Param                            ${PAGE_GEOKRETY_DETAILS_URL}        gkid=${GEOKRETY_1.id}
    Page Should Contain Link                        ${GEOKRET_DETAILS_WATCHERS_LINK}
    Page Should Contain Link                        ${GEOKRET_DETAILS_WATCH_LINK}

Owner Cannot Access Watch
    Sign In ${USER_1.name} Fast
    Go To Url With Param                            ${PAGE_GEOKRETY_WATCH_URL}          gkid=${GEOKRETY_1.id}
    Page Should Not Contain                         Add this GeoKret to your watch list?
    Page Should Contain                             You cannot watch your own GeoKrety
    Location With Param Should Be                   ${PAGE_GEOKRETY_DETAILS_URL}        gkid=${GEOKRETY_1.ref}

Authenticated Users Can Access Watch
    Sign In ${USER_2.name} Fast
    Go To Url With Param                            ${PAGE_GEOKRETY_WATCH_URL}          gkid=${GEOKRETY_1.id}
    Page Should Not Contain                         ${UNAUTHORIZED}
    Wait Until Panel                                Add this GeoKret to your watch list?

Owner Cannot Access Unwatch
    Sign In ${USER_1.name} Fast
    Go To Url With Param                            ${PAGE_GEOKRETY_UNWATCH_URL}        gkid=${GEOKRETY_1.id}
    Page Should Not Contain                         Remove this GeoKret from your watch list?
    Page Should Contain                             You cannot watch your own GeoKrety
    Location With Param Should Be                   ${PAGE_GEOKRETY_DETAILS_URL}        gkid=${GEOKRETY_1.ref}

Authenticated Users Can Access Unwatch
    Sign In ${USER_2.name} Fast
    Go To Url With Param                            ${PAGE_GEOKRETY_UNWATCH_URL}        gkid=${GEOKRETY_1.id}
    Page Should Not Contain                         ${UNAUTHORIZED}
    Wait Until Panel                                Remove this GeoKret from your watch list?

Owner Can Acces Watchers
    Sign In ${USER_1.name} Fast
    Go To Url With Param                            ${PAGE_GEOKRETY_WATCHERS_URL}       gkid=${GEOKRETY_1.id}
    Page Should Not Contain                         ${UNAUTHORIZED}
    Page Should Contain                             No users are watching GeoKret ${GEOKRETY_1.name}

Authenticated Users Can Acces Watchers
    Sign In ${USER_2.name} Fast
    Go To Url With Param                            ${PAGE_GEOKRETY_WATCHERS_URL}       gkid=${GEOKRETY_1.id}
    Page Should Not Contain                         ${UNAUTHORIZED}
    Page Should Contain                             No users are watching GeoKret ${GEOKRETY_1.name}


*** Keywords ***

Seed
    Clear DB And Seed 2 users
    Seed 1 geokrety owned by ${USER_1.id}
