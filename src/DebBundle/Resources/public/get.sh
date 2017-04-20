#!/bin/sh
dpkg --info $1 | tail -n+6 | cut -b 2- > info.tmp
sed -e "/Description:/iSize: `stat -c %s $1`" -i info.tmp
sed -e "/Description:/iSHA256: `sha256sum $1 | cut -d" " -f1`" -i info.tmp
sed -e "/Description:/iSHA1: `sha1sum $1 | cut -d" " -f1`" -i info.tmp
sed -e "/Description:/iMD5sum: `md5sum $1 | cut -d" " -f1`" -i info.tmp
cat info.tmp
rm info.tmp
