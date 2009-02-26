<?php

// -- Multibyte Compatibility functions ---------------------------------------

/**
 *  mb_ereg_replace()
 *
 *  Included for mbstring pseudo-compatability.
 */
if (!function_exists('mb_ereg_replace'))
{
  function mb_ereg_replace($search, $replace, $str)
  {
    return ereg_replace($search, $replace, $str);
  }
}

/**
 *  mb_internal_encoding()
 *
 *  Included for mbstring pseudo-compatability.
 */
if (!function_exists('mb_internal_encoding'))
{
  function mb_internal_encoding($enc) {return true; }
}

/**
 *  mb_strlen()
 *
 *  Included for mbstring pseudo-compatability.
 */
if (!function_exists('mb_strlen'))
{
  function mb_strlen($str)
  {
    return strlen($str);
  }
}

/**
 *  mb_strpos()
 *
 *  Included for mbstring pseudo-compatability.
 */
if (!function_exists('mb_strpos'))
{
  function mb_strpos($haystack, $needle, $offset=0)
  {
    return strpos($haystack, $needle, $offset);
  }
}

/**
 *  mb_substr()
 *
 *  Included for mbstring pseudo-compatability.
 */
if (!function_exists('mb_substr'))
{
  function mb_substr($str, $start, $length=0)
  {
    return substr($str, $start, $length);
  }
}

/**
 *  mb_substr_count()
 *
 *  Included for mbstring pseudo-compatability.
 */
if (!function_exists('mb_substr_count'))
{
  function mb_substr_count($haystack, $needle)
  {
    return substr_count($haystack, $needle);
  }
}

?>