#!/bin/bash -u

# this command outputs the top-most parent of the current folder that is still 
# under svn revision control to standard out

# if the current folder is not under svn revision control, nothing is output
# and a non-zero exit value is given

parent="";
grandparent=$(pwd);

while [ -d "$grandparent/.svn" ]; do
    parent=$grandparent;
    grandparent=$(dirname $parent);
done

if [ ! -z "$parent" ]; then
    echo $parent;
else
    exit 1;
fi