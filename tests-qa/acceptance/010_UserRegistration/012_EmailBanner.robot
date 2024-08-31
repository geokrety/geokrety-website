*** Settings ***
Library         RequestsLibrary
Resource        ../ressources/Authentication.robot
Test Setup      Test Setup

*** Test Cases ***

Display agresive spam filter rule warning banner
    [Documentation]    Show a banner for o2/wp.pl domains
    Go To Url                             ${PAGE_REGISTER_URL}

    Input Text                            ${REGISTRATION_EMAIL_INPUT}                     geokrety@example.com
    Element Should Not Be Visible         ${REGISTRATION_BANNER_AGRESSIVE_SPAM_RULES}

    Input Text                            ${REGISTRATION_EMAIL_INPUT}                     geokrety@o2.pl
    Wait Until Element Is Visible         ${REGISTRATION_BANNER_AGRESSIVE_SPAM_RULES}

    Input Text                            ${REGISTRATION_EMAIL_INPUT}                     geokrety@o2.p
    Element Should Not Be Visible         ${REGISTRATION_BANNER_AGRESSIVE_SPAM_RULES}

    Input Text                            ${REGISTRATION_EMAIL_INPUT}                     geokrety@wp.pl
    Wait Until Element Is Visible         ${REGISTRATION_BANNER_AGRESSIVE_SPAM_RULES}

    Input Text                            ${REGISTRATION_EMAIL_INPUT}                     geokrety@o3.pl
    Element Should Not Be Visible         ${REGISTRATION_BANNER_AGRESSIVE_SPAM_RULES}


*** Keywords ***

Test Setup
    Clear Database
    Sign Out Fast
