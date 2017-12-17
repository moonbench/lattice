<?php
/*
 * Functions for detecting spam
 */


/**
 * Spam database
 */
define("spam_phrases", ["cialis", "viagra", "pharm", "pharm[0-9]*canada", "pharm[0-9]*pills", "pillsonline", "onlinepills",
			"Very nice site!",
			"payday", "loan", "instantcash", "paydaycash",
			"cheap goods", "discount", "http:\/\/cheap",
			"delmet.by"]);


/**
 * Compute a spam score for a string
 *
 * A spam score represents a likelyhood that a string is
 * just spam. It is based on the number of links and the
 * number of spam words
 */
function get_spam_score_for_text( $text ){
  $fudge_factor = 3;
  $link_factor = intval(log(number_of_links($text)*2, 10)*10) + 1;
  $phrase_factor = intval(log(number_of_spam_phrases($text)*2, 10)*10) + 1;

  return (intval($link_factor * $phrase_factor)-1)/$fudge_factor/10;
}

/**
 * Count the number of links in a string
 */
function number_of_links( $text ){
  $finds = null;
  preg_match_all("/http[s]?:\/\//", $text, $finds);
  return count($finds[0]);
}

/**
 * Computer the number of spam phrases in a string
 */
function number_of_spam_phrases( $text ){
  $total_finds = 0;
  foreach(spam_phrases as $phrase){
    if(preg_match_all("/$phrase/i", $text, $finds)){ $total_finds += count($finds[0]); }
  }
  return $total_finds;
}
?>
