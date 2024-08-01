#!/bin/bash

languages=(
    'en::Daniel'
    'de::Anna'
    'fr::Thomas'
)

for index in "${languages[@]}" ; do
  LANGUAGE="${index%%::*}"
  VOICE="${index##*::}"

  mkdir -p "$LANGUAGE"

  for x in {A..Z}
  do
    lowerX=$(echo "$x" |  tr '[:upper:]' '[:lower:]' )
    say "$lowerX" -v $VOICE -o "$LANGUAGE/$lowerX.aiff"
    say "$x" -v $VOICE -o "$LANGUAGE/$lowerX-upper.aiff"

    ffmpeg -i "$LANGUAGE/$lowerX.aiff" -map_metadata -1 -fflags +bitexact "$LANGUAGE/$lowerX.wav"
    ffmpeg -i "$LANGUAGE/$lowerX-upper.aiff" -map_metadata -1 -fflags +bitexact "$LANGUAGE/$lowerX-upper.wav"

    rm "$LANGUAGE/$lowerX.aiff"
    rm "$LANGUAGE/$lowerX-upper.aiff"
  done

  for x in {0..9}
  do
    say "$x" -v $VOICE -o "$LANGUAGE/$x.aiff"

    ffmpeg -i "$LANGUAGE/$x.aiff" -map_metadata -1 -fflags +bitexact "$LANGUAGE/$x.wav"

    rm "$LANGUAGE/$x.aiff"
  done

done

say "[[slnc 400]]" -o "silence.aiff"
ffmpeg -i "silence.aiff" -map_metadata -1 -fflags +bitexact "silence.wav"
rm silence.aiff