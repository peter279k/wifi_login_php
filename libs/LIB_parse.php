<?php
/*
########################################################################
Copyright 2007, Michael Schrenk
   This software is designed for use with the book,
   "Webbots, Spiders, and Screen Scarpers", Michael Schrenk, 2007 No Starch Press, San Francisco CA

W3C® SOFTWARE NOTICE AND LICENSE

This work (and included software, documentation such as READMEs, or other
related items) is being provided by the copyright holders under the following license.
 By obtaining, using and/or copying this work, you (the licensee) agree that you have read,
 understood, and will comply with the following terms and conditions.

Permission to copy, modify, and distribute this software and its documentation, with or
without modification, for any purpose and without fee or royalty is hereby granted, provided
that you include the following on ALL copies of the software and documentation or portions thereof,
including modifications:
   1. The full text of this NOTICE in a location viewable to users of the redistributed
      or derivative work.
   2. Any pre-existing intellectual property disclaimers, notices, or terms and conditions.
      If none exist, the W3C Software Short Notice should be included (hypertext is preferred,
      text is permitted) within the body of any redistributed or derivative code.
   3. Notice of any changes or modifications to the files, including the date changes were made.
      (We recommend you provide URIs to the location from which the code is derived.)

THIS SOFTWARE AND DOCUMENTATION IS PROVIDED "AS IS," AND COPYRIGHT HOLDERS MAKE NO REPRESENTATIONS OR
WARRANTIES, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO, WARRANTIES OF MERCHANTABILITY OR FITNESS
FOR ANY PARTICULAR PURPOSE OR THAT THE USE OF THE SOFTWARE OR DOCUMENTATION WILL NOT INFRINGE ANY THIRD
PARTY PATENTS, COPYRIGHTS, TRADEMARKS OR OTHER RIGHTS.

COPYRIGHT HOLDERS WILL NOT BE LIABLE FOR ANY DIRECT, INDIRECT, SPECIAL OR CONSEQUENTIAL DAMAGES ARISING OUT
OF ANY USE OF THE SOFTWARE OR DOCUMENTATION.

The name and trademarks of copyright holders may NOT be used in advertising or publicity pertaining to the
software without specific, written prior permission. Title to copyright in this software and any associated
documentation will at all times remain with copyright holders.
########################################################################
*/

########################################################################
#
# LIB_parse.php     Parse Routines
#
#-----------------------------------------------------------------------
# FUNCTIONS
#
#    split_string()   Returns the portion of a string either before
#                     or after a delineator. The returned string may
#                     or may not include the delineator.
#
#    return_between() Returns the portion of a string that falls
#                     between two delineators, exclusive or inclusive
#                     of the delineators.
#
#    parse_array()    Returns an array containing all occurrences of
#                     text that falls between a set of delineators.
#
#    get_attribute()  Returns the value of a HTML tag attribute
#
#    remove()         Removes all occurrences of a string from
#                     another string.
#
#    tidy_html()      Puts raw HTML into a known state with proper
#                     with parsable syntax
#
########################################################################

/***********************************************************************
Parse Constants (scope = global)
----------------------------------------------------------------------*/
# Specifies if parse includes the delineator
define("EXCL", true);
define("INCL", false);
# Specifies if parse returns the text before or after the delineator
define("BEFORE", true);
define("AFTER", false);

/***********************************************************************
split_string($string, $delineator, $desired, $type)
-------------------------------------------------------------
DESCRIPTION:
        Returns a potion of the string that is either before or after
        the delineator. The parse is not case sensitive, but the case of
        the parsed string is not effected.
INPUT:
        $string         Input string to parse
        $delineator     Delineation point (place where split occurs)
        $desired        BEFORE: return portion before delineator
                        AFTER:  return portion before delineator
        $type           INCL: include delineator in parsed string
                        EXCL: exclude delineator in parsed string
***********************************************************************/
function split_string($string, $delineator, $desired, $type)
{
    # Case insensitive parse, convert string and delineator to lower case
    $lc_str = strtolower($string);
    $marker = strtolower($delineator);
    
    # Return text BEFORE the delineator
    if ($desired == BEFORE) {
        if ($type == EXCL) {  // Return text ESCL of the delineator
            $split_here = strpos($lc_str, $marker);
        } else { // Return text INCL of the delineator
            $split_here = strpos($lc_str, $marker)+strlen($marker);
        }
        
        $parsed_string = substr($string, 0, $split_here);
    } # Return text AFTER the delineator
    else {
        if ($type==EXCL) {    // Return text ESCL of the delineator
            $split_here = strpos($lc_str, $marker) + strlen($marker);
        } else { // Return text INCL of the delineator
            $split_here = strpos($lc_str, $marker) ;
        }
        
        $parsed_string =  substr($string, $split_here, strlen($string));
    }
    return $parsed_string;
}

/***********************************************************************
$value = return_between($string, $start, $end, $type)
-------------------------------------------------------------
DESCRIPTION:
        Returns a substring of $string delineated by $start and $end
        The parse is not case sensitive, but the case of the parsed
        string is not effected.
INPUT:
        $string         Input string to parse
        $start          Defines the beginning of the sub string
        $end            Defines the end of the sub string
        $type           INCL: include delineators in parsed string
                        EXCL: exclude delineators in parsed string
***********************************************************************/
function return_between($string, $start, $stop, $type)
{
    $temp = split_string($string, $start, AFTER, $type);
    return split_string($temp, $stop, BEFORE, $type);
}

/***********************************************************************
$array = parse_array($string, $open_tag, $close_tag)
-------------------------------------------------------------
DESCRIPTION:
        Returns an array of strings that exists repeatedly in $string.
        This function is usful for returning an array that contains
        links, images, tables or any other data that appears more than
        once.
INPUT:
        $string     String that contains the tags
        $open_tag   Name of the open tag (i.e. "<a>")
        $close_tag  Name of the closing tag (i.e. "</title>")

***********************************************************************/
function parse_array($string, $beg_tag, $close_tag)
{
    preg_match_all("($beg_tag(.*)$close_tag)siU", $string, $matching_data);
    return $matching_data[0];
}

/***********************************************************************
$value = get_attribute($tag, $attribute)
-------------------------------------------------------------
DESCRIPTION:
        Returns the value of an attribute in a given tag.
INPUT:
        $tag         The tag that contains the attribute
        $attribute   The name of the attribute, whose value you seek

***********************************************************************/
function get_attribute($tag, $attribute)
{
    # Use Tidy library to 'clean' input
    $cleaned_html = tidy_html($tag);
    
    # Remove all line feeds from the string
    $cleaned_html = str_replace("\r", "", $cleaned_html);
    $cleaned_html = str_replace("\n", "", $cleaned_html);
    
    # Use return_between() to find the properly quoted value for the attribute
    return return_between($cleaned_html, strtoupper($attribute)."=\"", "\"", EXCL);
}

/***********************************************************************
remove($string, $open_tag, $close_tag)
-------------------------------------------------------------
DESCRIPTION:
        Removes all text between $open_tag and $close_tag
INPUT:
        $string     The target of your parse
        $open_tag   The starting delimitor
        $close_tag  The ending delimitor

***********************************************************************/
function remove($string, $open_tag, $close_tag)
{
    # Get array of things that should be removed from the input string
    $remove_array = parse_array($string, $open_tag, $close_tag);
    
    # Remove each occurrence of each array element from string;
    for ($xx=0; $xx<count($remove_array); $xx++) {
        $string = str_replace($remove_array, "", $string);
    }
    
    return $string;
}

/***********************************************************************
tidy_html($input_string)
-------------------------------------------------------------
DESCRIPTION:
        Returns a "Cleans-up" (parsable) version raw HTML
INPUT:
        $string     raw HTML

OUTPUT:
        Returns a string of cleaned-up HTML
***********************************************************************/
function tidy_html($input_string)
{
    // Detect if Tidy is in configured
    if (function_exists('tidy_get_release')) {
        # Tidy for PHP version 4
        if (substr(phpversion(), 0, 1) == 4) {
            tidy_setopt('uppercase-attributes', true);
            tidy_setopt('wrap', 800);
            tidy_parse_string($input_string);
            $cleaned_html = tidy_get_output();
        }
        # Tidy for PHP version 5
        if (substr(phpversion(), 0, 1) == 5) {
            $config = array(
                           'uppercase-attributes' => true,
                           'wrap'                 => 800);
            $tidy = new tidy;
            $tidy->parseString($input_string, $config, 'utf8');
            $tidy->cleanRepair();
            $cleaned_html  = tidy_get_output($tidy);
        }
    } else {
        # Tidy not configured for this computer
        $cleaned_html = $input_string;
    }
    return $cleaned_html;
}
