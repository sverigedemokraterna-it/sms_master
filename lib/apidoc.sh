#!/bin/bash

# This script generates the apidoc.
# See that you have doxygen installed.

# Remove the old directory
rm -rf apidoc

# Ask doxygen to generate the docs
cat Doxyfile | doxygen $@ -
