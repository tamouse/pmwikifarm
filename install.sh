#!/bin/sh
#
# install - make installation of pmwiki farm
#
# Author: Tamara Temple <tamara@tamaratemple.com>
# Created: 2011/10/21
# Copyright (c) 2011 Tamara Temple Web Development
# License: GPLv3

PATH_TO_FARM=$(pwd)
cat skel/in-index.php | sed -e "s|@PATHTOWIKIFARM@|$PATH_TO_FARM|" > skel/index.php
cat in-newwiki.sh | sed -e "s|@PATHTOWIKIFARM@|$PATH_TO_FARM|" > newwiki.sh
chmod +x newwiki.sh

