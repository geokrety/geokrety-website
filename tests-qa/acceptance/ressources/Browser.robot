*** Settings ***
Resource          BrowserStack.robot
Library           Collections
Variables         ../ressources/vars/browser_config.py

*** Variables ***

&{SELENOID_OPTIONS}    enableVideo=${TRUE}

*** Keywords ***

!Open GeoKrety Browser
    [Documentation]    Main entry function to open the browser
    Run Keyword If    "${BROWSER}" == "firefox"
    ...        Open GeoKrety Browser Firefox    remote_url=${REMOTE_URL}
    # ...    ELSE IF    "${BROWSER}" == "chrome"
    # ...        Open GeoKrety Browser Chrome
    ...    ELSE       Fatal Error    Invalid browser name "${BROWSER}"
    Set Window Size             1280    1024

# See https://spage.fi/headless-selenium-rf
Open GeoKrety Browser Firefox
    [Tags]    robot:private
    [Arguments]    ${remote_url}=None
    Log       Open Browser Firefox

    # # This is the equivalent Python code
    # from selenium import webdriver
    # firefox_options = webdriver.firefox.options.Options()
    ${firefox_options} =     Evaluate
    ...    sys.modules['selenium.webdriver'].firefox.webdriver.Options()
    ...    sys, selenium.webdriver

    Call Method    ${firefox_options}   set_preference    timezone    Africa/Nairobi
    Call Method    ${firefox_options}   set_preference    strictFileInteractability    ${FALSE}
    Call Method    ${firefox_options}   set_capability    strictFileInteractability    ${FALSE}

    # Enable video recording when using selenoid
    Run Keyword If      ${RECORDING_ENABLED}
    ...                 Call Method    ${firefox_options}   set_capability    selenoid:options    ${SELENOID_OPTIONS}

    Run Keyword If      ${HEADLESS}
    ...                 Call Method    ${firefox_options}   add_argument    -headless

    Open Browser    browser=Firefox    options=${firefox_options}
    ...    remote_url=${remote_url}

# Open GeoKrety Browser Chrome
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
