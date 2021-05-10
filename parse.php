#!/usr/bin/php
<?php

// fetch data from stdin
$data = file_get_contents("php://stdin");

// extract the body
// NOTE: a properly formatted email's first empty line defines the separation between the headers and the message body
list($data, $body) = explode("\n\n", $data, 2);

// explode on new line
$data = explode("\n", $data);

// define a variable map of known headers
$patterns = array(
  'Return-Path',
  'X-Original-To',
  'Delivered-To',
  'Received',
  'To',
  'Message-Id',
  'Date',
  'From',
  'Subject',
);

// define a variable to hold parsed headers
$headers = array();

// loop through data
foreach ($data as $data_line) {

  // for each line, assume a match does not exist yet
  $pattern_match_exists = false;

  // check for lines that start with white space
  // NOTE: if a line starts with a white space, it signifies a continuation of the previous header
  if ((substr($data_line,0,1)==' ' || substr($data_line,0,1)=="\t") && $last_match) {

    // append to last header
    $headers[$last_match][] = $data_line;
    continue;

  }

  // loop through patterns
  foreach ($patterns as $key => $pattern) {

    // create preg regex
    $preg_pattern = '/^' . $pattern .': (.*)$/';

    // execute preg
    preg_match($preg_pattern, $data_line, $matches);

    // check if preg matches exist
    if (count($matches)) {

      $headers[$pattern][] = $matches[1];
      $pattern_match_exists = true;
      $last_match = $pattern;

    }

  }

  // check if a pattern did not match for this line
  if (!$pattern_match_exists) {
    $headers['UNMATCHED'][] = $data_line;
  }

}

?>