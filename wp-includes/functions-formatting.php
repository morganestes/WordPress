<?php

add_filter('sanitize_title', 'sanitize_title_with_dashes');

function wptexturize($text) {
	$output = '';
	// Capture tags and everything inside them
	$textarr = preg_split("/(<.*>)/Us", $text, -1, PREG_SPLIT_DELIM_CAPTURE);
	$stop = count($textarr); $next = true; // loop stuff
	for ($i = 0; $i < $stop; $i++) {
		$curl = $textarr[$i];

		if (isset($curl{0}) && '<' != $curl{0} && $next) { // If it's not a tag
			$curl = str_replace('---', '&#8212;', $curl);
			$curl = str_replace('--', '&#8211;', $curl);
			$curl = str_replace("...", '&#8230;', $curl);
			$curl = str_replace('``', '&#8220;', $curl);

			// This is a hack, look at this more later. It works pretty well though.
			$cockney = array("'tain't","'twere","'twas","'tis","'twill","'til","'bout","'nuff","'round");
			$cockneyreplace = array("&#8217;tain&#8217;t","&#8217;twere","&#8217;twas","&#8217;tis","&#8217;twill","&#8217;til","&#8217;bout","&#8217;nuff","&#8217;round");
			$curl = str_replace($cockney, $cockneyreplace, $curl);

			$curl = preg_replace("/'s/", '&#8217;s', $curl);
			$curl = preg_replace("/'(\d\d(?:&#8217;|')?s)/", "&#8217;$1", $curl);
			$curl = preg_replace('/(\s|\A|")\'/', '$1&#8216;', $curl);
			$curl = preg_replace('/(\d+)"/', '$1&#8243;', $curl);
			$curl = preg_replace("/(\d+)'/", '$1&#8242;', $curl);
			$curl = preg_replace("/(\S)'([^'\s])/", "$1&#8217;$2", $curl);
			$curl = preg_replace('/(\s|\A)"(?!\s)/', '$1&#8220;$2', $curl);
			$curl = preg_replace('/"(\s|\Z)/', '&#8221;$1', $curl);
			$curl = preg_replace("/'([\s.]|\Z)/", '&#8217;$1', $curl);
			$curl = preg_replace("/\(tm\)/i", '&#8482;', $curl);
			$curl = preg_replace("/\(c\)/i", '&#169;', $curl);
			$curl = preg_replace("/\(r\)/i", '&#174;', $curl);
			$curl = str_replace("''", '&#8221;', $curl);
			
			$curl = preg_replace('/(d+)x(\d+)/', "$1&#215;$2", $curl);

		} elseif (strstr($curl, '<code') || strstr($curl, '<pre') || strstr($curl, '<kbd' || strstr($curl, '<style') || strstr($curl, '<script'))) {
			// strstr is fast
			$next = false;
		} else {
			$next = true;
		}
		$curl = preg_replace('/&([^#])(?![a-z]{1,8};)/', '&#038;$1', $curl);
		$output .= $curl;
	}
	return $output;
}

function clean_pre($text) {
	$text = str_replace('<br />', '', $text);
	return $text;
}

function wpautop($pee, $br = 1) {
	$pee = $pee . "\n"; // just to make things a little easier, pad the end
	$pee = preg_replace('|<br />\s*<br />|', "\n\n", $pee);
	// Space things out a little
	$pee = preg_replace('!(<(?:table|thead|tfoot|caption|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|form|blockquote|math|p|h[1-6])[^>]*>)!', "\n$1", $pee); 
	$pee = preg_replace('!(</(?:table|thead|tfoot|caption|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|form|blockquote|math|p|h[1-6])>)!', "$1\n", $pee);
	$pee = str_replace(array("\r\n", "\r"), "\n", $pee); // cross-platform newlines 
	$pee = preg_replace("/\n\n+/", "\n\n", $pee); // take care of duplicates
	$pee = preg_replace('/\n?(.+?)(?:\n\s*\n|\z)/s', "\t<p>$1</p>\n", $pee); // make paragraphs, including one at the end 
	$pee = preg_replace('|<p>\s*?</p>|', '', $pee); // under certain strange conditions it could create a P of entirely whitespace 
    $pee = preg_replace('!<p>\s*(</?(?:table|thead|tfoot|caption|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|form|blockquote|math|p|h[1-6])[^>]*>)\s*</p>!', "$1", $pee); // don't pee all over a tag
	$pee = preg_replace("|<p>(<li.+?)</p>|", "$1", $pee); // problem with nested lists
	$pee = preg_replace('|<p><blockquote([^>]*)>|i', "<blockquote$1><p>", $pee);
	$pee = str_replace('</blockquote></p>', '</p></blockquote>', $pee);
	$pee = preg_replace('!<p>\s*(</?(?:table|thead|tfoot|caption|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|form|blockquote|math|p|h[1-6])[^>]*>)!', "$1", $pee);
	$pee = preg_replace('!(</?(?:table|thead|tfoot|caption|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|form|blockquote|math|p|h[1-6])[^>]*>)\s*</p>!', "$1", $pee); 
	if ($br) $pee = preg_replace('|(?<!<br />)\s*\n|', "<br />\n", $pee); // optionally make line breaks
	$pee = preg_replace('!(</?(?:table|thead|tfoot|caption|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|form|blockquote|math|p|h[1-6])[^>]*>)\s*<br />!', "$1", $pee);
	$pee = preg_replace('!<br />(\s*</?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)>)!', '$1', $pee);
	$pee = preg_replace('!(<pre.*?>)(.*?)</pre>!ise', " stripslashes('$1') .  clean_pre('$2')  . '</pre>' ", $pee);
	
	return $pee; 
}


function seems_utf8($Str) { # by bmorel at ssi dot fr
	for ($i=0; $i<strlen($Str); $i++) {
		if (ord($Str[$i]) < 0x80) continue; # 0bbbbbbb
		elseif ((ord($Str[$i]) & 0xE0) == 0xC0) $n=1; # 110bbbbb
		elseif ((ord($Str[$i]) & 0xF0) == 0xE0) $n=2; # 1110bbbb
		elseif ((ord($Str[$i]) & 0xF8) == 0xF0) $n=3; # 11110bbb
		elseif ((ord($Str[$i]) & 0xFC) == 0xF8) $n=4; # 111110bb
		elseif ((ord($Str[$i]) & 0xFE) == 0xFC) $n=5; # 1111110b
		else return false; # Does not match any model
		for ($j=0; $j<$n; $j++) { # n bytes matching 10bbbbbb follow ?
			if ((++$i == strlen($Str)) || ((ord($Str[$i]) & 0xC0) != 0x80))
			return false;
		}
	}
	return true;
}

function remove_accents($string) {
	$chars['in'] = chr(128).chr(131).chr(138).chr(142).chr(154).chr(158)
	  .chr(159).chr(162).chr(165).chr(181).chr(192).chr(193).chr(194)
	  .chr(195).chr(196).chr(197).chr(199).chr(200).chr(201).chr(202)
	  .chr(203).chr(204).chr(205).chr(206).chr(207).chr(209).chr(210)
	  .chr(211).chr(212).chr(213).chr(214).chr(216).chr(217).chr(218)
	  .chr(219).chr(220).chr(221).chr(224).chr(225).chr(226).chr(227)
	  .chr(228).chr(229).chr(231).chr(232).chr(233).chr(234).chr(235)
	  .chr(236).chr(237).chr(238).chr(239).chr(241).chr(242).chr(243)
	  .chr(244).chr(245).chr(246).chr(248).chr(249).chr(250).chr(251)
	  .chr(252).chr(253).chr(255);
	$chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";
	if (seems_utf8($string)) {
		$invalid_latin_chars = array(chr(197).chr(146) => 'OE', chr(197).chr(147) => 'oe', chr(197).chr(160) => 'S', chr(197).chr(189) => 'Z', chr(197).chr(161) => 's', chr(197).chr(190) => 'z', chr(226).chr(130).chr(172) => 'E');
		$string = utf8_decode(strtr($string, $invalid_latin_chars));
	}
	$string = strtr($string, $chars['in'], $chars['out']);
	$double_chars['in'] = array(chr(140), chr(156), chr(198), chr(208), chr(222), chr(223), chr(230), chr(240), chr(254));
	$double_chars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
	$string = str_replace($double_chars['in'], $double_chars['out'], $string);
	return $string;
}

function sanitize_title($title, $fallback_title = '') {
    $title = strip_tags($title);
    $title = apply_filters('sanitize_title', $title);

    if (empty($title)) {
        $title = $fallback_title;
    }

    return $title;
}

function sanitize_title_with_dashes($title) {
    $title = remove_accents($title);
    $title = strtolower($title);
    $title = preg_replace('/&.+?;/', '', $title); // kill entities
    $title = preg_replace('/[^a-z0-9 _-]/', '', $title);
    $title = preg_replace('/\s+/', ' ', $title);
    $title = str_replace(' ', '-', $title);
    $title = preg_replace('|-+|', '-', $title);
    $title = trim($title, '-');

    return $title;
}

function convert_chars($content, $flag = 'obsolete') { 
	global $wp_htmltranswinuni;

	// Remove metadata tags
	$content = preg_replace('/<title>(.+?)<\/title>/','',$content);
	$content = preg_replace('/<category>(.+?)<\/category>/','',$content);

	// Converts lone & characters into &#38; (a.k.a. &amp;)
	$content = preg_replace('/&([^#])(?![a-z]{1,8};)/i', '&#038;$1', $content);

	// Fix Word pasting
	$content = strtr($content, $wp_htmltranswinuni);

	// Just a little XHTML help
	$content = str_replace('<br>', '<br />', $content);
	$content = str_replace('<hr>', '<hr />', $content);

	return $content;
}

/*
 balanceTags
 
 Balances Tags of string using a modified stack.
 
 @param text      Text to be balanced
 @return          Returns balanced text
 @author          Leonard Lin (leonard@acm.org)
 @version         v1.1
 @date            November 4, 2001
 @license         GPL v2.0
 @notes           
 @changelog       
             1.2  ***TODO*** Make better - change loop condition to $text
             1.1  Fixed handling of append/stack pop order of end text
                  Added Cleaning Hooks
             1.0  First Version
*/
function balanceTags($text, $is_comment = 0) {
	
	if (get_settings('use_balanceTags') == 0) {
		return $text;
	}

	$tagstack = array(); $stacksize = 0; $tagqueue = ''; $newtext = '';

	# WP bug fix for comments - in case you REALLY meant to type '< !--'
	$text = str_replace('< !--', '<    !--', $text);
	# WP bug fix for LOVE <3 (and other situations with '<' before a number)
	$text = preg_replace('#<([0-9]{1})#', '&lt;$1', $text);

	while (preg_match("/<(\/?\w*)\s*([^>]*)>/",$text,$regex)) {
		$newtext = $newtext . $tagqueue;

		$i = strpos($text,$regex[0]);
		$l = strlen($tagqueue) + strlen($regex[0]);

		// clear the shifter
		$tagqueue = '';
		// Pop or Push
		if ($regex[1][0] == "/") { // End Tag
			$tag = strtolower(substr($regex[1],1));
			// if too many closing tags
			if($stacksize <= 0) { 
				$tag = '';
				//or close to be safe $tag = '/' . $tag;
			}
			// if stacktop value = tag close value then pop
			else if ($tagstack[$stacksize - 1] == $tag) { // found closing tag
				$tag = '</' . $tag . '>'; // Close Tag
				// Pop
				array_pop ($tagstack);
				$stacksize--;
			} else { // closing tag not at top, search for it
				for ($j=$stacksize-1;$j>=0;$j--) {
					if ($tagstack[$j] == $tag) {
					// add tag to tagqueue
						for ($k=$stacksize-1;$k>=$j;$k--){
							$tagqueue .= '</' . array_pop ($tagstack) . '>';
							$stacksize--;
						}
						break;
					}
				}
				$tag = '';
			}
		} else { // Begin Tag
			$tag = strtolower($regex[1]);

			// Tag Cleaning

			// Push if not img or br or hr
			if($tag != 'br' && $tag != 'img' && $tag != 'hr') {
				$stacksize = array_push ($tagstack, $tag);
			}

			// Attributes
			// $attributes = $regex[2];
			$attributes = $regex[2];
			if($attributes) {
				$attributes = ' '.$attributes;
			}
			$tag = '<'.$tag.$attributes.'>';
		}
		$newtext .= substr($text,0,$i) . $tag;
		$text = substr($text,$i+$l);
	}  

	// Clear Tag Queue
	$newtext = $newtext . $tagqueue;

	// Add Remaining text
	$newtext .= $text;

	// Empty Stack
	while($x = array_pop($tagstack)) {
		$newtext = $newtext . '</' . $x . '>'; // Add remaining tags to close      
	}

	// WP fix for the bug with HTML comments
	$newtext = str_replace("< !--","<!--",$newtext);
	$newtext = str_replace("<    !--","< !--",$newtext);

	return $newtext;
}


function format_to_edit($content) {
	$content = apply_filters('format_to_edit', $content);
	$content = htmlspecialchars($content);
	return $content;
}

function format_to_post($content) {
	global $wpdb;
	$content = apply_filters('format_to_post', $content);
	return $content;
}

function zeroise($number,$threshold) { // function to add leading zeros when necessary
	$l=strlen($number);
	if ($l<$threshold)
		for ($i=0; $i<($threshold-$l); $i=$i+1) { $number='0'.$number;	}
	return $number;
	}


function backslashit($string) {
	$string = preg_replace('/([a-z])/i', '\\\\\1', $string);
	return $string;
}

function trailingslashit($string) {
    if ( '/' != substr($string, -1)) {
        $string .= '/';
    }
    return $string;
}

function addslashes_gpc($gpc) {
	if (!get_magic_quotes_gpc()) {
		$gpc = addslashes($gpc);
	}
	return $gpc;
}

function antispambot($emailaddy, $mailto=0) {
	$emailNOSPAMaddy = '';
	srand ((float) microtime() * 1000000);
	for ($i = 0; $i < strlen($emailaddy); $i = $i + 1) {
		$j = floor(rand(0, 1+$mailto));
		if ($j==0) {
			$emailNOSPAMaddy .= '&#'.ord(substr($emailaddy,$i,1)).';';
		} elseif ($j==1) {
			$emailNOSPAMaddy .= substr($emailaddy,$i,1);
		} elseif ($j==2) {
			$emailNOSPAMaddy .= '%'.zeroise(dechex(ord(substr($emailaddy, $i, 1))), 2);
		}
	}
	$emailNOSPAMaddy = str_replace('@','&#64;',$emailNOSPAMaddy);
	return $emailNOSPAMaddy;
}

function make_clickable($ret) { // original function: phpBB
	$ret = ' ' . $ret . ' ';
	$ret = preg_replace("#(\s)http://([^\s<>{}()]+[^\s.,<>{}()])#i", "$1<a href='http://$2'>http://$2</a>", $ret);
	$ret = preg_replace("#(\s)www\.([a-z0-9\-]+)\.([a-z0-9\-.\~]+)((?:/[^ <>{}()\n\r]*[^., <>{}()\n\r]?)?)#i", "$1<a href='http://www.$2.$3$4'>www.$2.$3$4</a>", $ret);
	$ret = preg_replace("#(\s)([a-z0-9\-_.]+)@([^,< \n\r]+)#i", "$1<a href='mailto:$2@$3\">$2@$3</a>", $ret);
	$ret = trim($ret);
	return $ret;
}

function convert_smilies($text) {
	global $wp_smiliessearch, $wp_smiliesreplace;
    $output = '';
	if (get_settings('use_smilies')) {
		// HTML loop taken from texturize function, could possible be consolidated
		$textarr = preg_split("/(<.*>)/U", $text, -1, PREG_SPLIT_DELIM_CAPTURE); // capture the tags as well as in between
		$stop = count($textarr);// loop stuff
		for ($i = 0; $i < $stop; $i++) {
			$content = $textarr[$i];
			if ((strlen($content) > 0) && ('<' != $content{0})) { // If it's not a tag
				$content = str_replace($wp_smiliessearch, $wp_smiliesreplace, $content);
			}
			$output .= $content;
		}
	} else {
		// return default text.
		$output = $text;
	}
	return $output;
}


function is_email($user_email) {
	$chars = "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}\$/i";
	if(strstr($user_email, '@') && strstr($user_email, '.')) {
		if (preg_match($chars, $user_email)) {
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}


function strip_all_but_one_link($text, $mylink) {
	$match_link = '#(<a.+?href.+?'.'>)(.+?)(</a>)#';
	preg_match_all($match_link, $text, $matches);
	$count = count($matches[0]);
	for ($i=0; $i<$count; $i++) {
		if (!strstr($matches[0][$i], $mylink)) {
			$text = str_replace($matches[0][$i], $matches[2][$i], $text);
		}
	}
	return $text;
}


// used by wp-mail to handle charsets in email subjects
function wp_iso_descrambler($string) {
  /* this may only work with iso-8859-1, I'm afraid */
  if (!preg_match('#\=\?(.+)\?Q\?(.+)\?\=#i', $string, $matches)) {
    return $string;
  } else {
    $subject = str_replace('_', ' ', $matches[2]);
    $subject = preg_replace('#\=([0-9a-f]{2})#ei', "chr(hexdec(strtolower('$1')))", $subject);
    return $subject;
  }
}


// give it a date, it will give you the same date as GMT
function get_gmt_from_date($string) {
  // note: this only substracts $time_difference from the given date
  preg_match('#([0-9]{1,4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})#', $string, $matches);
  $string_time = gmmktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
  $string_gmt = gmdate('Y-m-d H:i:s', $string_time - get_settings('gmt_offset') * 3600);
  return $string_gmt;
}

// give it a GMT date, it will give you the same date with $time_difference added
function get_date_from_gmt($string) {
  // note: this only adds $time_difference to the given date
  preg_match('#([0-9]{1,4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})#', $string, $matches);
  $string_time = gmmktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
  $string_localtime = gmdate('Y-m-d H:i:s', $string_time + get_settings('gmt_offset')*3600);
  return $string_localtime;
}

// computes an offset in seconds from an iso8601 timezone
function iso8601_timezone_to_offset($timezone) {
  // $timezone is either 'Z' or '[+|-]hhmm'
  if ($timezone == 'Z') {
    $offset = 0;
  } else {
    $sign    = (substr($timezone, 0, 1) == '+') ? 1 : -1;
    $hours   = intval(substr($timezone, 1, 2));
    $minutes = intval(substr($timezone, 3, 4)) / 60;
    $offset  = $sign * 3600 * ($hours + $minutes);
  }
  return $offset;
}

// converts an iso8601 date to MySQL DateTime format used by post_date[_gmt]
function iso8601_to_datetime($date_string, $timezone = USER) {
  if ($timezone == GMT) {
    preg_match('#([0-9]{4})([0-9]{2})([0-9]{2})T([0-9]{2}):([0-9]{2}):([0-9]{2})(Z|[\+|\-][0-9]{2,4}){0,1}#', $date_string, $date_bits);
    if (!empty($date_bits[7])) { // we have a timezone, so let's compute an offset
      $offset = iso8601_timezone_to_offset($date_bits[7]);
    } else { // we don't have a timezone, so we assume user local timezone (not server's!)
      $offset = 3600 * get_settings('gmt_offset');
    }
    $timestamp = gmmktime($date_bits[4], $date_bits[5], $date_bits[6], $date_bits[2], $date_bits[3], $date_bits[1]);
    $timestamp -= $offset;
    return gmdate('Y-m-d H:i:s', $timestamp);
  } elseif ($timezone == USER) {
    return preg_replace('#([0-9]{4})([0-9]{2})([0-9]{2})T([0-9]{2}):([0-9]{2}):([0-9]{2})(Z|[\+|\-][0-9]{2,4}){0,1}#', '$1-$2-$3 $4:$5:$6', $date_string);
  }
}

function popuplinks($text) {
	// Comment text in popup windows should be filtered through this.
	// Right now it's a moderately dumb function, ideally it would detect whether
	// a target or rel attribute was already there and adjust its actions accordingly.
	$text = preg_replace('/<a (.+?)>/i', "<a $1 target='_blank' rel='external'>", $text);
	return $text;
}

?>