*** Settings ***
Resource        ../functions/PageHome.robot
Force Tags      Home    Languages
Suite Setup     Clear Database

*** Test Cases ***
Welcome: (EN)
    [Documentation]    Default english welcome page
    [Tags]             EN
    !Go To GeoKrety
    Page WithoutWarningOrFailure
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
    [Tags]             FR
    !Go To GeoKrety
    !Click On FR Lang
    Page WithoutWarningOrFailure
    Page WaitForFooterHome
    Welcome ShouldShow WelcomeToGeokretyFR
    Welcome ShouldShow SomeStatisticsFR
    Welcome ShouldShow FoundGeokretLogItFR
    # Welcome ShouldShow LatestMovesFR
    # Welcome ShouldShow RecentPicturesFR
    # Welcome ShouldShow RecentlyCreatedGKFR
    Page ShouldShow FooterElements
