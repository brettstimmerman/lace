<?php

/*
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation; either version 2 of the License, or (at your
 * option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General
 * Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/**
 * validateSession()
 *
 * Returns true if a session has already been created in an
 * attempt to prevent abuse of the listener.
 */
function validateSession()
{
  global $A; // Activity object
  global $name;

  $name = getName();

  if (cookieVar(LACE_SESSION_COOKIE) === false && !$A->keyExists($name))
  {
    joinMessage($name);
  }

  $A->update($name);

  setcookie(LACE_SESSION_COOKIE, getCookieString(), time() + 600, LACE_URL_REL);
  setcookie(LACE_NAME_COOKIE, $name, time() + 2592000, LACE_URL_REL);

  return true;
}

/**
 * getCookieString()
 *
 * Returns an MD5 hash of various unique info to
 * use as a unique identifier in a cookie
 */
function getCookieString()
{
  return md5($_SERVER['HTTP_USER_AGENT'].LACE_SECRET_WORD);
}

/**
 * fixMagicQuotes()
 *
 * Remove slashes from all incoming GET/POST/COOKIE data
 *
 * Yoinked straight out of Ryan Grove's Poseidon
 * http://wiki.wonko.com/software/poseidon
 */
function fixMagicQuotes()
{
  set_magic_quotes_runtime(0);

  if (get_magic_quotes_gpc() === 0)
    return;

  function removeMagicSlashes($element)
  {
    if (is_array($element))
      return array_map('removeMagicSlashes', $element);
    else
      return stripslashes($element);
  }

  // Remove slashes from all incoming GET/POST/COOKIE data.
  $_GET    = array_map('removeMagicSlashes', $_GET);
  $_POST   = array_map('removeMagicSlashes', $_POST);
  $_COOKIE = array_map('removeMagicSlashes', $_COOKIE);
}

/**
 * duration_str()
 *
 * Turn a given number of seconds into a human readable
 * duration statement (e.g. 100 seconds -> '1 minute, 40 seconds'
 */
function duration_str($seconds, $short_units = false, $min_units = false)
{
  // This craziness converts a given number of seconds
  // into a human readable time duration
  //
  // $short_units: use short units ('6 m' rather than '6 minutes')
  // $min_units  : minimum units to return ('days' will remove hours, minutes,
  // seconds)
  //
  // Example:
  //
  //    echo duration_str(time() - (time() - 3600));
  //    echo duration_str(time() - (time() - 3600 * 24 * 3.5));
  //    echo duration_str(time() - (time() - 60 * 250), true);
  //    echo duration_str(time() - (time() - 3600 * 24 * 500), false, 'weeks');
  //
  // Outputs:
  //
  //    1 hour
  //      3 days, 12 hours
  //      4 h, 10 m
  //    1 year, 4 months, 1 week

  $seconds = abs((int)$seconds);

  $periods = array
  (
    'years'   => array ( 31557600,'y'),
    'months'  => array ( 2628000, 'mo'),
    'weeks'   => array ( 604800,  'w'),
    'days'    => array ( 86400,   'd'),
    'hours'   => array ( 3600,    'h'),
    'minutes' => array ( 60,      'm'),
    'seconds' => array ( 1,       's'),
  );

  if ($min_units !== false)
  {
    if (is_int($min_units) === false)
    {
      $unit_keys = array_keys($periods);
      $key = array_keys($unit_keys, $min_units);
      for ($i = $key[0] + 1; $i < 7; $i++)
        array_pop($periods);
    }
  }

  foreach ($periods as $units => $data)
  {
    $count = floor($seconds / $data[0]);
    if ($count <= 0)
      continue;

    $units = ($short_units) ? $data[1] : $units;
    $values[$units] = $count;
    $seconds = $seconds % $data[0];
  }

  if (empty($values))
    return false;

  foreach ($values as $key => $value)
  {
    if ($short_units === false && $value == 1)
      $key = mb_substr($key, 0, -1);

    $array[] = $value . ' ' . $key;
  }

  if (!empty($array))
  {
    if (is_int($min_units) === true)
    {
      $count = count($array);
      if ($min_units > $count)
        $min_units = $count;

      for ($i = 0; $i < $min_units; $i++)
        $temp[] = $array[$i];

      $array = $temp;
    }

    return implode(', ', $array);
  }

  return false;
}

/**
 * getVar()
 *
 * Retrieves the given variable from $_GET if it exists
 */
function getVar($var, $default = false)
{
  return (array_key_exists($var, $_GET)) ? trim($_GET[$var]) : $default;
}

/**
 * postVar()
 *
 * Retrieves the given variable from $_POST if it exists
 */
function postVar($var, $default = false)
{
  return (array_key_exists($var, $_POST)) ? trim($_POST[$var]) : $default;
}

/**
 * cookieVar()
 *
 * Retrieves the given variable from $_COOKIE if it exists
 */
function cookieVar($var, $default = false)
{
  return (array_key_exists($var, $_COOKIE)) ? trim($_COOKIE[$var]) : $default;
}

/**
 * real_wordwrap()
 *
 * Wraps words, but doesn't break tags
 */
function real_wordwrap($str, $cols, $cut)
{
  $len = mb_strlen($str);
  $tag = 0;
  $wordlen = 0;
  $result  = '';

  for ($i = 0; $i < $len; $i++)
  {
    $chr = mb_substr($str, $i, 1);
    if ($chr == '<')
      $tag++;
    elseif ($chr == '>')
      $tag--;
    elseif (!$tag && $chr == ' ')
      $wordlen = 0;
    elseif (!$tag)
      $wordlen++;

    if (!$tag && $wordlen > 0 && !($wordlen%$cols))
      $chr .= $cut;

    $result .= $chr;
  }

  return $result;
}

/**
 * str_shorten()
 *
 * Chops a string into chunks and pastes the first and last
 * together with an ellipsis
 */
function str_shorten($str, $len)
{
  $separator = '~|~';
  $chunk = explode($separator,
    mb_substr(chunk_split($str, $len, $separator), 0, -3));

  return $chunk[0]. '...' . $chunk[count($chunk) -1];
}

// -- Compatibility functions ---------------------------------------

/**
 *  file_put_contents()
 *
 *  Included for PHP4 compatability.
 */
if (!function_exists('file_put_contents'))
{
  function file_put_contents($file, $string)
  {
       $f = fopen($file, 'w');
       fwrite($f, $string);
       fclose($f);
  }
}
?>