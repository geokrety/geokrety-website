#!/bin/bash

cat html-head.html > index.html

for f in `ls *.png *.svg`
do
	rozmiar=`du -sh $f | awk '{ print $1}'`
	echo "<p><a href='$f'><img src='$f' border='0'></a><br />$f :: $rozmiar</p><hr />" >> index.html
done

echo -e "Generated: " >>  index.html
date >>  index.html

cat html-tail.html >> index.html