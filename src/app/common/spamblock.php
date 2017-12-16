<?php

define("spam_phrases", ["cialis", "viagra", "pharm", "pharm[0-9]*canada", "pharm[0-9]*pills", "pillsonline", "onlinepills",
			"Very nice site!",
			"payday", "loan", "instantcash", "paydaycash",
			"cheap goods", "discount", "http:\/\/cheap",
			"delmet.by"]);

function number_of_links( $text ){
  $finds = null;
  preg_match_all("/http[s]?:\/\//", $text, $finds);
  return count($finds[0]);
}

function number_of_spam_phrases( $text ){
  $total_finds = 0;
  foreach(spam_phrases as $phrase){
    if(preg_match_all("/$phrase/i", $text, $finds)){ $total_finds += count($finds[0]); }
  }
  return $total_finds;
}

function get_spam_score_for_text( $text ){
  $fudge_factor = 3;
  $link_factor = intval(log(number_of_links($text)*2, 10)*10) + 1;
  $phrase_factor = intval(log(number_of_spam_phrases($text)*2, 10)*10) + 1;

  return (intval($link_factor * $phrase_factor)-1)/$fudge_factor/10;
}

?>