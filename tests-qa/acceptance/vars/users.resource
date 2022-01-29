*** Settings ***
Documentation     Variables for users definitions

*** Variables ***
&{USER_EMPTY}    id=${EMPTY}    name=${EMPTY}    email=${EMPTY}    lang=${EMPTY}    daily_mail=${FALSE}    terms=${FALSE}    secid=${EMPTY}

&{USER_1}    id=1    name=username1    email=username1+qa@geokrety.org    lang=en    daily_mail=${FALSE}    terms=${FALSE}    secid=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx1
&{USER_2}    id=2    name=username2    email=username2+qa@geokrety.org    lang=fr    daily_mail=${TRUE}     terms=${FALSE}    secid=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx2
&{USER_3}    id=3    name=username3    email=username3+qa@geokrety.org    lang=en    daily_mail=${FALSE}    terms=${FALSE}    secid=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx3
