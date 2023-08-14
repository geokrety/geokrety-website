*** Settings ***
Resource        ../ressources/Database.robot
Resource        ../ressources/vars/pages/Home.robot

*** Variables ***

${DROPDOWN_LANG}     //*[@id="navbar-lang"]
${DROPDOWN_LANG_EN}  //*[@id="navbar-lang-en"]
${DROPDOWN_LANG_FR}  //*[@id="navbar-lang-fr"]

*** Test Cases ***
Welcome: (EN)
    [Documentation]    Default english welcome page
    Go To Home
    Page WaitForFooterHome
    Welcome ShouldShow WelcomeToGeokrety
    Welcome ShouldShow SomeStatistics
    Welcome ShouldShow FoundGeokretLogIt
    # Welcome ShouldShow LatestMoves
    # Welcome ShouldShow RecentPictures
    # Welcome ShouldShow RecentlyCreatedGK
    Page ShouldShow FooterElements

Welcome: (FR)
    [Documentation]    Welcome page in french
    Go To Home
    Click On FR Lang
    Page WaitForFooterHome
    Welcome ShouldShow WelcomeToGeokretyFR
    Welcome ShouldShow SomeStatisticsFR
    Welcome ShouldShow FoundGeokretLogItFR
    # Welcome ShouldShow LatestMovesFR
    # Welcome ShouldShow RecentPicturesFR
    # Welcome ShouldShow RecentlyCreatedGKFR
    Page ShouldShow FooterElements

*** Keywords ***

Click On FR Lang
    Wait Until Element Is Visible  ${DROPDOWN_LANG}
    Click Element                  ${DROPDOWN_LANG}
    Wait Until Element Is Visible  ${DROPDOWN_LANG_FR}
    Click Element                  ${DROPDOWN_LANG_FR}
    Location Should Be             ${PAGE_HOME_URL_FR}?

Welcome ShouldShow WelcomeToGeokrety
  Page Should Contain   Welcome to GeoKrety.org!
Welcome ShouldShow SomeStatistics
  Page should contain   Some statistics
Welcome ShouldShow LatestMoves
  Page should contain   Latest moves
Welcome ShouldShow RecentPictures
  Page should contain   Recent pictures
Welcome ShouldShow RecentlyCreatedGK
  Page should contain   Recently created GeoKrety
Welcome ShouldShow FoundGeokretLogIt
  Page should contain   Found a GeoKret?
  Page should contain button    ${HOME_FOUND_GK_TRACKING_CODE_BUTTON}

Welcome ShouldShow WelcomeToGeokretyFR
  Page Should Contain   Bienvenue sur GeoKrety.org !
Welcome ShouldShow SomeStatisticsFR
  Page should contain   Quelques statistiques
Welcome ShouldShow LatestMovesFR
  Page should contain   Derniers mouvements
Welcome ShouldShow RecentPicturesFR
  Page should contain   Photos récentes
Welcome ShouldShow RecentlyCreatedGKFR
  Page should contain   GeoKrety créés récemment
Welcome ShouldShow FoundGeokretLogItFR
  Page should contain   Vous avez trouvé un GeoKret ?
  Page should contain button    ${HOME_FOUND_GK_TRACKING_CODE_BUTTON}

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

Page WaitForFooterHome
    Wait Until Page Contains Element  ${FOOTER_HOME}
