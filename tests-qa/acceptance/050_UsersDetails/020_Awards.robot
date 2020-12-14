*** Settings ***
Library         SeleniumLibrary  timeout=10  implicit_wait=0
Library         DependencyLibrary
Resource        ../functions/PageGeoKretyCreate.robot
Resource        ../vars/users.resource
Resource        ../vars/geokrety.resource
Force Tags      Users Details
Suite Setup     Seed

*** Test Cases ***

Panel should be present
    Go To User 1 url
    Page Should Contain Element     ${USER_PROFILE_AWARDS_PANEL}
    Go To User 2 url
    Page Should Contain Element     ${USER_PROFILE_AWARDS_PANEL}

Stats present
    Check created awards counters    ${PAGE_USER_1_PROFILE_URL}    ${USER_1.name}

Stats are per user
    Seed 1 geokrety owned by 2
    Check created awards counters    ${PAGE_USER_2_PROFILE_URL}    ${USER_2.name}    1    1
    Check created awards counters    ${PAGE_USER_1_PROFILE_URL}    ${USER_1.name}    0    0

Stat created incremented
    [Timeout]    600
    Seed 1 geokrety owned by 1
    Check created awards counters    ${PAGE_USER_1_PROFILE_URL}    ${USER_1.name}    1    1

    Seed 1 geokrety owned by 1
    Check created awards counters    ${PAGE_USER_1_PROFILE_URL}    ${USER_1.name}    2    1

    Seed 8 geokrety owned by 1
    Check created awards counters    ${PAGE_USER_1_PROFILE_URL}    ${USER_1.name}    10    2

    Seed 9 geokrety owned by 1
    Check created awards counters    ${PAGE_USER_1_PROFILE_URL}    ${USER_1.name}    19    2
    Seed 1 geokrety owned by 1
    Check created awards counters    ${PAGE_USER_1_PROFILE_URL}    ${USER_1.name}    20    3

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

Seed
    Clear Database
    Seed 2 users
    Sign Out Fast

Check created awards counters
    [Arguments]    ${url}    ${username}    ${count}=0    ${countAwards}=0    ${distance}=0
    Go To url           ${url}
    User ${username} created ${count} on distance ${distance}
    User ${username} moved 0 on distance 0
    Has ${countAwards} created awards
    Has 0 moved awards

User ${username} created ${count1} on distance ${count2}
    Element Should Contain          ${USER_PROFILE_AWARDS_PANEL}    ${username} has created ${count1} GeoKrety, which travelled ${count2} km.

User ${username} moved ${count1} on distance ${count2}
    Element Should Contain          ${USER_PROFILE_AWARDS_PANEL}    ${username} has moved ${count1} GeoKrety on a total distance of ${count2} km.

Has ${count} created awards
    ${total} =    Get Element Count    ${USER_PROFILE_GAINED_CREATED_AWARDS}/img
    Should Be Equal As Integers    ${count}    ${total}

Has ${count} moved awards
    ${total} =    Get Element Count    ${USER_PROFILE_GAINED_MOVED_AWARDS}/img
    Should Be Equal As Integers    ${count}    ${total}
