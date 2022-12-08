*** Settings ***
Library         RequestsLibrary
Library         XML
Resource        ../functions/FunctionsGlobal.robot
Resource        ../vars/users.resource
Resource        ../vars/geokrety.resource
Resource        ../vars/waypoints.resource
Suite Setup     Seed
Force Tags      xml    legacy    ruchy

*** Variables ***

# Stan
&{move_1}    nr=${GEOKRETY_1.tc}    logtype=0    data=2020-12-09    godzina=21    minuta=01    wpt=${WPT_GC_1.id}    latlon=${WPT_GC_1.coords}    comment=Hello    app=robotframework    app_ver=3.2.1
&{move_2}    nr=${GEOKRETY_1.tc}    logtype=0    data=2020-12-09    godzina=21    minuta=02    wpt=${WPT_GC_1.id}    latlon=${WPT_GC_1.coords}    comment=Hello    app=robotframework    app_ver=3.2.1    secid=invalidxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx3

# New English names for parameters
&{move_9}    tracking_code=${GEOKRETY_1.tc}    logtype=5    date=2020-12-09    hour=21    minute=09    waypoint=${WPT_GC_1.id}    coordinates=${WPT_GC_1.coords}    comment=Hello    app=robotframework    app_ver=3.2.1    secid=${USER_1.secid}

# Required parameters
&{move_11}   secid=${USER_1.secid}
&{move_12}   secid=${USER_1.secid}                           logtype=0    data=2020-12-09    godzina=21    minuta=10    wpt=${WPT_GC_1.id}    latlon=${WPT_GC_1.coords}
&{move_13}   secid=${USER_1.secid}    nr=${GEOKRETY_1.tc}    logtype=0    data=2020-12-09    godzina=21    minuta=11
&{move_14}   secid=${USER_1.secid}    nr=${GEOKRETY_1.tc}    logtype=0    data=2020-12-09    godzina=21    minuta=12    wpt=${WPT_GC_1.id}
&{move_15}   secid=${USER_1.secid}    nr=${GEOKRETY_1.tc}    logtype=0    data=2020-12-09    godzina=21    minuta=13    wpt=${WPT_OC_1.id}
&{move_16}   secid=${USER_1.secid}    nr=${GEOKRETY_1.tc}    logtype=0    data=2222-12-09    godzina=21    minuta=14    wpt=${WPT_GC_1.id}    latlon=${WPT_GC_1.coords}
&{move_17}   secid=${USER_1.secid}    nr=${GEOKRETY_1.tc}    logtype=0    data=2010-12-09    godzina=21    minuta=15    wpt=${WPT_GC_1.id}    latlon=${WPT_GC_1.coords}
&{move_18}   secid=${USER_1.secid}    nr=BADTC               logtype=0    data=2020-12-09    godzina=21    minuta=16    wpt=${WPT_GC_1.id}    latlon=${WPT_GC_1.coords}
&{move_19}   secid=${USER_1.secid}    nr=BADTC1              logtype=0    data=2020-12-09    godzina=21    minuta=17    wpt=${WPT_GC_1.id}    latlon=${WPT_GC_1.coords}
&{move_20}   secid=${USER_1.secid}    nr=BADTCTOOLONG        logtype=0    data=2020-12-09    godzina=21    minuta=18    wpt=${WPT_GC_1.id}    latlon=${WPT_GC_1.coords}

# Logtypes
&{move_21}   secid=${USER_1.secid}    nr=${GEOKRETY_1.tc}    logtype=0    data=2020-12-09    godzina=22    minuta=01    wpt=${WPT_GC_1.id}    latlon=${WPT_GC_1.coords}
&{move_22}   secid=${USER_1.secid}    nr=${GEOKRETY_1.tc}    logtype=1    data=2020-12-09    godzina=22    minuta=02
&{move_23}   secid=${USER_1.secid}    nr=${GEOKRETY_1.tc}    logtype=2    data=2020-12-09    godzina=22    minuta=03
&{move_24}   secid=${USER_1.secid}    nr=${GEOKRETY_1.tc}    logtype=3    data=2020-12-09    godzina=22    minuta=04    wpt=${WPT_GC_1.id}    latlon=${WPT_GC_1.coords}
&{move_25}   secid=${USER_1.secid}    nr=${GEOKRETY_1.tc}    logtype=4    data=2020-12-09    godzina=22    minuta=05
&{move_26}   secid=${USER_1.secid}    nr=${GEOKRETY_1.tc}    logtype=5    data=2020-12-09    godzina=22    minuta=06    wpt=${WPT_GC_1.id}    latlon=${WPT_GC_1.coords}
&{move_27}   secid=${USER_1.secid}    nr=${GEOKRETY_1.tc}    logtype=6    data=2020-12-09    godzina=22    minuta=07    wpt=${WPT_GC_1.id}    latlon=${WPT_GC_1.coords}

# Multiple Tracking Code
&{move_31}   secid=${USER_1.secid}    nr=${GEOKRETY_1.tc},${GEOKRETY_2.tc}                                      logtype=0    data=2020-12-09    godzina=22    minuta=08    wpt=${WPT_GC_1.id}    latlon=${WPT_GC_1.coords}
&{move_32}   secid=${USER_1.secid}    nr=${GEOKRETY_1.tc},${GEOKRETY_2.tc},${GEOKRETY_3.tc},${GEOKRETY_4.tc},${GEOKRETY_5.tc},${GEOKRETY_6.tc},${GEOKRETY_7.tc},${GEOKRETY_8.tc},${GEOKRETY_9.tc},${GEOKRETY_10.tc}    logtype=0    data=2020-12-09    godzina=22    minuta=09    wpt=${WPT_GC_1.id}    latlon=${WPT_GC_1.coords}
&{move_33}   secid=${USER_1.secid}    nr=${GEOKRETY_1.tc},${GEOKRETY_2.tc},${GEOKRETY_3.tc},${GEOKRETY_4.tc},${GEOKRETY_5.tc},${GEOKRETY_6.tc},${GEOKRETY_7.tc},${GEOKRETY_8.tc},${GEOKRETY_9.tc},${GEOKRETY_10.tc},${GEOKRETY_11.tc}    logtype=0    data=2020-12-09    godzina=22    minuta=10    wpt=${WPT_GC_1.id}    latlon=${WPT_GC_1.coords}
&{move_34}   secid=${USER_1.secid}    nr=${GEOKRETY_1.tc},${GEOKRETY_1.tc}                                      logtype=0    data=2020-12-09    godzina=22    minuta=11    wpt=${WPT_GC_1.id}    latlon=${WPT_GC_1.coords}
&{move_35}   secid=${USER_1.secid}    nr=${GEOKRETY_1.tc},${GEOKRETY_2.tc},${GEOKRETY_1.tc},${GEOKRETY_2.tc}    logtype=0    data=2020-12-09    godzina=22    minuta=12    wpt=${WPT_GC_1.id}    latlon=${WPT_GC_1.coords}
&{move_36}   secid=${USER_1.secid}    nr=${GEOKRETY_1.tc},${GEOKRETY_12.tc}                                     logtype=0    data=2020-12-09    godzina=22    minuta=13    wpt=${WPT_GC_1.id}    latlon=${WPT_GC_1.coords}
&{move_37}   secid=${USER_1.secid}    nr=${GEOKRETY_1.tc},${GEOKRETY_12.tc},${GEOKRETY_13.tc}                   logtype=0    data=2020-12-09    godzina=22    minuta=13    wpt=${WPT_GC_1.id}    latlon=${WPT_GC_1.coords}

# Timezone (generator create GK at 2020-08-22 15:30:42)
&{move_41}   secid=${USER_1.secid}    nr=${GEOKRETY_1.tc}    logtype=0    data=2020-08-22    godzina=15    minuta=30                       wpt=${WPT_GC_1.id}    latlon=${WPT_GC_1.coords}
&{move_42}   secid=${USER_1.secid}    nr=${GEOKRETY_1.tc}    logtype=0    data=2020-08-22    godzina=17    minuta=31    tz=Europe/Paris    wpt=${WPT_GC_1.id}    latlon=${WPT_GC_1.coords}
&{move_43}   secid=${USER_1.secid}    nr=${GEOKRETY_1.tc}    logtype=0    data=2020-08-22    godzina=15    minuta=30    tz=UTC             wpt=${WPT_GC_1.id}    latlon=${WPT_GC_1.coords}
&{move_44}   secid=${USER_1.secid}    nr=${GEOKRETY_1.tc}    logtype=0    data=2020-08-22    godzina=17    minuta=32                       wpt=${WPT_GC_1.id}    latlon=${WPT_GC_1.coords}

*** Test Cases ***

Required Parameters
    [Template]    Post Return Error
    ${move_11}    Waypoint seems empty.    Missing or invalid coordinates.    No Tracking Code provided.
    ${move_12}    No Tracking Code provided.
    ${move_13}    Waypoint seems empty.    Missing or invalid coordinates.
    ${move_14}    Missing or invalid coordinates.    View the <a href="https://geokrety.org/go2geo/?wpt=${move_14.wpt}" target="_blank">cache page</a>.     This is a Geocaching.com cache that no one logged yet on GeoKrety.org. To ensure correct travel of this GeoKret, please copy/paste cache coordinates in the "Coordinates" field.
    ${move_15}    Missing or invalid coordinates.    View the <a href="https://geokrety.org/go2geo/?wpt=${move_15.wpt}" target="_blank">cache page</a>.     Sorry, but this waypoint is not (yet) in our database. Does it really exist?
    ${move_16}    Moved_on_datetime cannot be in the future
    ${move_17}    Moved_on_datetime must be after GeoKret birth
    ${move_18}    Tracking Code "${move_18.nr}" seems too short. We expect 6 characters here.
    ${move_19}    Sorry, but Tracking Code "${move_19.nr}" was not found in our database.
    ${move_20}    Tracking Code "${move_20.nr}" seems too long. We expect 6 characters here.
    ${move_27}    The move type is invalid
    ${move_33}    Only 10 Tracking Codes may be specified at once, there are 11 selected.
    ${move_36}    Sorry, but Tracking Code "TC000C" was not found in our database.
    ${move_37}    Sorry, but Tracking Code "TC000C" was not found in our database.    Sorry, but Tracking Code "TC000D" was not found in our database.

Test Invalid Cases
    [Template]    Post Return Error
    ${move_1}     Invalid "secid" length
    ${move_2}     This "secid" does not exist
    ${move_41}    Move date (2020-08-22 13:30:00+00) time can not be before GeoKret birth (2020-08-22 15:30:00+00)


Test Valid Cases
    [Template]    Post Move Valid
    ${move_9}     ${GEOKRETY_1}
    ${move_21}    ${GEOKRETY_1}
    ${move_22}    ${GEOKRETY_1}
    ${move_23}    ${GEOKRETY_1}
    ${move_24}    ${GEOKRETY_1}
    ${move_25}    ${GEOKRETY_1}
    ${move_26}    ${GEOKRETY_1}
    ${move_31}    ${GEOKRETY_1}    ${GEOKRETY_2}
    ${move_32}    ${GEOKRETY_1}    ${GEOKRETY_2}    ${GEOKRETY_3}    ${GEOKRETY_4}    ${GEOKRETY_5}    ${GEOKRETY_6}    ${GEOKRETY_7}    ${GEOKRETY_8}    ${GEOKRETY_9}    ${GEOKRETY_10}
    ${move_34}    ${GEOKRETY_1}
    ${move_35}    ${GEOKRETY_1}    ${GEOKRETY_2}
    ${move_42}    ${GEOKRETY_1}
    ${move_43}    ${GEOKRETY_1}
    ${move_44}    ${GEOKRETY_1}

*** Keywords ***

Seed
    Clear DB And Seed 1 users
    Seed 11 geokrety owned by 1
    Sign Out Fast


Check Error In XML List
  [Arguments]    ${root}   ${error}
    ${value} =                            Get Elements                ${root}         xpath=.//error[.='${error}']
    Length Should Be                      ${value}                    1


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


Post Move Valid
    [Arguments]    ${move}    @{geokrety}
    Create Session                          geokrety                    ${GK_URL}
    ${auth} =     GET On Session            geokrety                    url=/

    ${resp} =     POST On Session           geokrety                    url=/ruchy.php    data=&{move}
    Should Not Be Empty    ${resp.content}

    ${root} =     Parse XML                 ${resp.content}
    Should Be Equal                         ${root.tag}                 gkxml
    Response Should be XML Valid            ${root}                     @{geokrety}

    Delete All Sessions
