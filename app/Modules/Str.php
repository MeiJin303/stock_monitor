<?php
/**
 * Functions for examining and manipulating strings.
 *
 * Includes functions for converting between singulars and plurals.
 * These are mainly intended for converting between class names & tables.
 * They should just work with most regular and common irregular plurals.
 *
 * If they don't work for your Model the best thing to is explictly set the names in your model e.g.
 * <code>
 * Child::table_name('children'); // not actually needed as child/children is known by default
 * </code>
 *
 * But if you really need to add an irregular plural, you can put the below code somewhere near the start of your script
 * <code>
 * str_plural("child", "children"); // not actually needed as child/children is known by default
 * </code>
 *
 *
 * @author Denis Atkinson <atkinson.d@apple.com>
 * @package fw
 */


if( count(get_included_files()) == 1 ) die("Direct access not permitted.");


/**
 *  Test if one string starts with another, optionally ignoring case
 *
 * @param string $haystack The string to check
 * @param string $needle  The prefix to test for
 * @param boolean $case_sensitive   (optional) Defaults to true
 * @return boolean
 */
function str_starts($haystack, $needle, $case_sensitive = true) {
        if (!$case_sensitive) { $needle = strtolower($needle); $haystack = strtolower($haystack); }
	return $needle == substr($haystack, 0, strlen($needle));
}


/**
 *  Test if one string ends with another, optionally ignoring case
 *
 * @param string $haystack The string to check
 * @param string $needle The suffix to test for
 * @param boolean $case_sensitive   (optional) Defaults to true
 * @return boolean
 */
function str_ends($haystack, $needle, $case_sensitive = true) {
        if (!$case_sensitive) { $needle = strtolower($needle); $haystack = strtolower($haystack); }
	return $needle == substr($haystack, 0 - strlen($needle));
}


/**
 *  Convert a string to camel case (lower case, inner word boundries uppercase)
 *
 *  <code>
 *      $camel = str_camel("CaMeL cAsE");
 *      echo $camel;    // prints : camelCase
 *  </code>
 *
 *
 * @param string $string
 * @return string
 */
function str_camel($string) {
	$string = str_replace(" ", "", str_proper($string));
	if ($string == "") return "";
	return strtolower($string{0}) . substr($string, 1);
	// return lcfirst(str_replace(" ", "", ucwords($string))); // lcfirst if a 5.3 feature
}


/**
 *  Insert spaces in camel case string 
 * 
 *  <code>
 *      $decamel = str_decamel("myFieldName");
 *      echo $decamel;    // prints : My Field Name 
 *  </code>
 *
 *
 * @param string $string
 * @return string
 */
function str_decamel($string) { //converts "myFieldName" to "My Field Name"
	$split = preg_split("/(?<=[a-z]) (?=[A-Z])/x", $string);
	return ucwords(implode(' ', $split));
}


/**
 *  Insert spaces in snake case string
 *
 *  <code>
 *      $desnake = str_desnake("my_field_name");
 *      echo $desnake;    // prints : My Field Name
 *  </code>
 *
 * @param string $string
 * @return string
 */
function str_desnake($string) { 
	$split = explode("_", $string);
	return ucwords(implode(' ', $split));
}

/**
 *  Generates a random string of a defined length
 *
 *  <code>
 *      $random = random_str(10);
 *      echo $random;    // prints 10 digit random string
 *  </code>
 *
 * @param int $length
 * @return string
 */
function random_str($length = 10) {
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$random = '';
	for ($i = 0; $i < $length; $i++) {
		$random .= $characters[rand(0, strlen($characters) - 1)];
	}
	return $random;
}


/**
 *  Convert a string to snake case (all lower case, spaces replaced with underscores)
 *
 *  <code>
 *      $snake = str_camel("SnAkE cAsE");
 *      echo $snake;    // prints : snake_case
 *  </code>
 *
 * @param string $string
 * @return string
 */
function str_snake($string) {
	$split = explode(" ", $string);
	return strtolower(implode("_", $split));
}


/**
 *  Return Nth field of a delimtied string
 *
 *  <code>
 *      $field = str_field("A,B,C,D",2);
 *      echo $field;    // prints : C
 *  </code>
 *
 * @param string $string
 * @param string $delim
 * @param int $field (optional)
 * @return string|false
 */
function str_field($str, $delim, $field = 0) {
	$fields = explode($delim, $str);
	if ($field >= count($fields)) return false;
	return $fields[$field];
}


/**
 * Return if a string is a valid IP4 ip address
 *
 * @param string $str
 * @return boolean
 */
function str_is_valid_ip4($str) {
	if (!$str) return false;
	$sl = strlen($str);

	if ($sl < 7) return false;
	if ($sl > 15) return false;
	if ($sl !== strspn($str, '0123456789.')) return false;

	if (substr_count($str, '.') != 3) return false;
	return ip2long($str);
}

/**
 * Returns true if the string is a valid date in SQL/ISO8601 format (e.g. '2011-03-31').
 * Uses regular expressions to ensure a valid format (numbers in the right places, months ranging from 01 to 12, days ranging from 01 to 31) and then uses checkdate to identify invalid data (e.g. 29th Feb on non leap year)
 * 
 * @uses checkdate
 * @param string $str
 * @return boolean 
 */
function str_is_valid_sql_date($str){
    $pattern = '/^(\d\d\d\d)-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/';
    $matches = array();
    $valid = preg_match($pattern, $str, $matches);
    if (!$valid) return false;
    return checkdate($matches[2], $matches[3], $matches[1]);
}


/**
 * Return the ordinal nuber for an integer
 *
 * <code>
 *      $s = str_ord(193);
 *      echo $s;    // prints : 193rd
 * </code>
 *
 * @param int $num
 * @return string
 */
function str_ord($num) {
	$d2 = substr($num, -2);
	if (($d2 == '11') || ($d2 == '12') || ($d2 == '13')) return $num.'th';

	$d1 = substr($num, -1);
	if ($d1 == '1') return $num.'st';
	if ($d1 == '2') return $num.'nd';
	if ($d1 == '3') return $num.'rd';
	return $num.'th';
}


/**
 * Tests if a string is entirely composed of a given set of characters
 *
 * <code>
 *      $ip = str_valid_for_set('10.0.0.1', '0123456789.');
 *      echo $ip ? 'Might be IP' : 'Not an IP address'; // prints : Might be IP
 * </code>
 * 
 * @uses strspn
 * @param string $str   The string to test
 * @param string $set   The list of allowed characters
 * @return boolean
 */
function str_valid_for_set($str, $set) {
	$sl = strlen($str);
	return $sl === strspn($str, $set);
}


define('STR_SAME'  ,  0);
define('STR_LC_FIRST' ,  1);
define('STR_LC_ALL'  ,  2);
define('STR_UC_FIRST' ,  4);
define('STR_UC_WORDS' ,  8);
define('STR_UC_ALL'  , 16);
define('STR_CAMEL'  , 32);
define('STR_SNAKE'  , 64);
define('STR_PLURAL'  , 128);
define('STR_SINGULAR' , 256);
define('STR_NO_SPACE' , 512);


/**
 *  Transforms a string based on the passed values (e.g. uppercase, plural etc)
 *
 *  <code>
 *      $result = str_transform("mY odd STRING", STR_PLURAL | STR_UC_WORDS);
 *      echo $result;   // prints My Odd Strings"
 *  </code>
 *
 *  Some notes on case changes
 *  <li>STR_PLURAL, STR_SINGULAR & STR_SNAKE return lowercase unless directed otherwise</li>
 *  <li>STR_UC_WORDS doesn't currently work with STR_SNAKE</li>
 *  <li>STR_UC_FIRST is the only case change that works with STR_CAMEL</li>
 *
 * @param string $string    Inflected form of original string
 * @param int $convention   Some combination of the STR_XXX constants
 * @return string
 *
 * @uses str_snake
 * @uses str_camel
 * @uses str_plural
 * @uses str_singular
 * @uses str_proper
 */
function str_transform($string, $convention) {
	if ( is_int($string) && is_string($convention) ) {
		$tmp = $string;
		$string = $convention;
		$convention = $tmp;
	}
	if (is_string($convention)) return $convention;

	if ($convention == 0) return $string;

	$string = str_proper($string);

	if ($convention & STR_PLURAL)  $string = str_plural($string);
	if ($convention & STR_SINGULAR) $string = str_singular($string);
	if ($convention & STR_SNAKE)  $string = str_snake($string);
	if ($convention & STR_LC_FIRST) $string{0} = strtolower($string{0}); // no lcfirst before 5.3
	if ($convention & STR_LC_ALL)  $string = strtolower($string);
	if ($convention & STR_UC_WORDS) $string = ucwords($string);
	if ($convention & STR_UC_ALL)  $string = strtoupper($string);
	if ($convention & STR_CAMEL)  $string = str_camel($string);
	if ($convention & STR_UC_FIRST) $string = ucfirst($string);
	if ($convention & STR_NO_SPACE) $string = str_replace(' ', '', $string);
	return $string;
}


/**
 *  Returns a string to a consistent state (space separated with an uppercase letter starting each word)
 *
 * @param string $string
 * @return string
 * @uses str_decamel
 * @uses str_desnake
 */
function str_proper($string) {
	$string = str_decamel($string);
	$string = str_desnake($string);
	return ucwords(strtolower($string));
}


/**
 * Gets or sets the plural form of a word based on the lookup table
 *
 * The lookuptable is loaded from plurals.ini
 *
 * @param string $singular
 * @param string $set_plural   (optional)
 * @param boolean $set_singular (optional)
 * @return string
 * @access private
 */
function str_plural_lookup($singular, $set_plural = null, $set_singular = true) {
	static $lookup = null;
	if ($lookup == null) $lookup = str_reload_plural_table();
	if ($set_plural) {
		$lookup[$singular] = $set_plural;
		if ($set_singular) str_singular_lookup($set_plural, $singular, false);
	}
	return isset($lookup[$singular]) ? $lookup[$singular] : null;
}


/**
 * Gets or sets the singular form of a word based on the lookup table
 *
 * The lookuptable is loaded from plurals.ini
 *
 * @param string $plural    The word to convert to singular form
 * @param string $set_singular (optional) If set stores this singular in the singular lookup table
 * @param boolean $set_plural  (optional) If true stores the reverse pair in the plural lookup table
 * @return string
 * @access private
 */
function str_singular_lookup($plural, $set_singular = null, $set_plural = true) {
	static $lookup = null;
	if ($lookup == null) $lookup = str_reload_singular_table();
	if ($set_singular) {
		$lookup[$plural] = $set_singular;
		if ($set_plural) str_plural_lookup($set_singular, $plural, false);
	}
	return isset($lookup[$plural]) ? $lookup[$plural] : null;
}


/**
 * Algorithmically guess the plural form of the passed string
 * The best this can hope for is to be right slightly more often than it is wrong
 *
 * Correctly converts irregular plurals like...
 * <li>day > days</li>
 * <li>baby > babies</li>
 * <li>radius > radii</li>
 * <li>analysis > analyses (this may not revert correctly when passed to str_singular_alg)</li>
 * <li>bus > buses</li>
 * <li>bush > bushes</li>
 * <li>match > matches</li>
 * <li>box -> boxes</li>
 * <li>fizz -> fizzes</li>
 *
 *
 * @param string $str
 * @return string
 * @access private
 */
function str_plural_alg($str) {
	$str = strtolower($str);
	$last = substr($str, -1);
	if ($last == 'y') {
		if (preg_match('/.*[aeiou]y$/i', $str)) return $str . 's'; // day -> days
		return substr($str, 0, strlen($str) - 1). 'ies'; // baby -> babies
	}

	if ($last == 's') {
		if (preg_match('/.*us$/i', $str)) return substr($str, 0, strlen($str) - 2) . "i"; // radius -> radii
		if (preg_match('/.*is$/i', $str)) {
			$plural = substr($str, 0, strlen($str) - 2) . "es"; // analysis -> analyses
			log_warning("Algorithmically pluralizing '$str' as '$plural'. This may not be reversible. You should explicitly set a plural for this word e.g. str_plural($str','$plural')");
			return $plural;
		}
		return $str . "es";              // bus -> buses
	}

	if ($last == 'h') {
		if (preg_match('/.*sh$/i', $str)) return $str . "es"; // bush -> bushes
		if (preg_match('/.*ch$/i', $str)) return $str . "es"; // match -> matches
	}

	if ($last == 'x') return $str . "es"; // box -> boxes
	if ($last == 'z') return $str . "es"; // fizz -> fizzes

	return $str . 's';
}


/**
 *  Algorithmically guess the singular form of the passed string
 *
 * @param string $str
 * @return string
 * @access private
 * @see str_plural_alg
 */
function str_singular_alg($str) {
	$str = strtolower($str);

	$last = substr($str, -1);
	if (preg_match('/.*i$/i', $str))    return substr($str, 0, strlen($str) - 1) . 'us';
	if (preg_match('/.*[aeiou]ys$/i', $str)) return substr($str, 0, strlen($str) - 1);
	if (preg_match('/.*ies$/i', $str))    return substr($str, 0, strlen($str) - 3) . 'y';
	if (preg_match('/.*ches$/i', $str))   return substr($str, 0, strlen($str) - 2);
	if (preg_match('/.*shes$/i', $str))   return substr($str, 0, strlen($str) - 2);
	if (preg_match('/.*xes$/i', $str))    return substr($str, 0, strlen($str) - 2);
	if (preg_match('/.*zes$/i', $str))    return substr($str, 0, strlen($str) - 2);
	if ($last == 's')        return substr($str, 0, strlen($str) - 1);
	return $str;
}


/**
 *  Force a load of the plurals.ini file
 *
 * @return array
 * @access private
 */
function str_reload_singular_table() {
	return array_flip(str_reload_plural_table());
}


/**
 *  Force a load of the plurals.ini file
 *
 * @return array
 * @access private
 */
function str_reload_plural_table() {
	$plurals = parse_ini_file('plurals.ini');
	return $plurals;
}


/**
 *  Prints some test information about singulars/plurals
 *  @access private
 */
function str_test_plural_arg_against_table() {
	$plurals = str_reload_plural_table();
	foreach ($plurals as $s => $p) {
		$a_s = str_singular_alg($p);
		$a_p = str_plural_alg($s);

		echo "singular $s converts to $a_p \n";
		echo "plural   $p converts to $a_s \n";
		if ($a_s != $s) continue;
		if ($a_p != $p) continue;
		echo "***** remove from ini file : $p = $s \n";
	}

}

/**
 * Does a "whole word" find & replace
 * 
 * @param string $needle
 * @param string $replacement
 * @param string $haystack
 * @return string 
 */

function str_replace_word($needle,$replacement,$haystack){
    $pattern = "/\b".preg_quote($needle)."\b/i";
    $haystack = preg_replace($pattern, $replacement, $haystack);
    return $haystack;
}


/**
 *  Truncates a string to the given length and appends the passed suffix
 *
 * @param string $text
 * @param int $max_length
 * @param string $suffix (optional) Defaults to elipsis (i.e. '...')
 */

function str_truncate($text, $max_length, $suffix = '...'){
    if ($max_length > strlen($text)) return $text;
   
    if (!$suffix) $suffix = '';
    
    $max_length -= strlen($suffix);
    
    if ($max_length <= 0) return $suffix;

    return substr($text, 0,$max_length).$suffix;
}


/**
 * Finds the closest match for a string in the passed list of words
 *
 * @param string $search
 * @param mixed $words	(optional) If a string is passed the wordlist with that name is used. IF an array is passed the contents are used. If null is passed (or it is ommited) the wordlist en_60 is used
 * @param function $distance_function (optional) Defaults to built in levenshtein
 * @param double $threshold maximum distance to consider. IF this is set and not met then null will be returned
 * @param boolean $add_phonetic IF true also compares metaphone values of the words
 * @return string 
 * 
 */
function str_closest($search, $words = null, $distance_function = 'levenshtein', $threshold = false, $add_phonenetic = true) {
    if (!function_exists($distance_function))
        log_fatal("String distance function '$distance_function' does not exist!");

    $shortest = -1;
    $closest = false;

    if ($words == null) {
        fw_require('word');
        $words = word_list('en-60');       
    }

    $search = trim($search);
    $lc_search = strtolower($search);
    $mp_search = metaphone($search);
    
    $mul = $add_phonenetic ? 2 : 1;
    
    foreach ($words as $word) {
       if ($search == $word) return $search;
 
       $d  = call_user_func($distance_function, $lc_search, strtolower($word));
       if ($add_phonenetic) $d += call_user_func($distance_function, $mp_search, metaphone($word));
       
       
       if ($threshold && ($d > $threshold * $mul)) continue;
       if ($d <= $shortest || $shortest < 0) {
            $closest = $word;
            $shortest = $d;
       }
    }
    return $closest;
}


/**
 * Splits a query string in to tokens. Allows for quoted phrases with single or double quotes.
 * Strips all punctuation and spaces except:
 *      apostrophe ['], hypen [-] and underscore [_] when the appear within words
 *      anything that appears within a pair of single or double quotes
 * <code>
 *      $tokens = str_query_tokens("'Jaws 4: The Revenge' man-eating shark movie");
 *      echo implode(" | ", $tokens); // prints Jaws 4: The Revenge | man-eating | shark | movie
 * </code>
 * 
 * @param string $query The query string to tokenize
 * @param boolean $include_quotes (optional) If true leaves the surrounding quote chars on phrase tokens. Defaults to false.
 * @param boolean|string $include_punctuation (optional) If false (the default) all punctuation is stripped, except as described above. If true all punctioation is returned but each punctiation charater is returned as a separate token. If a string is passed the only punctioation contained in that string are returned. Defaults to false.
 * @return array 
 */
function str_query_tokens($query, $include_quotes = false, $include_punctuation = false){
     static $quotes = '"\'';
   
    $chars = str_split($query);
    
    $token = '';
    $quote = false;
    $tokens = array();
    
    foreach($chars as $c){
        
        // if we're in a quoted section check if we've come to the closing quote.
        if ($quote && ($c == $quote)){
            if ($include_quotes) $token .= $quote;
            if ($token && ((!$include_quotes) || ($token != "$quote$quote"))) {
                $tokens[] = $token;
            }
            $quote = false;
            $token = '';
            continue;
        }
        
        // if we're not in a quoted section check if we've found an opening quote
        if ((!$quote) && ((strpos($quotes,$c) !== false) && (($c != "'") || !$token))){
            $quote = $c;
            if ($include_quotes) $token .= $quote;
            continue;
        }
      
        // if we're in a quoted section we accept anything
        if ($quote) {
            $token .= $c;
            continue;
        }        
        
        $keep_chars = is_string($include_punctuation) ? $include_punctuation : '';
        $include_punctuation = $include_punctuation && !$keep_chars;
        // check if we're at a word boundry
        if (ctype_space($c) || ctype_punct($c)) {           
            // some punctuation is allowed if it's in a word
            if ($token && (strpos("_'-", $c) !== false)) $token .= $c;
            else {              
                if ($token) $tokens[] = $token;
                
                // check for logical operators, and add them to the token list
                if ($include_punctuation || (strpos($keep_chars, $c) !== false)) $tokens[] = $c;
                
                $token = '';
            }            
            continue;
        }
        
        
        
        // normal charcter.
        $token .= $c;
    }
    if ($token) $tokens[] = $token;

    return $tokens;
}

/**
 * Split a string into lines regardless of platform line ending
 * @param string $str
 * @return array 
 */
function str_lines($str){
        return preg_split("/(\r\n|\n|\r)/", $str);
}

?>