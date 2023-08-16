*** Settings ***
Library         RequestsLibrary
Resource        ../../ressources/Authentication.robot
Variables       ../../ressources/vars/users.yml
Suite Setup     Suite Setup
Test Setup      Test Setup
Force Tags      Awards

*** Test Cases ***

Panel should be present
    Go To User ${USER_1.id}
    Page Should Contain Element     ${USER_PROFILE_AWARDS_PANEL}
    Go To User ${USER_2.id}
    Page Should Contain Element     ${USER_PROFILE_AWARDS_PANEL}

Stats present
    Check created awards counters    ${PAGE_USER_1_PROFILE_URL}    ${USER_1.name}

Stats are per user
    Seed 1 geokrety owned by ${USER_2.id}
    Check created awards counters    ${PAGE_USER_2_PROFILE_URL}    ${USER_2.name}    1    1
    Check created awards counters    ${PAGE_USER_1_PROFILE_URL}    ${USER_1.name}    0    0

Stat created incremented
    Seed ${1} geokrety owned by ${USER_1.id}
    Check created awards counters    ${PAGE_USER_1_PROFILE_URL}    ${USER_1.name}    1    1

    Seed ${1} geokrety owned by ${USER_1.id}
    Check created awards counters    ${PAGE_USER_1_PROFILE_URL}    ${USER_1.name}    2    1

    Seed ${8} geokrety owned by ${USER_1.id}
    Check created awards counters    ${PAGE_USER_1_PROFILE_URL}    ${USER_1.name}    10    2

    Seed ${9} geokrety owned by ${USER_1.id}
    Check created awards counters    ${PAGE_USER_1_PROFILE_URL}    ${USER_1.name}    19    2
    Seed ${1} geokrety owned by ${USER_1.id}
    Check created awards counters    ${PAGE_USER_1_PROFILE_URL}    ${USER_1.name}    20    3

    # Workaround a latency issue
    Sleep    1

    #### Disabled as it takes really long time
    # Seed 30 geokrety owned by 1
    # Check created awards counters    ${PAGE_USER_1_PROFILE_URL}    ${USER_1.name}    50    4
    #
    # Seed 50 geokrety owned by 1
    # Check created awards counters    ${PAGE_USER_1_PROFILE_URL}    ${USER_1.name}    100    5
    #
    # Seed 20 geokrety owned by 1
    # Check created awards counters    ${PAGE_USER_1_PROFILE_URL}    ${USER_1.name}    120    6
    #
    # Seed 80 geokrety owned by 1
    # Check created awards counters    ${PAGE_USER_1_PROFILE_URL}    ${USER_1.name}    200    7
    #
    # Seed 114 geokrety owned by 1
    # Check created awards counters    ${PAGE_USER_1_PROFILE_URL}    ${USER_1.name}    314    8
    #
    # Seed 186 geokrety owned by 1
    # Check created awards counters    ${PAGE_USER_1_PROFILE_URL}    ${USER_1.name}    500    9
    #
    # Seed 12 geokrety owned by 1
    # Check created awards counters    ${PAGE_USER_1_PROFILE_URL}    ${USER_1.name}    512    10
    #
    # Seed 208 geokrety owned by 1
    # Check created awards counters    ${PAGE_USER_1_PROFILE_URL}    ${USER_1.name}    720    11
    #
    # Seed 80 geokrety owned by 1
    # Check created awards counters    ${PAGE_USER_1_PROFILE_URL}    ${USER_1.name}    800    12
    #
    # # Seed 200 geokrety owned by 1
    # # Check created awards counters    ${PAGE_USER_1_PROFILE_URL}    ${USER_1.name}    1000    13
    # #
    # # Seed 24 geokrety owned by 1
    # # Check created awards counters    ${PAGE_USER_1_PROFILE_URL}    ${USER_1.name}    1024    14
    # #
    # # Seed 500 geokrety owned by 1
    # # Seed 476 geokrety owned by 1
    # # Check created awards counters    ${PAGE_USER_1_PROFILE_URL}    ${USER_1.name}    2000    15
    # #
    # # Seed 500 geokrety owned by 1
    # # Seed 500 geokrety owned by 1
    # # Check created awards counters    ${PAGE_USER_1_PROFILE_URL}    ${USER_1.name}    3000    16
    # #
    # # Seed 500 geokrety owned by 1
    # # Seed 500 geokrety owned by 1
    # # Seed 500 geokrety owned by 1
    # # Seed 500 geokrety owned by 1
    # # Check created awards counters    ${PAGE_USER_1_PROFILE_URL}    ${USER_1.name}    5000    17
    # #
    # # Seed 40 geokrety owned by 1
    # # Check created awards counters    ${PAGE_USER_1_PROFILE_URL}    ${USER_1.name}    5040    18
    # #
    # # Seed 500 geokrety owned by 1
    # # Seed 500 geokrety owned by 1
    # # Seed 500 geokrety owned by 1
    # # Seed 500 geokrety owned by 1
    # # Seed 500 geokrety owned by 1
    # # Seed 500 geokrety owned by 1
    # # Seed 500 geokrety owned by 1
    # # Seed 500 geokrety owned by 1
    # # Seed 500 geokrety owned by 1
    # # Seed 460 geokrety owned by 1
    # # Check created awards counters    ${PAGE_USER_1_PROFILE_URL}    ${USER_1.name}    10000    19


*** Keywords ***

Suite Setup
    Clear Database And Seed ${2} users

Test Setup
    Sign Out Fast

Check created awards counters
    [Arguments]    ${url}    ${username}    ${count}=0    ${countAwards}=0    ${distance}=0
    Go To url           ${url}
    User ${username} created ${count} on distance ${distance}
    User ${username} moved ${0} on distance ${0}
    Has ${countAwards} created awards
    Has 0 moved awards

User ${username} created ${count} on distance ${distance}
    Element Should Contain          ${USER_PROFILE_AWARDS_PANEL}    ${username} has created ${count} GeoKrety, which travelled ${distance} km.

User ${username} moved ${count} on distance ${distance}
    Element Should Contain          ${USER_PROFILE_AWARDS_PANEL}    ${username} has moved ${count} GeoKrety on a total distance of ${distance} km.

Has ${count} created awards
    ${total} =    Get Element Count    ${USER_PROFILE_GAINED_CREATED_AWARDS}/img
    Should Be Equal As Integers    ${count}    ${total}

Has ${count} moved awards
    ${total} =    Get Element Count    ${USER_PROFILE_GAINED_MOVED_AWARDS}/img
    Should Be Equal As Integers    ${count}    ${total}
