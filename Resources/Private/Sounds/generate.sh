#!/bin/bash
for x in {A..Z}
do
  lowerX=$(echo "$x" |  tr '[:upper:]' '[:lower:]' )
  say "$lowerX" -v Anna -o "de/$lowerX.aiff"
  say "$x" -v Anna -o "de/$lowerX-upper.aiff"

  ffmpeg -i "de/$lowerX.aiff" -map_metadata -1 -fflags +bitexact "de/$lowerX.wav"
  ffmpeg -i "de/$lowerX-upper.aiff" -map_metadata -1 -fflags +bitexact "de/$lowerX-upper.wav"

  rm "de/$lowerX.aiff"
  rm "de/$lowerX-upper.aiff"
done

for x in {0..9}
do
  say "$x" -v Anna -o "de/$x.aiff"

  ffmpeg -i "de/$x.aiff" -map_metadata -1 -fflags +bitexact "de/$x.wav"

  rm "de/$x.aiff"
done

say "[[slnc 400]]" -o "silence.aiff"
ffmpeg -i "silence.aiff" -map_metadata -1 -fflags +bitexact "silence.wav"
rm silence.aiff