#!/bin/sh +x
#
# newwiki - create a new wikifield in the wikifarm
#
#  Created by Tamara Temple on 2011-09-20.
#  Version: Time-stamp: <2011-10-21 14:03:35 tamara>
#  Copyright (c) 2011 Tamara Temple Web Development. 
#  License: GPLv3
#

# Configure these two variables to your installation
# These should be set by the install.sh script which should be run
# after installing pmwiki.
FARMDIR=@PATHTOWIKIFARM@
FIELDSDIR=$FARMDIR/var
SKELDIR=$FIELDSDIR/skel

read -p "Enter the wiki's title: " WIKITITLE
if [ -z "$WIKITITLE" ] ; then
    echo "You MUST specify a wiki title"
    exit -1
fi

DEFAULTWIKIFIELDNAME=$(echo $WIKITITLE|tr -cd '[:alnum:]'|tr '[:upper:]' '[:lower:]')

read -p "Enter the wiki's field name [$DEFAULTWIKIFIELDNAME]: " WIKIFIELDNAME
if [ -z "$WIKIFIELDNAME" ] ; then
    WIKIFIELDNAME=$DEFAULTWIKIFIELDNAME
fi
WIKIFIELDNAME=$(echo $WIKIFIELDNAME|tr -cd '[:alnum:]'|tr '[:upper:]' '[:lower:]')

DEFAULTSKIN=pmwiki
read -p "Which skin do you wish to use? [$DEFAULTSKIN] " SKIN
if [ -z $SKIN ] ; then
    $SKIN=$DEFAULTSKIN
fi

mkdir -p $FIELDSDIR/$WIKIFIELDNAME || exit -1
pushd $FIELDSDIR/$WIKIFIELDNAME
ln -s $FARMDIR/pub .
for d in $FARMDIR/skel/* ; do
    if [ -d $d ] ; then
	cp -r $d .
    fi
done
cat docs/sample-local-config.php | \
    sed -e "s/@WIKIFIELDNAME@/$WIKIFIELDNAME/g" \
    -e "s/@WIKITITLE@/$WIKITITLE/g" \
    -e "s/@SKIN@/$SKIN/g" > local/config.php
cp $FARMDIR/docs/sample-config.php local/local-config.php
popd

read -p "Enter the wiki field's web server folder: " WIKIFIELDROOT
mkdir -p $WIKIFIELDROOT || exit -1
pushd $WIKIFIELDROOT
ln -s $SKELDIR/index.php .
popd


