*** Settings ***
Documentation     Variables for users definitions

*** Variables ***
&{WPT_GC_1}     id=GC0001       coords=43.60000 7.00000    name=${EMPTY}    country=fr    elevation=800
&{WPT_GC_2}     id=GC0002       coords=43.70000 7.01000    name=${EMPTY}    country=fr    elevation=800
&{WPT_GC_3}     id=GC0003       coords=43.80000 7.02000    name=${EMPTY}    country=fr    elevation=800
&{WPT_GC_4}     id=GC0004       coords=43.90000 7.03000    name=${EMPTY}    country=fr    elevation=800
&{WPT_GC_5}     id=GC0005       coords=43.10000 7.04000    name=${EMPTY}    country=fr    elevation=800

${INVALID_WPT}          ABCDEF
${INVALID_GC_WPT}       GCZZZZ

&{WPT_OC_1}     id=OC0001       coords=43.69365 6.86097    name=OC Waypoint 0001    country=fr    elevation=800
&{WPT_OC_2}     id=OC0002       coords=43.69365 7.86097    name=OC Waypoint 0002    country=fr    elevation=800
&{WPT_OC_3}     id=OC0003       coords=43.69365 8.86097    name=OC Waypoint 0003    country=fr    elevation=800
&{WPT_OC_4}     id=OC0004       coords=43.69365 9.86097    name=OC Waypoint 0004    country=fr    elevation=800
&{WPT_OC_5}     id=OC0005       coords=43.69365 10.86097   name=OC Waypoint 0005    country=fr    elevation=800
