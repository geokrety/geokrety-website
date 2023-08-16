*** Settings ***
Library         RequestsLibrary
Resource        ../../ressources/Authentication.robot
Variables       ../../ressources/vars/users.yml

*** Test Cases ***

# Language should be changed immediately
#     Clear Database And Seed ${1} users
#     Sign In ${USER_1.name} Fast

#     Go To User ${1}
#     Element Should Contain                  ${NAVBAR_MOVE_LINK}                 Log a GeoKret
#     Click Button                            ${USER_PROFILE_LANGUAGE_EDIT_BUTTON}
#     Wait Until Modal                        Choose your preferred language
#     Select From List By Value               ${USER_LANGUAGE_LANGUAGE_SELECT}   fr
#     Click Button                            ${MODAL_DIALOG_SUBMIT_BUTTON}
#     # TODO: The message shown should be localized
#     Flash message shown                     Préférences de langue mises à jour.
#     Location Should Be                      ${PAGE_USER_1_PROFILE_URL_FR}
#     Element Should Contain                  ${NAVBAR_MOVE_LINK}                  Déplacer un GeoKret

# Language should be changed on sign in
#     [Tags]    TODO
#     Clear Database
#     Register User                           ${USER_4}
#     Sign Out Fast

#     Sign In User                            ${USER_4.name}
#     # TODO: Should be redirected to fr
#     Location Should Be                      ${PAGE_USER_1_PROFILE_URL_FR}
#     Flash message shown                     Suivi d'objets Open Source pour toutes les plateformes de géocaching

# Language should be changed on root domain access
#     Clear Database
#     Register User                           ${USER_4}
#     Sign Out Fast

#     Sign In User                            ${USER_4.name}
#     # TODO: Should be redirected to fr
#     Go To Url                               ${GK_URL}    redirect=${PAGE_USER_1_PROFILE_URL_FR}
#     Flash message shown                     Bienvenue à bord !

Language should be from a valid list
    Clear Database And Seed ${2} users

    ${data} =         Evaluate              {'language': 'hh'}
    Create Session                          gk         ${GK_URL}
    ${auth} =         GET On Session        gk         /devel/users/${USER_1.name}/login
    ${resp} =         POST On Session       gk         url=${PAGE_USER_CHANGE_LANGUAGE_URL}?skip_csrf=True
    ...                                                data=${data}
    ...                                                expected_status=200
    ${body} =         Convert To String     ${resp.content}
    Should Contain                          ${body}    This language is not supported
    Delete All Sessions
