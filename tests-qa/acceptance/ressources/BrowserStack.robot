*** Settings ***
Documentation     Robot Stack
Library           SeleniumLibrary  timeout=10  implicit_wait=0

*** Variables ***
${PROJECT_NAME}          GeoKrety
${BS_ENABLED}            ${False}
${BS_LOCAL}              ${False}
${BS_LOCAL_ID}           ${EMPTY}
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
    Run Keyword If    "${browser}"=="Firefox"    !Open GeoKrety BrowserStack Windows10 Firefox
    Run Keyword If    "${browser}"=="Chrome"     !Open GeoKrety BrowserStack Windows10 Chrome

!Open GeoKrety BrowserStack Windows10 Firefox
    [Tags]    robot:private
    Log    Open Browserstack Windows10 Firefox
    Log    ${DC_Win10Firefox}
    Open Browser    ${BROWSER_START_PAGE}    remote_url=${BS_HUB}  desired_capabilities=${DC_Win10Firefox}

!Open GeoKrety BrowserStack Windows10 Chrome
    [Tags]    robot:private
    Log    ${DC_Win10Chrome}
    Log    Open Browserstack Windows10 Chrome
    Open Browser    ${BROWSER_START_PAGE}    remote_url=${BS_HUB}  desired_capabilities=${DC_Win10Chrome}
