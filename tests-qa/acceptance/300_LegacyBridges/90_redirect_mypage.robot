*** Settings ***
Library         RequestsLibrary
Resource        ../functions/FunctionsGlobal.robot
Resource        ../vars/users.resource
Force Tags      Redirect    legacy    mypage
Suite Setup     Seed

*** Test Cases ***

Anonymous access to mypage redirects to home
    Go To Url                             ${GK_URL}/mypage.php
    Location Should Be                    ${PAGE_HOME_URL}

Authenticated access to mypage redirects to user details
    Sign In User                          ${USER_1.name}
    Go To Url                             ${GK_URL}/mypage.php
    Location Should Be                    ${PAGE_USER_1_PROFILE_URL}

Can acces any user page
    Go To Url                             url=${GK_URL}/mypage.php?userid=2
    Location With Param Should Be         ${PAGE_USER_X_PROFILE_URL}            userid=2

User page details - param 0
    Go To Url                             url=${GK_URL}/mypage.php?userid=1&co=0
    Location With Param Should Be         ${PAGE_USER_X_PROFILE_URL}            userid=1

User page details - param 1
    Go To Url                             url=${GK_URL}/mypage.php?userid=1&co=1
    Location With Param Should Be         ${PAGE_USER_OWNED_GEOKRETY_URL}       userid=1

User page details - param 2
    Go To Url                             url=${GK_URL}/mypage.php?userid=1&co=2
    Location With Param Should Be         ${PAGE_USER_WATCHED_GEOKRETY_URL}     userid=1

User page details - param 3
    Go To Url                             url=${GK_URL}/mypage.php?userid=1&co=3
    Location With Param Should Be         ${PAGE_USER_RECENT_MOVES_URL}         userid=1

User page details - param 4
    Go To Url                             url=${GK_URL}/mypage.php?userid=1&co=4
    Location With Param Should Be         ${PAGE_USER_OWNED_GEOKRETY_RECENT_MOVES_URL}        userid=1

User page details - param 5
    Go To Url                             url=${GK_URL}/mypage.php?userid=1&co=5
    Location With Param Should Be         ${PAGE_USER_INVENTORY_URL}            userid=1


*** Keywords ***

Seed
    Clear Database
    Seed 2 users
    Sign Out Fast
