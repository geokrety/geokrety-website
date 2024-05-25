*** Settings ***
Library         RequestsLibrary
Library         XML
Library    DateTime
Resource        ../../ressources/Authentication.robot
Resource        ../../ressources/vars/Urls.robot
Variables       ../../ressources/vars/users.yml
Variables       ../../ressources/vars/geokrety.yml
Variables       ../../ressources/vars/moves.yml
Variables       ../../ressources/vars/waypoints.yml
# Test Setup      Test Setup

*** Variables ***
# Logtypes
&{move_21}   secid=${USER_1.secid}    nr=${GEOKRETY_1.tc}    logtype=0    wpt=${WPT_GC_1.id}    latlon=${WPT_GC_1.coords}
&{move_22}   secid=${USER_1.secid}    nr=${GEOKRETY_1.tc}    logtype=1
&{move_23}   secid=${USER_1.secid}    nr=${GEOKRETY_1.tc}    logtype=2
&{move_24}   secid=${USER_1.secid}    nr=${GEOKRETY_1.tc}    logtype=3    wpt=${WPT_GC_1.id}    latlon=${WPT_GC_1.coords}
&{move_25}   secid=${USER_1.secid}    nr=${GEOKRETY_1.tc}    logtype=4
&{move_26}   secid=${USER_1.secid}    nr=${GEOKRETY_1.tc}    logtype=5    wpt=${WPT_GC_1.id}    latlon=${WPT_GC_1.coords}
&{move_27}   secid=${USER_1.secid}    nr=${GEOKRETY_1.tc}    logtype=6    wpt=${WPT_GC_1.id}    latlon=${WPT_GC_1.coords}
# Logtypes
&{move_31}   secid=${USER_2.secid}    nr=${GEOKRETY_1.tc}    logtype=0    wpt=${WPT_GC_1.id}    latlon=${WPT_GC_1.coords}
&{move_32}   secid=${USER_2.secid}    nr=${GEOKRETY_1.tc}    logtype=1
&{move_33}   secid=${USER_2.secid}    nr=${GEOKRETY_1.tc}    logtype=2
&{move_34}   secid=${USER_2.secid}    nr=${GEOKRETY_1.tc}    logtype=3    wpt=${WPT_GC_1.id}    latlon=${WPT_GC_1.coords}
&{move_35}   secid=${USER_2.secid}    nr=${GEOKRETY_1.tc}    logtype=4
&{move_36}   secid=${USER_2.secid}    nr=${GEOKRETY_1.tc}    logtype=5    wpt=${WPT_GC_1.id}    latlon=${WPT_GC_1.coords}
&{move_37}   secid=${USER_2.secid}    nr=${GEOKRETY_1.tc}    logtype=6    wpt=${WPT_GC_1.id}    latlon=${WPT_GC_1.coords}


*** Test Cases ***
Test Valid Cases
    [Template]    Post Move Valid
    ${move_23}    ${GEOKRETY_1}
    ${move_26}    ${GEOKRETY_1}
    ${move_34}    ${GEOKRETY_1}

Test Invalid Cases
    [Template]    Post Return Error
    ${move_22}    Holder of non collectible GeoKret cannot log DROPPED/GRABBED/SEEN
    ${move_31}    Non collectible GeoKret cannot be DROPPED/GRABBED/DIPPED

*** Keywords ***

Test Setup
    Clear Database And Seed ${3} users
    Seed ${1} geokrety owned by ${1}

    # Park GK 1
    Sign In ${USER_1.name} Fast
    Go To Url                           ${PAGE_GEOKRETY_EDIT_URL}
    Checkbox Should Not Be Selected     ${GEOKRET_CREATE_PARKED_CHECKBOX}
    Select Checkbox                     ${GEOKRET_CREATE_PARKED_CHECKBOX}
    Input Text                          ${GEOKRET_CREATE_NAME_INPUT}        GKNewName
    Click Button                        ${GEOKRET_CREATE_CREATE_BUTTON}
    Location Should Be                  ${PAGE_GEOKRETY_1_DETAILS_URL}


Post Move Valid
    [Arguments]    ${move}    @{geokrety}
    Test Setup
    Create Session                          geokrety                    ${GK_URL}
    ${auth} =     GET On Session            geokrety                    url=/

    ${resp} =     POST On Session           geokrety                    url=/ruchy.php    data=&{move}
    Should Not Be Empty    ${resp.content}

    ${root} =     Parse XML                 ${resp.content}
    Should Be Equal                         ${root.tag}                 gkxml
    Response Should be XML Valid            ${root}                     @{geokrety}
    Delete All Sessions

Check Error In XML List
  [Arguments]    ${root}   ${error}
    ${value} =                            Get Elements                ${root}         xpath=.//error[.='${error}']
    Length Should Be                      ${value}                    1               msg=Error not found: ${error}


Response Should be XML Error
    [Arguments]    ${root}    @{errors}
    Should Be Equal                       ${root.tag}                 gkxml

    ${errors_count} =                     Get Length                  ${errors}
    ${count} =                            XML.Get Element Count       ${root}         errors/error
    Should Be Equal As Numbers            ${errors_count}             ${count}

    FOR     ${error}    IN    @{errors}
      Check Error In XML List             ${root}                     ${error}
    END


Post Return Error
    [Arguments]    ${move}    @{errors}
    Test Setup
    Create Session                          geokrety                    ${GK_URL}
    ${auth} =     GET On Session            geokrety                    url=/

    ${resp} =     POST On Session           geokrety                    url=/ruchy.php    data=&{move}
    Should Not Be Empty    ${resp.content}

    ${root} =     Parse XML                 ${resp.content}
    Should Be Equal                         ${root.tag}                 gkxml
    Response Should be XML Error            ${root}                     @{errors}

    Delete All Sessions


Check GeoKret In XML List
  [Arguments]    ${root}   ${geokret}
    ${value} =                            Get Elements                ${root}         xpath=.//geokret[@id='${geokret.id}']
    Length Should Be                      ${value}                    1


Response Should be XML Valid
    [Arguments]    ${root}    @{geokrety}
    Should Be Equal                       ${root.tag}                 gkxml

    ${geokrety_count} =                   Get Length                  ${geokrety}
    ${count} =                            XML.Get Element Count       ${root}         geokrety/geokret
    Should Be Equal As Numbers            ${geokrety_count}           ${count}

    FOR     ${geokret}    IN    @{geokrety}
      Check GeoKret In XML List           ${root}                     ${geokret}
    END
