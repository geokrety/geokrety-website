#!/bin/bash

fraza="import_request_variables('g'"

f=$1


prefix=`cat $f | grep "$fraza" | awk -F "(" '{ print $2 }' | awk -F "'" '{ print $4 }'`
echo "--- prefix: $prefix"



cat $f | egrep -oh "[\\$][a-zA-Z_][a-zA-Z0-9_]+" | grep "\$$prefix" | sort -u > napraw.zmienne


while read line
do

echo "zmieniam zmienną $line"

zmiennawurl=`echo $line | sed -e "s/${prefix}//g" | sed -e "s/\$//g"`

#//echo "$zmiennawurl"
#exit

nowazmienna="$line = \$_GET['$zmiennawurl'];";

sed -i "s/$fraza/\n$nowazmienna\n\/\/ autopoprawione...$fraza/g" $f

done < "napraw.zmienne"


# for f in *.php; do sed -i "s/\$_POST\['\$kret_/\$_POST\['/g" $f; done