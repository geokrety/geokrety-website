*** Settings ***
Library         RequestsLibrary
Resource        ../ressources/vars/Urls.robot
Variables       ../ressources/vars/geokrety.yml
Suite Setup     Suite Setup

*** Test Cases ***

Should Redirect To Form Url
    Go To Url                            url=${GK_URL}/m/${GEOKRETY_1.tc}    redirect=${PAGE_MOVES_URL}\\?tracking_code=${GEOKRETY_1.tc}

*** Keywords ***

Suite Setup
    Clear Database And Seed ${1} users
    Seed ${1} geokrety owned by ${1}
