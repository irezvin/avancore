#!/bin/bash

#    Script diff3to2.sh may be used to compare Ac0.3 and Ac0.2 versions 
#    (usage: diff3to2.sh <Ac3file> <Ac2file>; will produce .patch file)

if [ ! -f "$1" ]; then
    echo "'from' (Ac3) file must be specified"
    exit
fi
if [ ! -f "$2" ]; then
    echo "'to' (Ac2) file must be specified"
    exit
fi

dir=`dirname $0`
fn=`basename $2`
dir2=`dirname $1`
t=${dir2}/ac3_${fn}
cp "$2" "$t"
flip -ub $t
sed -rf "$dir/2013-01-12 script.sed" -i $t
diff -u "$1" "$t" > $dir2/$fn.patch
rm "$t"

