#!/bin/sh
#
# install - make installation of pmwiki farm
#
# Author: Tamara Temple <tamara@tamaratemple.com>
# Created: 2011/10/21
# Time-stamp: <2011-10-21 10:59:55 tamara>
# Copyright (c) 2011 Tamara Temple Web Development
# License: GPLv3

if [ ! -f pmwiki.php ] ; then
    echo "$0 must be run after pmwiki is installed. See README for more info"
    exit -1

PATH_TO_FARM=$(pwd)

sed -i.bak -e "s/@PATHTOWIKIFARM@/$PATH_TO_FARM/" skel/index.php

cp skel/sample-site-config.php local/config.php

