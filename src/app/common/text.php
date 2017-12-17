<?php
/*
 * Common text handling functions
 */


/**
 * Convert links into HTML anchor tags
 */
function make_links_clickable($text){
  return preg_replace('!(((f|ht)tp(s)?://)[-a-zA-Z?-??-?()0-9@:%_+.~#?&;//=]+)!i', '<a href="$1" target="_blank">$1</a>', $text);
}

/**
 * Convert basic markup into HTML tags
 *
 * Valid markup is:
 *  [i][/i] - Italics
 *  [b][/b] - Bold
 *  [u][/u] - Underline
 *  [big][/big] - Big class
 *  [strike] - Strikethrough
 *  [spoiler] - Spoiler class
 */
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
