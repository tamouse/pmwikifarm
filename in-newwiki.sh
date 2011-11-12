#!/bin/bash
#
# newwiki - create a new wikifield in the wikifarm
#
#  Created by Tamara Temple on 2011-09-20.
#  Version: Time-stamp: <2011-11-12 08:11:47 tamara>
#  Copyright (c) 2011 Tamara Temple Web Development. 
#  License: GPLv3
#

# The following should be set by the install.sh or update.sh script
# which should be run after installing pmwiki.
FARMDIR=@PATHTOWIKIFARM@

PMWIKIDIR=$FARMDIR/pmwiki-latest
FIELDSDIR=$FARMDIR/var
SKELDIR=$FARMDIR/skel

read -p "Enter the wiki's field name : " WIKIFIELDNAME
if [ -z "$WIKIFIELDNAME" ] ; then
    echo "Must give wiki field name"
    exit 1
fi
WIKIFIELDNAME=$(echo $WIKIFIELDNAME|tr -cd '[:alnum:]'|tr '[:upper:]' '[:lower:]') # remove everything except letters and numbers and make all letters lower case
if [ -e $FIELDSDIR/$WIKIFIELDNAME ] ; then
    echo $FIELDSDIR/$WIKIFIELDNAME " already exists."
    exit 1
fi

read -p "Enter the wiki field's web server folder: " WIKIFIELDROOT
if [ -z "$WIKIFIELDROOT" ] ; then
    echo "Must specify a location for the wikifield document root"
    exit 2
fi
if [ -e "$WIKIFIELDROOT" ] ; then
    echo $WIKIFIELDROOT " already exists."
    exit 2
fi

WIKIFIELDROOTDIR=`dirname $WIKIFIELDROOT`
if [ ! -d "$WIKIFIELDROOTDIR" ] ; then
    echo "$WIKIFIELDROOTDIR does not exist. Create it first."
    exit 2
fi

if [ ! -w "$WIKIFIELDROOTDIR" ] ; then
    echo "$WIKIFIELDROOTDIR is not writeable. Make sure it is writeable first."
    exit 2
fi

DEFAULTLINKPUB="y"
read -p "Do you want to use the site-wide pub directory? (Y/n) " LINKPUB
# echo $LINKPUB
if [ -z "$LINKPUB" ] ; then
    LINKPUB=$DEFAULTLINKPUB
#    echo $LINKPUB
else
    LINKPUB=${LINKPUB:0:1}
#    echo $LINKPUB
    LINKPUB=$(echo "$LINKPUB" | tr '[:upper:]' '[:lower:]')
#    echo $LINKPUB
#    echo $LINKPUB
fi
echo $LINKPUB
	
echo "New wiki paramters:"
echo "Wiki field name: " $WIKIFIELDNAME
echo "Wiki field document root: " $WIKIFIELDROOT
if [ "y" == "$LINKPUB" ] ; then
    echo "Linking to farm's pub directory"
else
    echo "Copy farm's pub directory to new wiki"
fi
echo
read -p "Proceed with installation? [y/N] " PROCEED
if [ -z "$PROCEED" ] ; then exit; fi
PROCEED=$(echo ${PROCEED:0:1} | tr '[:upper:]' '[:lower:]')
if [ "n" == "$PROCEED" ] ; then exit; fi

mkdir -p $FIELDSDIR/$WIKIFIELDNAME || exit 4
cd $FIELDSDIR/$WIKIFIELDNAME
if [ "y" == "$LINKPUB" ] ; then
    ln -s $FARMDIR/pub .
else
    cp -r $FARMDIR/pub .
fi
for d in $SKELDIR/* ; do
    if [ -d $d ] ; then # copy just the directories; the files go straight into the wiki field's web directory
	cp -r $d .
    fi
done

cp $SKELDIR/docs/sample-local-config.php local/config.php
sed -i.bak -e "s/@WIKIFIELDNAME@/$WIKIFIELDNAME/g" local/config.php

cp $PMWIKIDIR/docs/sample-config.php local/local-config.php

mkdir $WIKIFIELDROOT || exit 5

cd $WIKIFIELDROOT || exit 6
ln -s $SKELDIR/index.php .
ln -s $FIELDSDIR/$WIKIFIELDNAME/pub .
ln -s $FIELDSDIR/$WIKIFIELDNAME/cookbook .
ln -s $FIELDSDIR/$WIKIFIELDNAME/local .
ln -s $FIELDSDIR/$WIKIFIELDNAME/uploads .
cp $SKELDIR/.htaccess .

cd $FARMDIR

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
