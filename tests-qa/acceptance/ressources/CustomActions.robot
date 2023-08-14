*** Settings ***
Resource        FunctionsGlobal.robot
Library         SeleniumLibrary  timeout=10  implicit_wait=0

*** Variables ***

${MODAL_DIALOG}                     //div[@class="modal-dialog"]
${MODAL_DIALOG_TITLE}               ${MODAL_DIALOG}//*[@id="modalLabel"]
${MODAL_DIALOG_SUBMIT_BUTTON}       ${MODAL_DIALOG}//div[contains(@class, "modal-footer")]/button[@type="submit"]
${MODAL_DIALOG_DISMISS_BUTTON}      ${MODAL_DIALOG}//div[contains(@class, "modal-footer")]/button[@data-dismiss="modal"]

${MODAL_PANEL}                      //div[contains(@class, "panel")]
${MODAL_PANEL_TITLE}                ${MODAL_PANEL}//*[@id="modalLabel"]
${MODAL_PANEL_SUBMIT_BUTTON}        ${MODAL_PANEL}//div[contains(@class, "modal-footer")]/button[@type="submit"]
${MODAL_PANEL_DISMISS_BUTTON}       ${MODAL_PANEL}//div[contains(@class, "modal-footer")]/button[@data-dismiss="modal"]

*** Keywords ***


# Shortcut to common pages

Page Navbar Is Present
    Page Should Contain Element       //body/nav

Page WaitForPageElement
    [Arguments]    ${element}
    Wait Until Page Contains Element  ${element}

Click Link With Text
    [Arguments]    ${text}
    Click Link                     //a[contains(text(),"${text}")]

Textfield Should Not Contain
    [Arguments]    ${locator}    ${not_expected}
    ${text} =    Get Value    ${locator}
    Should Not Be Equal As Strings    ${not_expected}    ${text}

Click Element With Param
    [Arguments]    ${url}    &{params}
    ${url_} =       Replace Variables    ${url}
    Click Element   ${url_}

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
    [Arguments]  ${element}     ${timeout}=3
    Wait until page contains element    ${element}\[contains(@class, "panel-success")]    timeout=${timeout}
    # Wait until page contains element    ${element}/ancestor::div[contains(@class, "panel") and contains(@class, "panel-success")]    timeout=2

Panel validation has error
    [Arguments]  ${element}     ${timeout}=3
    Wait until page contains element    ${element}\[contains(@class, "panel-danger")]    timeout=${timeout}
    # Wait until page contains element    ${element}/ancestor::div[contains(@class, "panel") and contains(@class, "panel-danger")]    timeout=2

Panel Is Collapsed
    [Arguments]  ${element}     ${timeout}=3
    Wait until page contains element    ${element}/div[contains(@class, "panel-heading") and contains(@class, "collapsed")]    timeout=${timeout}

Panel Is Open
    [Arguments]  ${element}     ${timeout}=3
    Wait until page contains element    ${element}/div[contains(@class, "panel-heading") and not(contains(@class, "collapsed"))]    timeout=${timeout}

Open Panel
    [Arguments]                     ${element}
    ${status}    ${value} =         Run Keyword And Ignore Error    Panel Is Collapsed    ${element}     timeout=1
    Run Keyword If                  '${status}' == 'PASS'
    ...                             Click Element                   ${element}/div[contains(@class, "panel-heading")]
    Panel Is Open                   ${element}

Flash message shown
    [Arguments]  ${message}
    Page WithoutWarningOrFailure
    Wait until page contains element    //div[contains(@class, "flash-message") and text()[contains(., "${message}")]]


Scroll Into View
    [Arguments]    ${element}
    Execute Javascript                      document.evaluate('${element}', document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue.scrollIntoView({ behavior: 'auto', block: 'center', inline: 'center' });

Wait Until Modal
    [Arguments]    ${title}
    Page WithoutWarningOrFailure
    Wait Until Page Contains Element        ${MODAL_DIALOG}
    Wait Until Page Contains Element        ${MODAL_DIALOG_TITLE}
    Wait Until Element Is Visible           ${MODAL_DIALOG}
    Wait Until Element Is Visible           ${MODAL_DIALOG_TITLE}
    Scroll Into View                        ${MODAL_DIALOG}
    # Wait Until Page Contains Element        ${MODAL_DIALOG_SUBMIT_BUTTON}
    # Wait Until Page Contains Element        ${MODAL_DIALOG_DISMISS_BUTTON}
    Element Should Contain                  ${MODAL_DIALOG_TITLE}               ${title}

Wait Until Modal Close
    Wait Until Element Is Not Visible       ${MODAL_DIALOG}

Wait Until Panel
    [Arguments]    ${title}
    Page WithoutWarningOrFailure
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

Element Attribute Should Be
    [Arguments]    ${element}    ${attribute}    ${expect}
    ${attr} =    Get Element Attribute      ${element}      ${attribute}
    Should Be Equal As Strings              ${attr}         ${expect}

Wait For Text To Not Appear
    [Arguments]    ${expect}    ${timeout}=1
    Page WithoutWarningOrFailure
    Run Keyword And Expect Error    not seen    Wait Until Page Contains    ${expect}    timeout=${timeout}    error=not seen

Input Value Should Be
    [Arguments]    ${element}    ${expect}
    ${value} = 	Get Value           ${element}
    Should Be Equal As Strings      ${value}    ${expect}
