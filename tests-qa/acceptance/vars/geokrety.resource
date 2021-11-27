*** Settings ***
Documentation     Variables for users definitions

*** Variables ***
&{GEOKRETY_1}      name=geokrety01    ref=GK0001    tc=TC0001    id=1    type=0    mission=A journey in heaven
&{GEOKRETY_2}      name=geokrety02    ref=GK0002    tc=TC0002    id=2    type=0    mission=${EMPTY}
&{GEOKRETY_3}      name=geokrety03    ref=GK0003    tc=TC0003    id=3    type=0    mission=${EMPTY}
&{GEOKRETY_4}      name=geokrety04    ref=GK0004    tc=TC0004    id=4    type=0    mission=${EMPTY}
&{GEOKRETY_5}      name=geokrety05    ref=GK0005    tc=TC0005    id=5    type=0    mission=${EMPTY}
&{GEOKRETY_6}      name=geokrety06    ref=GK0006    tc=TC0006    id=6    type=0    mission=${EMPTY}
&{GEOKRETY_7}      name=geokrety07    ref=GK0007    tc=TC0007    id=7    type=0    mission=${EMPTY}
&{GEOKRETY_8}      name=geokrety08    ref=GK0008    tc=TC0008    id=8    type=0    mission=${EMPTY}
&{GEOKRETY_9}      name=geokrety09    ref=GK0009    tc=TC0009    id=9    type=0    mission=${EMPTY}
&{GEOKRETY_10}     name=geokrety10    ref=GK000A    tc=TC000A    id=10   type=0    mission=${EMPTY}
&{GEOKRETY_11}     name=geokrety11    ref=GK000B    tc=TC000B    id=11   type=0    mission=${EMPTY}
&{GEOKRETY_12}     name=geokrety12    ref=GK000C    tc=TC000C    id=12   type=0    mission=${EMPTY}
&{GEOKRETY_13}     name=geokrety13    ref=GK000D    tc=TC000D    id=13   type=0    mission=${EMPTY}
&{GEOKRETY_14}     name=geokrety14    ref=GK000E    tc=TC000E    id=14   type=0    mission=${EMPTY}
&{GEOKRETY_15}     name=geokrety15    ref=GK000F    tc=TC000F    id=15   type=0    mission=${EMPTY}


&{GEOKRETY_STARTING_WITH_GK}     name=geokrety starting with GK    ref=N/A    tc=GK1234    id=16   type=0    mission=${EMPTY}

${TC_INVALID}      ABCDEF
