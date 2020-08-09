*** Settings ***
Resource        FunctionsGlobal.robot

*** Keywords ***
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

!V2 Enter TrackingCode
    [Arguments]    ${code}
    Wait Until Element Is Visible  ${HOME_FOUND_GK_TRACKING_CODE_INPUT}
    Input Text                     ${HOME_FOUND_GK_TRACKING_CODE_INPUT}  ${code}
    Simulate Event                 ${HOME_FOUND_GK_TRACKING_CODE_INPUT}  blur

!V2 Click On FoundGeokretLogIt
    Wait Until Element Is Visible  ${HOME_FOUND_GK_TRACKING_CODE_BUTTON}
    Click Button                   ${HOME_FOUND_GK_TRACKING_CODE_BUTTON}
