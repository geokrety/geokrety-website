*** Settings ***
Resource        ../ressources/Authentication.robot
Variables       ../ressources/vars/users.yml
Variables       ../ressources/vars/geokrety.yml
Suite Setup     Suite Setup


*** Test Cases ***

Anonymous Cannot Access Watch
    Sign Out Fast
    Go To Url                                       ${PAGE_GEOKRETY_WATCH_URL}          gkid=${GEOKRETY_1.id}    redirect=${PAGE_HOME_URL_EN}
    Page Should Contain                             ${UNAUTHORIZED}

Anonymous Cannot Access Unwatch
    Sign Out Fast
    Go To Url                                       ${PAGE_GEOKRETY_UNWATCH_URL}        gkid=${GEOKRETY_1.id}    redirect=${PAGE_HOME_URL_EN}
    Page Should Contain                             ${UNAUTHORIZED}

Anonymous Can Access Watchers
    Sign Out Fast
    Go To Url                                       ${PAGE_GEOKRETY_WATCHERS_URL}       gkid=${GEOKRETY_1.id}
    Page Should Not Contain                         ${UNAUTHORIZED}

Anonymous Don't See Watch Link
    Sign Out Fast
    Go To GeoKrety ${GEOKRETY_1.id}
    Page Should Not Contain Link                    ${GEOKRET_DETAILS_WATCHERS_LINK}
    Page Should Not Contain Link                    ${GEOKRET_DETAILS_WATCH_LINK}

Owner Don't See Watch Link
    Sign In ${USER_1.name} Fast
    Go To GeoKrety ${GEOKRETY_1.id}
    Page Should Contain Link                        ${GEOKRET_DETAILS_WATCHERS_LINK}
    Page Should Not Contain Link                    ${GEOKRET_DETAILS_WATCH_LINK}

Authenticated Users Can See Watch Link
    Sign In ${USER_2.name} Fast
    Go To GeoKrety ${GEOKRETY_1.id}
    Page Should Contain Link                        ${GEOKRET_DETAILS_WATCHERS_LINK}
    Page Should Contain Link                        ${GEOKRET_DETAILS_WATCH_LINK}

Owner Cannot Access Watch
    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_GEOKRETY_WATCH_URL}          gkid=${GEOKRETY_1.id}    redirect=${PAGE_GEOKRETY_1_DETAILS_URL}
    Page Should Not Contain                         Add this GeoKret to your watch list?
    Page Should Contain                             You cannot watch your own GeoKrety

Authenticated Users Can Access Watch
    Sign In ${USER_2.name} Fast
    Go To Url                                       ${PAGE_GEOKRETY_WATCH_URL}          gkid=${GEOKRETY_1.id}
    Page Should Not Contain                         ${UNAUTHORIZED}
    Wait Until Panel                                Add this GeoKret to your watch list?

Owner Cannot Access Unwatch
    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_GEOKRETY_UNWATCH_URL}        gkid=${GEOKRETY_1.id}    redirect=${PAGE_GEOKRETY_1_DETAILS_URL}
    Page Should Not Contain                         Remove this GeoKret from your watch list?
    Page Should Contain                             You cannot watch your own GeoKrety

Authenticated Users Can Access Unwatch
    Sign In ${USER_2.name} Fast
    Go To Url                                       ${PAGE_GEOKRETY_UNWATCH_URL}        gkid=${GEOKRETY_1.id}
    Page Should Not Contain                         ${UNAUTHORIZED}
    Wait Until Panel                                Remove this GeoKret from your watch list?

Owner Can Acces Watchers
    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_GEOKRETY_WATCHERS_URL}       gkid=${GEOKRETY_1.id}
    Page Should Not Contain                         ${UNAUTHORIZED}
    Page Should Contain                             No users are watching GeoKret ${GEOKRETY_1.name}

Authenticated Users Can Acces Watchers
    Sign In ${USER_2.name} Fast
    Go To Url                                       ${PAGE_GEOKRETY_WATCHERS_URL}       gkid=${GEOKRETY_1.id}
    Page Should Not Contain                         ${UNAUTHORIZED}
    Page Should Contain                             No users are watching GeoKret ${GEOKRETY_1.name}


*** Keywords ***

Suite Setup
    Clear Database And Seed ${2} users
    Seed ${1} geokrety owned by ${1}
