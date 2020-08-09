*** Settings ***
Documentation     Variables for users definitions

*** Variables ***
&{GEOKRETY_1}      name=geokrety01    ref=GK0001    tc=TC0001    type=0    mission=A journey in heaven
&{GEOKRETY_2}      name=geokrety02    ref=GK0002    tc=TC0002    type=0    mission=${EMPTY}
&{GEOKRETY_3}      name=geokrety03    ref=GK0003    tc=TC0003    type=0    mission=${EMPTY}
&{GEOKRETY_4}      name=geokrety04    ref=GK0004    tc=TC0004    type=0    mission=${EMPTY}

${TC_INVALID}      ABCDEF
