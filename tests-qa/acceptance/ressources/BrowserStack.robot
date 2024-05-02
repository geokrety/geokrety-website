*** Settings ***
Documentation     Robot Stack
Library           libraries/Browser.py  timeout=10  implicit_wait=0

*** Variables ***
${PROJECT_NAME}          GeoKrety
${RESOLUTION}            1280x1024
${BS_CONFIG}             browserstack.local:${BS_LOCAL},browserstack.localIdentifier:${BS_LOCAL_ID},project:${PROJECT_NAME},browserstack.autoWait:0,browserstack.resolution:${RESOLUTION}
${BROWSER_START_PAGE}    about:blank


##############
# BrowserStack desired capabilities
# https://www.browserstack.com/automate/capabilities
##############
# Use case: galaxy tab
## Capture Page Screenshot' could not be run
${DC_GalaxyTab}	     "os_version:8.1,device:Samsung Galaxy Tab S4,real_mobile:true,${BS_CONFIG}"
# Use case: windows 10 and chrome
${DC_Win10Chrome}	 "os:Windows,os_version:10,browser:Chrome,browser_version:84.0,${BS_CONFIG}"
# Use case: windows 10 and firefox
${DC_Win10Firefox}	 "os:Windows,os_version:10,browser:Firefox,browser_version:79.0,${BS_CONFIG}"

*** Keywords ***

!Open GeoKrety BrowserStack
    [Arguments]    ${browser}
    Run Keyword If    "${browser}"=="firefox"    !Open GeoKrety BrowserStack Windows10 Firefox
    Run Keyword If    "${browser}"=="chrome"     !Open GeoKrety BrowserStack Windows10 Chrome

!Open GeoKrety BrowserStack Windows10 Firefox
    [Tags]    robot:private
    Log    Open Browserstack Windows10 Firefox
    Log    ${DC_Win10Firefox}

    ${firefox_options} =     Evaluate
    ...    sys.modules['selenium.webdriver'].firefox.webdriver.Options()
    ...    sys, selenium.webdriver

    Call Method    ${firefox_options}   set_preference    timezone    Africa/Nairobi
    Call Method    ${firefox_options}   set_preference    strictFileInteractability    ${FALSE}
    Call Method    ${firefox_options}   set_capability    strictFileInteractability    ${FALSE}
    Call Method    ${firefox_options}   set_capability    os    Windows
    Call Method    ${firefox_options}   set_capability    os_version    10
    Call Method    ${firefox_options}   set_capability    browser_version    latest
    Call Method    ${firefox_options}   set_capability    browserstack.local    ${True}
    Call Method    ${firefox_options}   set_capability    browserstack.localIdentifier    ${BS_LOCAL_ID}
    Call Method    ${firefox_options}   set_capability    project    ${PROJECT_NAME}
    Call Method    ${firefox_options}   set_capability    browserstack.autoWait    ${0}
    Call Method    ${firefox_options}   set_capability    browserstack.resolution    ${RESOLUTION}
    Call Method    ${firefox_options}   set_capability    buildName    QA-tests

    Open Browser    ${BROWSER_START_PAGE}    remote_url=${REMOTE_URL}  options=${firefox_options}

!Open GeoKrety BrowserStack Windows10 Chrome
    [Tags]    robot:private
    Log    ${DC_Win10Chrome}
    Log    Open Browserstack Windows10 Chrome
    Open Browser    ${BROWSER_START_PAGE}    remote_url=${REMOTE_URL}  desired_capabilities=${DC_Win10Chrome}
