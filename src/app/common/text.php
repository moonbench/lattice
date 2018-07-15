<?php

function println($string){
  echo("${string}\n");
}

function clean($string){
  return htmlentities(preg_replace('/([\\r\\n][\\r\\n]){2,}/i',"\n\n",trim($string)));
}

function clean_br($string){
  return nl2br(clean($string));
}

function make_links_clickable($text){
  return preg_replace('!(((f|ht)tp(s)?://)[-a-zA-Z?-??-?()0-9@:%_+.~#?&;//=]+)!i', '<a href="$1" target="_blank">$1</a>', $text);
}

function apply_markup($text){
  $i_reg = '/\[i\](.+)\[\/i\]/';
  $b_reg = '/\[b\](.+)\[\/b\]/';
  $u_reg = '/\[u\](.+)\[\/u\]/';
  $big_reg = '/\[big\](.+)\[\/big\]/';
  $strike_reg = '/\[strike\](.+)\[\/strike\]/';
  $spoil_reg = '/\[spoiler\](.+)\[\/spoiler\]/';

  $reps = [[$i_reg, "i"], [$b_reg, "b"], [$u_reg, "u"], [$big_reg, "span class=\"big\""], [$strike_reg, "strike"], [$spoil_reg, "span class=\"spoiler\""]];
  foreach($reps as $replacement_set){
    $matches = array();
    $replacement_tag1 = $replacement_set[1];
    $replacement_tag2 = explode(" ", $replacement_set[1])[0];
    preg_match_all($replacement_set[0], $text, $matches);
    foreach($matches[1] as $index => $string){
      $text = str_replace($matches[0][$index], "<" . $replacement_tag1 . ">" . $string . "</" . $replacement_tag2.">", $text);
    }
  }
  return $text;
}
?>
