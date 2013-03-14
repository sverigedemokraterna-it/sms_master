#!/bin/bash

# Use this to regenerate the pot, which can then be imported into the languages .po

if [ ! -e "default.pot" ]
then
    touch default.pot
fi

xgettext -j -o default.pot -D . --no-wrap --no-location -kT_ *php


# This is old stuff. I don't think I'll delete it just quite yet. 2013-03-13.
#find locale -name LC_MESSAGES -exec cp default.po
#mcedit default.po
#msgfmt -o default.mo default.po
#cp default.* locale/sv_SE/LC_MESSAGES
