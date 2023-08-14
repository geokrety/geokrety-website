*** Settings ***
Resource          BrowserStack.robot
Library           Collections

*** Variables ***

${HEADLESS}              ${True}

*** Keywords ***

!Open GeoKrety Browser
    [Documentation]    Main entry function to open the browser
    [Arguments]        ${browser}
#    Run Keyword If     ${BS_ENABLED}    !Open GeoKrety BrowserStack     ${browser}
#    ...    ELSE                         !Open GeoKrety Browser Local    ${browser}
    !Open GeoKrety Browser Local    ${browser}
    Set Window Size             1280    1024

!Open GeoKrety Browser Local
    [Tags]    robot:private
    [Arguments]    ${browser}
    Run Keyword If    "${browser}"=="Firefox"    !Open GeoKrety Browser Firefox
#    ...    ELSE IF    "${browser}"=="Chrome"     !Open GeoKrety Browser Chrome
    ...    ELSE       Fatal Error                Invalid browser name

# See https://spage.fi/headless-selenium-rf
!Open GeoKrety Browser Firefox
    [Tags]    robot:private
    Log       Open Browser Firefox

    ${firefox_options} =     Evaluate    sys.modules['selenium.webdriver'].firefox.webdriver.Options()    sys, selenium.webdriver
    Run Keyword If      ${HEADLESS}
    ...                 Call Method    ${firefox_options}   add_argument    -headless
    ${dc}   Evaluate    sys.modules['selenium.webdriver'].DesiredCapabilities.FIREFOX  sys, selenium.webdriver
    Set To Dictionary   ${dc}      strictFileInteractability=${FALSE}
    Set To Dictionary   ${dc}      timezone=UTC-05:00
    Create Webdriver    Firefox    options=${firefox options}    desired_capabilities=${dc}

# !Open GeoKrety Browser Chrome
#     [Tags]    robot:private
#     Log    Open Browser Chrome
#     ${chrome_options} =     Evaluate    sys.modules['selenium.webdriver'].ChromeOptions()    sys, selenium.webdriver
#     Run Keyword If      ${HEADLESS}
#     ...                 Call Method    ${chrome_options}   add_argument    headless
#     Call Method    ${chrome_options}   add_argument    disable-gpu
#     Call Method    ${chrome_options}   add_argument    dns-prefetch-disable
#     Call Method    ${chrome_options}   add_argument    disable-web-security
#     Call Method    ${chrome_options}   add_argument    allow-running-insecure-content
#     Call Method    ${chrome_options}   add_argument    disable-browser-side-navigation
#     Create Webdriver    Chrome    chrome_options=${chrome_options}
