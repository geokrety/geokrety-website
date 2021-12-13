*** Settings ***
Resource        ../functions/FunctionsGlobal.robot
Resource        ../vars/users.resource
Force Tags      Users Details    Language

*** Test Cases ***

Language should be changed immediately
    [Tags]    TODO
    Clear DB And Seed 1 users
    Sign In ${USER_1.name} Fast
    Go To User 1 url
    Element Should Contain                  ${NAVBAR_MOVE_LINK}                 Log a GeoKret
    Click Button                            ${USER_PROFILE_LANGUAGE_EDIT_BUTTON}
    Wait Until Modal                        Choose your preferred language
    Select From List By Value               ${USER_LANGUAGE_LANGUAGE_SELECT}   fr
    Click Button                            ${MODAL_DIALOG_SUBMIT_BUTTON}
    # TODO: The message shown should be localized
    Flash message shown                     Préférences de langue mises à jour.
    Location Should Be                      ${PAGE_USER_1_PROFILE_URL_FR}
    Element Should Contain                  ${NAVBAR_MOVE_LINK}                  Déplacer un GeoKret

Language should be changed on sign in
    [Tags]    TODO
    Clear DB And Seed 2 users
    Sign Out Fast
    Go To Url                               ${PAGE_HOME_URL}
    Sign In User                            ${USER_2.name}
    Location Should Be                      ${PAGE_HOME_URL_FR}
    # TODO: The message shown should be localized
    Flash message shown                     Bienvenue à bord !

Language should be changed on root domain access
    [Tags]    TODO
    Clear DB And Seed 2 users
    Sign Out Fast
    Sign In ${USER_2.name} Fast
    Go To Url                               ${GK_URL}
    # TODO: Url should be redirected
    Location Should Be                      ${PAGE_HOME_URL_FR}
    Flash message shown                     Bienvenue à bord !
