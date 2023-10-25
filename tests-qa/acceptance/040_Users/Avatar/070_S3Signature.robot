*** Settings ***
Library         RequestsLibrary
Library         XML
Resource        ../../ressources/vars/Urls.robot
Resource        ../../ressources/Moves.robot
Variables       ../../ressources/vars/users.yml
Variables       ../../ressources/vars/moves.yml
Variables       ../../ressources/vars/geokrety.yml
Test Setup      Test Setup

*** Variables ***

@{ENDPOINTS_FOR_USER_1}    /api/v1/geokrety/${GEOKRETY_1.ref}/avatar/request-s3-file-signature
...                        /api/v1/moves/${1}/avatar/request-s3-file-signature
...                        /api/v1/users/${USER_1.id}/avatar/request-s3-file-signature
...
@{ENDPOINTS_FOR_USER_2}    /api/v1/geokrety/${GEOKRETY_2.ref}/avatar/request-s3-file-signature
...                        /api/v1/moves/${2}/avatar/request-s3-file-signature
...                        /api/v1/users/${USER_2.id}/avatar/request-s3-file-signature

&{SECID_NOTHING}
&{SECID_EMPTY}     secid=
&{SECID_USER_1}    secid=${USER_1.secid}
&{SECID_USER_2}    secid=${USER_2.secid}


*** Test Cases ***

Valid Parameters
    [Template]    Post Request Valid
    ${ENDPOINTS_FOR_USER_1}[0]    ${SECID_USER_1}
    ${ENDPOINTS_FOR_USER_1}[1]    ${SECID_USER_1}
    ${ENDPOINTS_FOR_USER_1}[2]    ${SECID_USER_1}

    ${ENDPOINTS_FOR_USER_2}[0]    ${SECID_USER_2}
    ${ENDPOINTS_FOR_USER_2}[1]    ${SECID_USER_2}
    ${ENDPOINTS_FOR_USER_2}[2]    ${SECID_USER_2}


Required Parameters
    [Template]    Post Return Error
    ${ENDPOINTS_FOR_USER_1}[0]    ${SECID_NOTHING}     200    Invalid "secid" length
    ${ENDPOINTS_FOR_USER_1}[1]    ${SECID_NOTHING}     200    Invalid "secid" length
    ${ENDPOINTS_FOR_USER_1}[2]    ${SECID_NOTHING}     200    Invalid "secid" length

    ${ENDPOINTS_FOR_USER_1}[0]    ${SECID_EMPTY}       200    Invalid "secid" length
    ${ENDPOINTS_FOR_USER_1}[1]    ${SECID_EMPTY}       200    Invalid "secid" length
    ${ENDPOINTS_FOR_USER_1}[2]    ${SECID_EMPTY}       200    Invalid "secid" length

    ${ENDPOINTS_FOR_USER_1}[0]    ${SECID_USER_2}      403    You are not the GeoKret owner
    ${ENDPOINTS_FOR_USER_1}[1]    ${SECID_USER_2}      403    You have no write permission on this move
    ${ENDPOINTS_FOR_USER_1}[2]    ${SECID_USER_2}      403    This is not your profile

    ${ENDPOINTS_FOR_USER_2}[0]    ${SECID_USER_1}      403    You are not the GeoKret owner
    ${ENDPOINTS_FOR_USER_2}[1]    ${SECID_USER_1}      403    You have no write permission on this move
    ${ENDPOINTS_FOR_USER_2}[2]    ${SECID_USER_1}      403    This is not your profile

*** Keywords ***

Test Setup
    Clear Database And Seed ${2} users
    Seed ${1} geokrety owned by ${1}
    Seed ${1} geokrety owned by ${2}
    Post move    ${MOVE_1}
    Post move    ${MOVE_41}


Check Error In XML List
  [Arguments]    ${root}   ${error}
    ${value} =                            Get Elements                ${root}         xpath=.//error[.='${error}']
    Length Should Be                      ${value}                    1

Response Should be XML Error
    [Arguments]    ${root}    @{errors}
    Should Be Equal                       ${root.tag}                 gkxml

    ${errors_count} =                     Get Length                  ${errors}
    ${count} =                            XML.Get Element Count       ${root}         errors/error
    Should Be Equal As Numbers            ${count}                    ${errors_count}

    FOR     ${error}    IN    @{errors}
      Check Error In XML List             ${root}                     ${error}
    END

Post Return Error
    [Arguments]    ${url}    ${data}    ${code}    @{errors}
    Create Session                          geokrety                    ${GK_URL}
    ${auth} =     GET On Session            geokrety                    url=/

    ${resp} =     POST On Session           geokrety                    url=${url}    data=${data}    expected_status=${code}
    Should Not Be Empty    ${resp.content}

    ${root} =     Parse XML                 ${resp.content}
    Response Should be XML Error            ${root}                     @{errors}


Response Should be XML Valid
    [Arguments]    ${root}
    Should Be Equal                       ${root.tag}                 gkxml


    ${count} =                            XML.Get Element Count       ${root}         image-upload
    Should Be Equal As Numbers            ${count}                    ${1}

    ${count} =                            XML.Get Element Count       ${root}         image-upload/success
    Should Be Equal As Numbers            ${count}                    ${1}
    ${count} =                            XML.Get Element Count       ${root}         image-upload/uploadUrl
    Should Be Equal As Numbers            ${count}                    ${1}
    ${count} =                            XML.Get Element Count       ${root}         image-upload/s3Key
    Should Be Equal As Numbers            ${count}                    ${1}
    ${count} =                            XML.Get Element Count       ${root}         image-upload/headers
    Should Be Equal As Numbers            ${count}                    ${1}
    ${count} =                            XML.Get Element Count       ${root}         image-upload/headers/key
    Should Be Equal As Numbers            ${count}                    ${1}
    ${count} =                            XML.Get Element Count       ${root}         image-upload/headers/X-Amz-Credential
    Should Be Equal As Numbers            ${count}                    ${1}
    ${count} =                            XML.Get Element Count       ${root}         image-upload/headers/X-Amz-Algorithm
    Should Be Equal As Numbers            ${count}                    ${1}
    ${count} =                            XML.Get Element Count       ${root}         image-upload/headers/X-Amz-Date
    Should Be Equal As Numbers            ${count}                    ${1}
    ${count} =                            XML.Get Element Count       ${root}         image-upload/headers/Policy
    Should Be Equal As Numbers            ${count}                    ${1}
    ${count} =                            XML.Get Element Count       ${root}         image-upload/headers/X-Amz-Signature
    Should Be Equal As Numbers            ${count}                    ${1}


Post Request Valid
    [Arguments]    ${url}    ${data}
    Create Session                          geokrety                    ${GK_URL}
    ${auth} =     GET On Session            geokrety                    url=/

    ${resp} =     POST On Session           geokrety                    url=${url}    data=${data}
    Should Not Be Empty    ${resp.content}

    ${root} =     Parse XML                 ${resp.content}
    Response Should be XML Valid            ${root}

    Delete All Sessions
