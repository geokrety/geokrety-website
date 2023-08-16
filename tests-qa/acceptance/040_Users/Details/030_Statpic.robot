*** Settings ***
Library         RequestsLibrary
Resource        ../../ressources/Authentication.robot
Resource        ../../ressources/Moves.robot
Resource        ../../ressources/Pictures.robot
Variables       ../../ressources/vars/users.yml
Variables       ../../ressources/vars/moves.yml
Test Setup      Test Setup
Force Tags      Statpic

*** Test Cases ***

Statpic generated on user create
    Go To User ${USER_1.id}
    Check Image             ${USER_PROFILE_STATPIC_IMAGE}

Statpic updated on geokrety create
    Go To User ${USER_1.id}
    User ${USER_1.name} created ${0} on distance ${0}

    Seed ${1} geokrety owned by ${USER_1.id}

    Go To User ${USER_1.id}
    User ${USER_1.name} created ${1} on distance ${0}
    Check Image             ${USER_PROFILE_STATPIC_IMAGE}

Statpic updated on move create
    Seed ${1} geokrety owned by ${USER_1.id}
    Post Move Fast          &{MOVE_1}
    Post Move Fast          &{MOVE_6}

    Go To User ${USER_1.id}
    User ${USER_1.name} created ${1} on distance ${27}
    User ${USER_1.name} moved ${2} on distance ${27}

    Check Image             ${USER_PROFILE_STATPIC_IMAGE}

Statpic updated on move delete
    Seed ${1} geokrety owned by ${USER_1.id}
    Post Move Fast          &{MOVE_1}
    Post Move Fast          &{MOVE_6}
    Delete Move Fast        ${2}

    Go To User ${USER_1.id}
    User ${USER_1.name} created ${1} on distance ${0}
    User ${USER_1.name} moved ${1} on distance ${0}

    Check Image             ${USER_PROFILE_STATPIC_IMAGE}

# Todo check delete

*** Keywords ***

Test Setup
    Clear Database And Seed ${1} users
    Sign Out Fast

User ${username} created ${count} on distance ${distance}
    Element Should Contain          ${USER_PROFILE_AWARDS_PANEL}    ${username} has created ${count} GeoKrety, which travelled ${distance} km.

User ${username} moved ${count} on distance ${distance}
    Element Should Contain          ${USER_PROFILE_AWARDS_PANEL}    ${username} has moved ${count} GeoKrety on a total distance of ${distance} km.
