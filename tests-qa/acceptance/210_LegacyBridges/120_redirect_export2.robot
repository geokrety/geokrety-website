*** Settings ***
Library         RequestsLibrary
Resource        ../ressources/vars/Urls.robot

*** Variables ***

${DEFAULT_PARAMS}    timezone=Europe%2FParis&compress=0


*** Test Cases ***

Should Redirect To New Url
    Go To Url                            ${GK_URL}/export2.php    redirect=${NO_REDIRECT_CHECK}
    Location Should Be                   ${PAGE_LEGACY_API_EXPORT2_URL}?${DEFAULT_PARAMS}

Should Redirect To A 302
    Create Session     geokrety          ${GK_URL}
    GET On Session     geokrety          /export2.php      expected_status=302  allow_redirects=${false}

With param - modifiedsince
    Go To Url                            url=${GK_URL}/export2.php?modifiedsince=202011201312    redirect=${NO_REDIRECT_CHECK}
    Location Should Be                   url=${PAGE_LEGACY_API_EXPORT2_URL}?modifiedsince=202011201312&${DEFAULT_PARAMS}

With param - userid
    Go To Url                            url=${GK_URL}/export2.php?userid=26422    redirect=${NO_REDIRECT_CHECK}
    Location Should Be                   url=${PAGE_LEGACY_API_EXPORT2_URL}?${DEFAULT_PARAMS}&userid=26422

With param - gkid
    Go To Url                            url=${GK_URL}/export2.php?gkid=1234    redirect=${NO_REDIRECT_CHECK}
    Location Should Be                   url=${PAGE_LEGACY_API_EXPORT2_URL}?${DEFAULT_PARAMS}&gkid=1234

With param - wpt
    Go To Url                            url=${GK_URL}/export2.php?wpt=GC5BRQK    redirect=${NO_REDIRECT_CHECK}
    Location Should Be                   url=${PAGE_LEGACY_API_EXPORT2_URL}?${DEFAULT_PARAMS}&wpt=GC5BRQK

With param - wpt - like
    Go To Url                            url=${GK_URL}/export2.php?wpt=GR    redirect=${NO_REDIRECT_CHECK}
    Location Should Be                   url=${PAGE_LEGACY_API_EXPORT2_URL}?${DEFAULT_PARAMS}&wpt=GR

With param - lonSW
    Go To Url                            url=${GK_URL}/export2.php?lonSW=43.2    redirect=${NO_REDIRECT_CHECK}
    Location Should Be                   url=${PAGE_LEGACY_API_EXPORT2_URL}?${DEFAULT_PARAMS}&lonSW=43.2

With param - latSW
    Go To Url                            url=${GK_URL}/export2.php?latSW=6.8    redirect=${NO_REDIRECT_CHECK}
    Location Should Be                   url=${PAGE_LEGACY_API_EXPORT2_URL}?${DEFAULT_PARAMS}&latSW=6.8

With param - lonNE
    Go To Url                            url=${GK_URL}/export2.php?lonNE=45.3    redirect=${NO_REDIRECT_CHECK}
    Location Should Be                   url=${PAGE_LEGACY_API_EXPORT2_URL}?${DEFAULT_PARAMS}&lonNE=45.3

With param - latNE
    Go To Url                            url=${GK_URL}/export2.php?${DEFAULT_PARAMS}&latNE=7.0    redirect=${NO_REDIRECT_CHECK}
    Location Should Be                   url=${PAGE_LEGACY_API_EXPORT2_URL}?${DEFAULT_PARAMS}&latNE=7.0

With param - lonSW latSW lonNE latNE
    Go To Url                            url=${GK_URL}/export2.php?lonSW=43.2&latSW=6.8&lonNE=45.3&latNE=7.0    redirect=${NO_REDIRECT_CHECK}
    Location Should Be                   url=${PAGE_LEGACY_API_EXPORT2_URL}?${DEFAULT_PARAMS}&lonSW=43.2&latSW=6.8&lonNE=45.3&latNE=7.0

With param - bypass password
    Go To Url                            url=${GK_URL}/export2.php?kocham_kaczynskiego=somepassword    redirect=${NO_REDIRECT_CHECK}
    Location Should Be                   url=${PAGE_LEGACY_API_EXPORT2_URL}?bypass_password=somepassword&${DEFAULT_PARAMS}

With param - timezone
    Go To Url                            url=${GK_URL}/export2.php?timezone=Europe/Berlin    redirect=${NO_REDIRECT_CHECK}
    Location Should Be                   url=${PAGE_LEGACY_API_EXPORT2_URL}?timezone=Europe%2FBerlin&compress=0

With param - inventory
    Go To Url                            url=${GK_URL}/export2.php?inventory=1    redirect=${NO_REDIRECT_CHECK}
    Location Should Be                   url=${PAGE_LEGACY_API_EXPORT2_URL}?${DEFAULT_PARAMS}&inventory=1

With param - secid
    Go To Url                            url=${GK_URL}/export2.php?secid=1324657890acbdefghijklmnopqrstuvwxz    redirect=${NO_REDIRECT_CHECK}
    Location Should Be                   url=${PAGE_LEGACY_API_EXPORT2_URL}?${DEFAULT_PARAMS}&secid=1324657890acbdefghijklmnopqrstuvwxz

With param - secid+inventory
    Go To Url                            url=${GK_URL}/export2.php?secid=1324657890acbdefghijklmnopqrstuvwxz&inventory=1    redirect=${NO_REDIRECT_CHECK}
    Location Should Be                   url=${PAGE_LEGACY_API_EXPORT2_URL}?${DEFAULT_PARAMS}&secid=1324657890acbdefghijklmnopqrstuvwxz&inventory=1
