*** Settings ***
Library         RequestsLibrary
Resource        ../ressources/vars/Urls.robot

*** Variables ***

${DEFAULT_PARAMS}    timezone=Europe%2FParis&compress=0


*** Test Cases ***

Should Redirect To New Url
    Go To Url                            ${GK_URL}/export.php    redirect=${NO_REDIRECT_CHECK}
    Location Should Be                   ${PAGE_LEGACY_API_EXPORT_URL}?${DEFAULT_PARAMS}

Should Redirect To A 302
    Create Session     geokrety          ${GK_URL}
    GET On Session     geokrety          /export.php      expected_status=302  allow_redirects=${false}

With param - modifiedsince
    Go To Url                            url=${GK_URL}/export.php?modifiedsince=202011201312    redirect=${NO_REDIRECT_CHECK}
    Location Should Be                   url=${PAGE_LEGACY_API_EXPORT_URL}?modifiedsince=202011201312&${DEFAULT_PARAMS}

With param - bypass password
    Go To Url                            url=${GK_URL}/export.php?kocham_kaczynskiego=somepassword    redirect=${NO_REDIRECT_CHECK}
    Location Should Be                   url=${PAGE_LEGACY_API_EXPORT_URL}?bypass_password=somepassword&${DEFAULT_PARAMS}

With param - timezone
    Go To Url                            url=${GK_URL}/export.php?timezone=Europe/Berlin    redirect=${NO_REDIRECT_CHECK}
    Location Should Be                   url=${PAGE_LEGACY_API_EXPORT_URL}?timezone=Europe%2FBerlin&compress=0
