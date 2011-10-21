#!/bin/sh
#
# newwiki - create a new wikifield in the wikifarm
#
#  Created by Tamara Temple on 2011-09-20.
#  Version: Time-stamp: <2011-10-21 16:17:10 tamara>
#  Copyright (c) 2011 Tamara Temple Web Development. 
#  License: GPLv3
#

# Configure these two variables to your installation
# These should be set by the install.sh script which should be run
# after installing pmwiki.
FARMDIR=@PATHTOWIKIFARM@
FIELDSDIR=$FARMDIR/var
SKELDIR=$FARMDIR/skel

read -p "Enter the wiki's title: " WIKITITLE
if [ -z "$WIKITITLE" ] ; then
    echo "You MUST specify a wiki title"
    exit -1
fi

WIKITITLE=$(echo "$WIKITITLE" | tr -d '/') # wiki titles and such cannot have a slash in them -- pmwiki rules

DEFAULTWIKIFIELDNAME=$(echo "$WIKITITLE" |tr -cd '[:alnum:]'|tr '[:upper:]' '[:lower:]') # remove everything except letters and numbers and make all letters lower case

read -p "Enter the wiki's field name [$DEFAULTWIKIFIELDNAME]: " WIKIFIELDNAME
if [ -z "$WIKIFIELDNAME" ] ; then
    WIKIFIELDNAME="$DEFAULTWIKIFIELDNAME"
fi
WIKIFIELDNAME=$(echo $WIKIFIELDNAME|tr -cd '[:alnum:]'|tr '[:upper:]' '[:lower:]') # remove everything except letters and numbers and make all letters lower case

DEFAULTSKIN=pmwiki
read -p "Which skin do you wish to use? [$DEFAULTSKIN] " SKIN
if [ -z "$SKIN" ] ; then
    SKIN="$DEFAULTSKIN"
fi

read -p "Enter the wiki field's web server folder: " WIKIFIELDROOT
if [ ! -d "$WIKIFIELDROOT" ] ; then
    echo "$WIKIFIELDROOT does not exist. Create it first."
    exit -1;
fi

if [ ! -w "$WIKIFIELDROOT" ] ; then
    echo "$WIKIFIELDROOT is not writeable. Make sure it is writeable first."
    exit -1
fi


mkdir -p $FIELDSDIR/$WIKIFIELDNAME || exit -1
cd $FIELDSDIR/$WIKIFIELDNAME
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

cd $WIKIFIELDROOT
mkdir -p $WIKIFIELDNAME || exit -1
cd $WIKIFIELDNAME
ln -s $SKELDIR/index.php .
ln -s $FARMDIR/pub .
ln -s $FIELDSDIR/$WIKIFIELDNAME/local .
ln -s $FIELDSDIR/$WIKIFIELDNAME/uploads .

echo "Make sure directories have proper permissions."
echo
echo "$FIELDSDIR/$WIKIFIELDNAME/wiki.d/ needs to be writeable by the server."
echo "$FIELDSDIR/$WIKIFIELDNAME/uploads/ needs to be writeable by the server."
echo
echo "These can be set by:"
echo
echo "   $ cd $FIELDSDIR/$WIKIFIELDNAME"
echo "   $ sudo chown server-user:server-group wiki.d/ uploads/"
echo
echo "Substituting what ever user and group your server runs as"
echo "for server-user:server-group."



