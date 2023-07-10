#!/bin/bash
for x in {A..Z}
do
  lowerX=$(echo "$x" |  tr '[:upper:]' '[:lower:]' )
  say "$lowerX" -v Anna -o "de/$lowerX.aiff"
  say "$lowerX" -o "en/$lowerX.aiff"
  say "$x" -v Anna -o "de/$lowerX-upper.aiff"
  say "$x" -o "en/$lowerX-upper.aiff"

  ffmpeg -i "de/$lowerX.aiff" -map_metadata -1 -fflags +bitexact "de/$lowerX.wav"
  ffmpeg -i "en/$lowerX.aiff" -map_metadata -1 -fflags +bitexact "en/$lowerX.wav"
  ffmpeg -i "de/$lowerX-upper.aiff" -map_metadata -1 -fflags +bitexact "de/$lowerX-upper.wav"
  ffmpeg -i "en/$lowerX-upper.aiff" -map_metadata -1 -fflags +bitexact "en/$lowerX-upper.wav"

  rm "de/$lowerX.aiff"
  rm "en/$lowerX.aiff"
  rm "de/$lowerX-upper.aiff"
  rm "en/$lowerX-upper.aiff"
done

for x in {0..9}
do
  say "$x" -v Anna -o "de/$x.aiff"
  say "$x" -o "en/$x.aiff"

  ffmpeg -i "de/$x.aiff" -map_metadata -1 -fflags +bitexact "de/$x.wav"
  ffmpeg -i "en/$x.aiff" -map_metadata -1 -fflags +bitexact "en/$x.wav"

  rm "de/$x.aiff"
  rm "en/$x.aiff"
done

say "[[slnc 400]]" -o "silence.aiff"
ffmpeg -i "silence.aiff" -map_metadata -1 -fflags +bitexact "silence.wav"
rm silence.aiff