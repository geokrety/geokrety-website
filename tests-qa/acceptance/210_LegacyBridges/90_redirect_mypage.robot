*** Settings ***
Library         RequestsLibrary
Resource        ../ressources/Authentication.robot
Resource        ../ressources/vars/Urls.robot
Variables       ../ressources/vars/users.yml
Suite Setup     Suite Setup

*** Test Cases ***

Anonymous access to mypage redirects to home
    Go To Url                             ${GK_URL}/mypage.php    redirect=${PAGE_HOME_URL_EN}

Authenticated access to mypage redirects to user details
    Sign In User                          ${USER_1.name}
    Go To Url                             ${GK_URL}/mypage.php    redirect=${PAGE_USER_1_PROFILE_URL}

Can acces any user page
    Go To Url                             url=${GK_URL}/mypage.php?userid=2         redirect=${PAGE_USER_2_PROFILE_URL}

User page details - param 0
    Go To Url                             url=${GK_URL}/mypage.php?userid=1&co=0    redirect=${PAGE_USER_1_PROFILE_URL}

User page details - param 1
    ${url} =    Get Url With Param        ${PAGE_USER_OWNED_GEOKRETY_URL}           userid=1
    Go To Url                             url=${GK_URL}/mypage.php?userid=1&co=1    redirect=${url}

User page details - param 2
    ${url} =    Get Url With Param        ${PAGE_USER_WATCHED_GEOKRETY_URL}         userid=1
    Go To Url                             url=${GK_URL}/mypage.php?userid=1&co=2    redirect=${url}

User page details - param 3
    ${url} =    Get Url With Param        ${PAGE_USER_RECENT_MOVES_URL}             userid=1
    Go To Url                             url=${GK_URL}/mypage.php?userid=1&co=3    redirect=${url}

User page details - param 4
    ${url} =    Get Url With Param        ${PAGE_USER_OWNED_GEOKRETY_RECENT_MOVES_URL}    userid=1
    Go To Url                             url=${GK_URL}/mypage.php?userid=1&co=4    redirect=${url}

User page details - param 5
    ${url} =    Get Url With Param        ${PAGE_USER_INVENTORY_URL}           userid=1
    Go To Url                             url=${GK_URL}/mypage.php?userid=1&co=5    redirect=${url}


*** Keywords ***

Suite Setup
    Clear Database And Seed ${2} users
    Sign Out Fast
