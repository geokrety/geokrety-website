*** Settings ***
Resource   ComponentsLocator.robot
Resource   Urls.robot
Library    SeleniumLibrary
Library    Collections
# doc: http://robotframework.org/Selenium2Library/Selenium2Library.html
#      http://robotframework.org/robotframework/latest/RobotFrameworkUserGuide.html

*** Variables ***
${RESOLUTION}        1280x1024
${BS_CONFIG}         browserstack.local:${BS_LOCAL},browserstack.localIdentifier:${BS_LOCAL_ID},project:${PROJECT_NAME},browserstack.autoWait:0,browserstack.resolution:${RESOLUTION}
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

Clear Database
    Go To Url               ${PAGE_DEV_RESET_DB_URL}
    Page Should Contain     OK

Global Setup
    !Open GeoKrety Browser  ${browser}    # which browser? the one that's the value of the variable

Global TearDown
    Close Browser

!Open GeoKrety Browser Local
    [Arguments]    ${browser}
    Run Keyword If    "${browser}"=="Firefox"    !Open GeoKrety Browser Firefox
    Run Keyword If    "${browser}"=="Chrome"     !Open GeoKrety Browser Chrome

# See https://spage.fi/headless-selenium-rf
!Open GeoKrety Browser Firefox
    Log    Open Browser Firefox
    ${firefox_options} =     Evaluate    sys.modules['selenium.webdriver'].firefox.webdriver.Options()    sys, selenium.webdriver
    Run Keyword If      ${HEADLESS}
    ...                 Call Method    ${firefox_options}   add_argument    -headless
    ${dc}   Evaluate    sys.modules['selenium.webdriver'].DesiredCapabilities.FIREFOX  sys, selenium.webdriver
    Set To Dictionary   ${dc}      strictFileInteractability=${FALSE}
    Set To Dictionary   ${dc}      timezone=UTC-05:00
    Create Webdriver    Firefox    firefox_options=${firefox options}    desired_capabilities=${dc}

!Open GeoKrety Browser Chrome
    Log    Open Browser Chrome
    ${chrome_options} =     Evaluate    sys.modules['selenium.webdriver'].ChromeOptions()    sys, selenium.webdriver
    Run Keyword If      ${HEADLESS}
    ...                 Call Method    ${chrome_options}   add_argument    headless
    Call Method    ${chrome_options}   add_argument    disable-gpu
    Call Method    ${chrome_options}   add_argument    dns-prefetch-disable
    Call Method    ${chrome_options}   add_argument    disable-web-security
    Call Method    ${chrome_options}   add_argument    allow-running-insecure-content
    Call Method    ${chrome_options}   add_argument    disable-browser-side-navigation
    Create Webdriver    Chrome    chrome_options=${chrome_options}

!Open GeoKrety BrowserStack
    [Arguments]    ${browser}
    Run Keyword If    "${browser}"=="Firefox"    !Open GeoKrety BrowserStack Windows10 Firefox
    Run Keyword If    "${browser}"=="Chrome"     !Open GeoKrety BrowserStack Windows10 Chrome

!Open GeoKrety BrowserStack Windows10 Firefox
    Log    Open Browserstack Windows10 Firefox
    Log    ${DC_Win10Firefox}
    Open Browser    ${BROWSER_START_PAGE}    remote_url=${BS_HUB}  desired_capabilities=${DC_Win10Firefox}

!Open GeoKrety BrowserStack Windows10 Chrome
    Log    ${DC_Win10Chrome}
    Log    Open Browserstack Windows10 Chrome
    Open Browser    ${BROWSER_START_PAGE}    remote_url=${BS_HUB}  desired_capabilities=${DC_Win10Chrome}

!Open GeoKrety Browser
    [Arguments]    ${browser}
    Run Keyword If    "${BS_ENABLED}" != "false"    !Open GeoKrety BrowserStack    ${browser}
    ...    ELSE       !Open GeoKrety Browser Local    ${browser}
    # Maximize Browser Window
    Set Window Size             1280    1024
    # Set Window Size            1919    1079
    # Set Selenium Timeout       30 s
    # Set Selenium Speed         0.45

!Go To GeoKrety
    Go To Url                  ${GK_URL}
    Location Should Contain    ${GK_URL}

Page WaitForPageElement
    [Arguments]    ${element}
    Wait Until Page Contains Element  ${element}

!Click On EN Lang
    Wait Until Element Is Visible  ${DROPDOWN_LANG}
    Click Element                  ${DROPDOWN_LANG}
    Wait Until Element Is Visible  ${DROPDOWN_LANG_EN}
    Click Element                  ${DROPDOWN_LANG_EN}
    Location Should Be             ${GK_URL}/en?

!Click On FR Lang
    Wait Until Element Is Visible  ${DROPDOWN_LANG}
    Click Element                  ${DROPDOWN_LANG}
    Wait Until Element Is Visible  ${DROPDOWN_LANG_FR}
    Click Element                  ${DROPDOWN_LANG_FR}
    Location Should Be             ${GK_URL}/fr?

Click Link With Text
    [Arguments]    ${text}
    Click Link                     //a[contains(text(),"${text}")]

Textfield Should Not Contain
    [Arguments]    ${locator}    ${not_expected}
    ${text} =    Get Value    ${locator}
    Should Not Be Equal As Strings    ${not_expected}    ${text}


Page WaitForFooterHome
    Wait Until Page Contains Element  ${FOOTER_HOME}

Go To Url
    [Arguments]    ${url}    &{params}
    Got To Url With Param    ${url}    &{params}
    Page WithoutWarningOrFailure

Got To Url With Param
    [Arguments]    ${url}    &{params}
    ${url_} =       Replace Variables    ${url}
    Go To           ${url_}

Go To User ${id} url
    Go To Url                       ${GK_URL}/en/users/${id}

Go To GeoKrety ${id} url
    Go To Url                       ${GK_URL}/en/geokrety/${id}

Location Should Not Be
    [Arguments]    ${url}
    Run Keyword And Expect Error    Location should have been '${url}' but was *    Location Should Be    ${url}

Location With Param Should Be
    [Arguments]    ${url}    &{params}
    ${url_} =       Replace Variables    ${url}
    Location Should Be    ${url_}

Click Element With Param
    [Arguments]    ${url}    &{params}
    ${url_} =       Replace Variables    ${url}
    Click Element   ${url_}

Page WithoutWarningOrFailure
    Page Should Not Contain  Warning:
    Page Should Not Contain  Failed
    Page Should Not Contain  Internal Server Error

Page ShouldShow FooterElements
    Wait Until Page Contains Element  ${FOOTER_HOME}
    Wait Until Page Contains Element  ${FOOTER_HELP}
    Wait Until Page Contains Element  ${FOOTER_NEWS}
    Wait Until Page Contains Element  ${FOOTER_CONTACT}
    Wait Until Page Contains Element  ${FOOTER_LICENSE}
    Wait Until Page Contains Element  ${FOOTER_FACEBOOK}
    Wait Until Page Contains Element  ${FOOTER_TWITTER}
    Wait Until Page Contains Element  ${FOOTER_INSTAGRAM}
    # TODO FOOTER_APPVERSION

Mailbox Should Contain ${count} Messages
    Element Text Should Be            ${NAVBAR_DEV_MAILBOX_COUNTER}     ${count}

Empty Dev Mailbox Fast
    Go To Url                         ${PAGE_DEV_MAILBOX_CLEAR_URL}

Empty Dev Mailbox
    Go To Url                         ${PAGE_DEV_MAILBOX_URL}
    Click Element                     ${DEV_MAILBOX_DELETE_ALL_MAILS_BUTTON}
    Element Text Should Be            ${NAVBAR_DEV_MAILBOX_COUNTER}     0

Delete First Mail in Mailbox
    Go To Url                         ${PAGE_DEV_MAILBOX_URL}
    Click Element                     ${DEV_MAILBOX_FIRST_MAIL_DELETE_LINK}

Delete Second Mail in Mailbox
    Go To Url                         ${PAGE_DEV_MAILBOX_URL}
    Click Element                     ${DEV_MAILBOX_SECOND_MAIL_DELETE_LINK}

Delete Mail ${mail_id} in Mailbox
    Go To Url                         ${PAGE_DEV_MAILBOX_URL}
    Click Element                     //*[@id="mailsTable"]/tbody/tr/td[@class="mail_id" and text()="${mail_id}"]/parent::tr//a[contains(@class, "deleteMailLink")]

Sign In ${username} Fast
    [Documentation]     Login user using special url (doesn't use login form)
    Go To Url                         ${PAGE_LOGIN_USER}    username=${username}
    Page Should Not Contain           Error signing in user

Sign Out Fast
    [Documentation]     Logout user using special url (doesn't use menu, no home page load)
    Go To Url                         ${GK_URL}/devel/users/logout

Sign In User
    [Arguments]     ${username}     ${password}=password
    Click Element                     ${NAVBAR_SIGN_IN_LINK}
    Wait Until Page Contains Element  ${MODAL_DIALOG}
    Wait Until Page Contains Element  ${SIGN_IN_FORM_SIGN_IN_BUTTON}
    Input Text                        ${SIGN_IN_FORM_USERNAME_INPUT}    ${username}
    Input Text                        ${SIGN_IN_FORM_PASSWORD_INPUT}    ${password}
    Click Button                      ${SIGN_IN_FORM_SIGN_IN_BUTTON}

Sign Out User
    Click Link                          ${NAVBAR_PROFILE_LINK}
    Click Link                          ${NAVBAR_SIGN_OUT_LINK}

Seed ${count} users
    Go To Url                           ${PAGE_SEED_USER}/${count}
    Empty Dev Mailbox Fast

Seed ${count} users without terms of use
    Go To Url                           ${PAGE_SEED_USER}/${count}/no-terms-of-use
    Empty Dev Mailbox Fast

Seed ${count} geokrety
    Go To Url                           ${PAGE_SEED_GEOKRETY}/${count}

Seed ${count} geokrety owned by ${userid}
    Go To Url                           ${GK_URL}/devel/db/users/${userid}/geokrety/seed/${count}

Seed ${count} waypoints OC
    Go To Url                           ${PAGE_SEED_WAYPOINT_OC}/${count}

Seed ${count} waypoints GC
    Go To Url                           ${PAGE_SEED_WAYPOINT_GC}/${count}

Clear DB And Seed ${count} users
    Clear Database
    Go To Url                           ${PAGE_SEED_USER}/${count}
    Empty Dev Mailbox Fast

Input validation has success
    [Arguments]  ${element}
    Wait until page contains element    ${element}/ancestor::div[contains(@class, "form-group") and contains(@class, "has-success")]   timeout=2

Input validation has error
    [Arguments]  ${element}
    Wait until page contains element    ${element}/ancestor::div[contains(@class, "form-group") and contains(@class, "has-error")]   timeout=2

Input validation has error help
    [Arguments]  ${element}    ${message}
    Wait Until Element Is Visible    //span[contains(@class, "help-block") and parent::div[.${element}]]    timeout=2
    Element Should Contain           //span[contains(@class, "help-block") and parent::div[.${element}]]    ${message}

Element should have class
    [Arguments]  ${element}  ${className}
    Wait until page contains element    ${element}\[contains(@class, "${className}")]

Element should not have class
    [Arguments]  ${element}  ${className}
    Page Should Not Contain Element    ${element}\[contains(@class, "${className}")]

Panel validation has success
    [Arguments]  ${element}
    Wait until page contains element    ${element}\[contains(@class, "panel-success")]    timeout=3
    # Wait until page contains element    ${element}/ancestor::div[contains(@class, "panel") and contains(@class, "panel-success")]    timeout=2

Panel validation has error
    [Arguments]  ${element}
    Wait until page contains element    ${element}\[contains(@class, "panel-danger")]    timeout=3
    # Wait until page contains element    ${element}/ancestor::div[contains(@class, "panel") and contains(@class, "panel-danger")]    timeout=2

Panel Is Collapsed
    [Arguments]  ${element}
    Page Should Contain Element         ${element}/div[contains(@class, "panel-heading") and contains(@class, "collapsed")]

Open Panel
    [Arguments]                     ${element}
    ${status}    ${value} =         Run Keyword And Ignore Error    Panel Is Collapsed    ${element}
    Run Keyword If                  '${status}' == 'PASS'
    ...                             Click Element                   ${element}/div[contains(@class, "panel-heading")]
    Page Should Contain Element     ${element}/div[contains(@class, "panel-heading") and not(contains(@class, "collapsed"))]

Flash message shown
    [Arguments]  ${message}
    Wait until page contains element    //div[contains(@class, "flash-message") and text()[contains(., "${message}")]]

Check Image
    [Arguments]    ${element}    ${name}=img1
    Open Eyes                               SeleniumLibrary  5
    Scroll Into View                        ${GK_LOGO_LINK}
    Scroll Into View                        ${element}
    Wait Until Element Is Visible           ${element}
    Capture Element                         ${element}    name=${name}
    Compare Images

Scroll Into View
    [Arguments]    ${element}
    Execute Javascript                      document.evaluate('${element}', document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue.scrollIntoView({ behavior: 'auto', block: 'center', inline: 'center' });

Wait Until Modal
    [Arguments]    ${title}
    Wait Until Page Contains Element        ${MODAL_DIALOG}
    Wait Until Page Contains Element        ${MODAL_DIALOG_TITLE}
    # Wait Until Page Contains Element        ${MODAL_DIALOG_SUBMIT_BUTTON}
    # Wait Until Page Contains Element        ${MODAL_DIALOG_DISMISS_BUTTON}
    Element Should Contain                  ${MODAL_DIALOG_TITLE}               ${title}

Wait Until Modal Close
    Wait Until Element Is Not Visible       ${MODAL_DIALOG}

Wait Until Panel
    [Arguments]    ${title}
    Wait Until Page Contains Element        ${MODAL_PANEL}
    Wait Until Page Contains Element        ${MODAL_PANEL_TITLE}
    # Wait Until Page Contains Element        ${MODAL_PANEL_SUBMIT_BUTTON}
    # Wait Until Page Contains Element        ${MODAL_PANEL_DISMISS_BUTTON}
    Element Should Contain                  ${MODAL_PANEL_TITLE}               ${title}

Element Count Should Be
    [Arguments]    ${element}    ${count}
    # ${count} = 	Get Element Count 	        ${element}
    # Should Be Equal As Integers             ${count}    ${expect}
    Page Should Contain Element             ${element}    limit=${count}

Wait For Text To Not Appear
    [Arguments]    ${expect}    ${timeout}=1
    Run Keyword And Expect Error    not seen    Wait Until Page Contains    ${expect}    timeout=${timeout}    error=not seen

Input Value Should Be
    [Arguments]    ${element}    ${expect}
    ${value} = 	Get Value           ${element}
    Should Be Equal As Strings      ${value}    ${expect}

Input Inscrybmde
    [Arguments]    ${element_id}    ${value}
    Execute Javascript              $("${element_id}").data('editor').value('${value}')

Inscrybmde To Textarea
    [Arguments]    ${element_id}
    Execute Javascript              $("${element_id}").data('editor').toTextArea()

Textarea To Inscrybmde
    [Arguments]    ${element_id}
    Execute Javascript              $("${element_id}").data('editor').toEditor()

Element Attribute Should Be
    [Arguments]    ${element}    ${attribute}    ${expect}
    ${attr} =    Get Element Attribute      ${element}      ${attribute}
    Should Be Equal As Strings              ${attr}         ${expect}
