#!/bin/sh
#
# install - make installation of pmwiki farm
#
# Author: Tamara Temple <tamara@tamaratemple.com>
# Created: 2011/10/21
# Time-stamp: <2011-11-12 04:51:35 tamara>
# Copyright (c) 2011 Tamara Temple Web Development
# License: GPLv3

if [ ! -f pmwiki-latest/pmwiki.php ] ; then
    echo "$0 must be run after pmwiki is installed. See README for more info"
    exit -1
fi

PATH_TO_FARM=$(pwd)

cat skel/in-index.php | sed -e "s|@PATHTOWIKIFARM@|$PATH_TO_FARM|" > skel/index.php
cat in-newwiki.sh | sed -e "s|@PATHTOWIKIFARM@|$PATH_TO_FARM|" > newwiki.sh

cp site/sample-site-config.php local/config.php

# get rid of the WikiTitle declaration in the
# pmwiki/local/sample-config.php file so it doesn't get reset by
# accident. Eventually, docs/sample-config.php will get copied to the
# wiki field's local directory as local-config.php
sed -i.bak -e '/WikiTitle/s|^|// WikiTitle is set in field'\''s local/config.php |' docs/sample-config.php