#!/bin/bash
if [ $# -lt 1 ]; then
        echo "Usage: <login asso> (<bounce|tous|both> [les adresses a creer])"
        exit 1
fi

#both is the default value
acreer="both"
if [ $# -ge 2 ]; then
        acreer=$2
fi

if [ ! "$acreer" = "bounce" -a ! "$acreer" = "tous" -a ! "$acreer" = "both" ]; then
        echo "Usage: <login asso> (<bounce|tous|both> [les adresses a creer])"
        exit 1
fi

echo "Création pour $1 des listes $acreer ..."

#If file exists, abort, otherwise create it (we don't wanna orceride any file)
file="./sympatmpcrealist.xml"
if [ -e $file ]; then
        echo "A filed named sympatmpcrealist.xml already exists, aborting ..."
        exit 1
fi

touch "$file"

writeList() {
echo "<?xml version=\"1.0\" ?>" > $file
echo "<list>" >> $file
echo "  <listname>$1-$2</listname>" >> $file
echo "  <type>$3</type>" >> $file
echo "  <subject>$1</subject>" >> $file
echo "  <status>open</status>" >> $file
echo "  <topic>$3</topic>" >> $file
echo "  <owner>" >> $file
echo "          <email>$1@assos.utc.fr</email>" >> $file
echo "  </owner>" >> $file
echo "</list>" >> $file

/usr/local/sbin/sympa.pl --create_list --input_file=$PWD/sympatmpcrealist.xml
rm $file
}

if [ "$acreer" = "bounce" -o "$acreer" = "both" ]; then
        writeList "$1" "bounce" "redirections"
fi

if [ "$acreer" = "tous" -o "$acreer" = "both" ]; then
        writeList "$1" "tous" "redirections"
fi
