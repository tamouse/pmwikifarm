#!/bin/sh
#
# newwiki - create a new wikifield in the wikifarm
#
#  Created by Tamara Temple on 2011-09-20.
#  Version: Time-stamp: <2011-11-12 04:27:56 tamara>
#  Copyright (c) 2011 Tamara Temple Web Development. 
#  License: GPLv3
#

# The following should be set by the install.sh or update.sh script
# which should be run after installing pmwiki.
FARMDIR=@PATHTOWIKIFARM@

FIELDSDIR=$FARMDIR/var
SKELDIR=$FARMDIR/skel

read -p "Enter the wiki's title: " WIKITITLE
if [ -z "$WIKITITLE" ] ; then
    echo "You MUST specify a wiki title"
    exit 1
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
    exit 2;
fi

if [ ! -w "$WIKIFIELDROOT" ] ; then
    echo "$WIKIFIELDROOT is not writeable. Make sure it is writeable first."
    exit 3
fi

DEFAULTLINKPUB=0
read -p "Do you want to use the site-wide pub directory? (Y/n) " LINKPUB
if [ -z "$LINKPUB" ] ; then
    LINKPUB=$DEFAULTLINKPUB
else
    LINKPUB=${LINKPUB:0:1}
    LINKPUB=$(echo "$LINKPUB" | tr '[:upper:]' '[:lower:]')
    if [ "y" == "$LINKPUB" ] ; then
	LINKPUB=0
    else
	LINKPUB=1
    fi
fi
	
mkdir -p $FIELDSDIR/$WIKIFIELDNAME || exit 4
cd $FIELDSDIR/$WIKIFIELDNAME
for d in $SKELDIR/* ; do
    if [ -d $d ] ; then # copy just the directories; the files go straight into the wiki field's web directory
	cp -r $d .
    fi
done

cat docs/sample-local-config.php | \
    sed -e "s/@WIKIFIELDNAME@/$WIKIFIELDNAME/g" \
    -e "s/@WIKITITLE@/$WIKITITLE/g" \
    -e "s/@SKIN@/$SKIN/g" > local/config.php

cp $FARMDIR/docs/sample-config.php local/local-config.php # this file has already been made benign by the install script

cd $WIKIFIELDROOT
ln -s $SKELDIR/index.php .
if $LINKPUB ; then
    ln -s $FARMDIR/pub .
else
    cp -r $FARMDIR/pub $FIELDSDIR/$WIKIFIELDNAME
    ln -s $FIELDSDIR/$WIKIFIELDNAME/pub .
fi
ln -s $FIELDSDIR/$WIKIFIELDNAME/cookbook .
ln -s $FIELDSDIR/$WIKIFIELDNAME/local .
ln -s $FIELDSDIR/$WIKIFIELDNAME/uploads .
cp $SKELDIR/.htaccess .


echo "Make sure directories have proper permissions."
echo
echo "$FIELDSDIR/$WIKIFIELDNAME/wiki.d/ needs to be writeable by the server."
echo "$FIELDSDIR/$WIKIFIELDNAME/uploads/ needs to be writeable by the server."
echo
echo "These can be set by:"
echo
echo "   $ cd $FIELDSDIR/$WIKIFIELDNAME"
echo "   $ chown server-user:server-group wiki.d uploads"
echo
echo "Substituting what ever user and group your server runs as"
echo "for server-user:server-group."
echo
echo "Alternatively, you can set permissions as follows:"
echo
echo "   $ cd $FIELDSDIR/$WIKIFIELDNAME"
echo "   $ chmod -R 2777 wiki.d uploads"
echo
echo "And this will permit server-user to write in those directories"
echo


exit 0
