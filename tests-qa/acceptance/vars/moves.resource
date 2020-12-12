*** Settings ***
Documentation     Variables for users definitions

*** Variables ***

@{REQUIRE_COORDINATES}    ${0}    ${3}    ${5}

### Moves by Author 1
# DROP
&{MOVE_1}     gkid=1    move_type=0    author=1    waypoint=GC0001      lat=43.60000    lon=7.00000     comment=Hello    app=robotframework    app_ver=3.2.1
# GRAB
&{MOVE_2}     gkid=1    move_type=1    author=1    waypoint=${EMPTY}    lat=${EMPTY}    lon=${EMPTY}    comment=Hello    app=robotframework    app_ver=3.2.1
# COMMENT
&{MOVE_3}     gkid=1    move_type=2    author=1    waypoint=${EMPTY}    lat=${EMPTY}    lon=${EMPTY}    comment=Hello    app=robotframework    app_ver=3.2.1
# SEEN
&{MOVE_4}     gkid=1    move_type=3    author=1    waypoint=GC0002      lat=43.70000    lon=7.10000     comment=Hello    app=robotframework    app_ver=3.2.1
# ARCHIVE
&{MOVE_5}     gkid=1    move_type=4    author=1    waypoint=${EMPTY}    lat=${EMPTY}    lon=${EMPTY}    comment=Hello    app=robotframework    app_ver=3.2.1
# DIP
&{MOVE_6}     gkid=1    move_type=5    author=1    waypoint=GC0003      lat=43.80000    lon=7.20000     comment=Hello    app=robotframework    app_ver=3.2.1

### Moves by Author 2
# DROP
&{MOVE_21}     gkid=1    move_type=0    author=2    waypoint=GC0001      lat=43.60000    lon=7.00000     comment=Hello    app=robotframework    app_ver=3.2.1
# GRAB
&{MOVE_22}     gkid=1    move_type=1    author=2    waypoint=${EMPTY}    lat=${EMPTY}    lon=${EMPTY}    comment=Hello    app=robotframework    app_ver=3.2.1
# COMMENT
&{MOVE_23}     gkid=1    move_type=2    author=2    waypoint=${EMPTY}    lat=${EMPTY}    lon=${EMPTY}    comment=Hello    app=robotframework    app_ver=3.2.1
# SEEN
&{MOVE_24}     gkid=1    move_type=3    author=2    waypoint=GC0002      lat=43.70000    lon=7.10000     comment=Hello    app=robotframework    app_ver=3.2.1
# ARCHIVE
&{MOVE_25}     gkid=1    move_type=4    author=2    waypoint=${EMPTY}    lat=${EMPTY}    lon=${EMPTY}    comment=Hello    app=robotframework    app_ver=3.2.1
# DIP
&{MOVE_26}     gkid=1    move_type=5    author=2    waypoint=GC0003      lat=43.80000    lon=7.20000     comment=Hello    app=robotframework    app_ver=3.2.1

### Moves by Geokret 2
# DROP
&{MOVE_31}     gkid=2    move_type=0    author=1    waypoint=GC0001      lat=43.60000    lon=7.00000     comment=Hello    app=robotframework    app_ver=3.2.1
# GRAB
&{MOVE_32}     gkid=2    move_type=1    author=1    waypoint=${EMPTY}    lat=${EMPTY}    lon=${EMPTY}    comment=Hello    app=robotframework    app_ver=3.2.1
# COMMENT
&{MOVE_33}     gkid=2    move_type=2    author=1    waypoint=${EMPTY}    lat=${EMPTY}    lon=${EMPTY}    comment=Hello    app=robotframework    app_ver=3.2.1
# SEEN
&{MOVE_34}     gkid=2    move_type=3    author=1    waypoint=GC0002      lat=43.70000    lon=7.10000     comment=Hello    app=robotframework    app_ver=3.2.1
# ARCHIVE
&{MOVE_35}     gkid=2    move_type=4    author=1    waypoint=${EMPTY}    lat=${EMPTY}    lon=${EMPTY}    comment=Hello    app=robotframework    app_ver=3.2.1
# DIP
&{MOVE_36}     gkid=2    move_type=5    author=1    waypoint=GC0003      lat=43.80000    lon=7.20000     comment=Hello    app=robotframework    app_ver=3.2.1
