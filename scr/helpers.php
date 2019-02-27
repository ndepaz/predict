<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
/**
 * Given a text, it checks if the text contains an the string occurrence.
 * @param string $inString
 * @param string $text
 * @return bool
 */
function inString($inString,$text) : bool {
    if(is_null($text)|| is_null($inString)){
        return false;
    }
    return strpos($text, $inString ) !== false;
}
/**
 * If a string is null, empty or is whitespace, return a null value.
 * if a string has a value then, returns the trim value.
 * @param string $text
 * @return string
 */
function trimOrnull($text){
    if(ctype_space($text)){
        return null;
    }
    return trim($text);
}
/**
 * searches multiple strings in text.
 * 
 * @param string $text
 * @param string|array $inStrings
 * @return bool
 */
function inText($text,$inStrings) : bool {
    $params = array_flatten(func_get_args());
    unset($params[0]);
    $inStrings =$params;
    foreach($inStrings as $inString){
        if(inString($inString,$text)){
            return true;
        }
    }
    return false;
}
/**
 * Given a list of keywords in a string row, returns true if found, or false if not.
 * @param strings $keywords
 * @param array(strings) $row
 * @return bool
 */
function cellInRowHas($keywords,$row) : bool{
    foreach($row as $index=> $cell){
       if(inText($cell,$keywords)){
           return true;
       }
    }
    return false;
}
function getRegextMatches($regexPattern,$string) {
    $matches = null;
    preg_match_all($regexPattern, $string, $matches);
    if($matches == []){
        return NULL;
    }
    return $matches;
}
function extractRegex($input,$regexPattern,$all = false) {
    $matches = getRegextMatches($regexPattern, $input);
    $result = array_flatten($matches);

    return $all ? $result: array_first($result);
}
function extractFirstName($input) {
    $regexPattern = '/,\s*[a-zA-Z]*/';
    $matches = getRegextMatches($regexPattern, $input);
    $firstName = is_null($matches)?null: substr($matches[0][0], 2);
    return $firstName;
}

function extractMiddleName($input) {
    //$input = trimOrnull($input); Can be enabled if wish to trim
    $regexPattern = '/\s[a-zA-Z]{1}$/';
    $removeDotAtEndOfString = preg_match('/\.$/', $input);
    if($removeDotAtEndOfString){
        $input = rtrim($input,".");
    }
    $matches=getRegextMatches($regexPattern, $input);
    $middleName = array_flatten($matches) === []?null: trimOrnull($matches[0][0]);
    return $middleName;
}

function extractLastName($input) {
    $regexPattern = '/([a-zA-Z]*\s)*([a-zA-Z]*)*/';
    $matches = getRegextMatches($regexPattern, $input);
    $lastName = is_null($matches)?null: $matches[0][0];
    return $lastName;
}

function convertArray2Object($defs) {
    $innerfunc = function ($a) use ( &$innerfunc ) {
       return (is_array($a)) ? (object) array_map($innerfunc, $a) : $a; 
    };
    return (object) array_map($innerfunc, $defs);
}
function searchForId($field,$id, $array) {
    foreach ($array as $key => $val) {
        if ($val[$field] === $id) {
            return $key;
        }
    }
    return null;
}

function shouldUpdateModel(Model $model, $months = 1) : bool {
    $createAt = Carbon::parse($model->created_at);
    $updateAt = $model->updated_at ?? $createAt;
    $updatedElapsedMonths = $updateAt->diffInMonths();
    return $updatedElapsedMonths > $months ;
}

function approxSecondsToComplete($diffInSeconds,$progressCount,$maxCount,$started_at=0){
    return intval((($diffInSeconds/($progressCount ===  0 ? 0.01:$progressCount) ) * $maxCount) + $started_at);
}
function secondsToTime(int $seconds){
    return gmdate("H:i:s", $seconds);
}
function secondsRemaining($approxSecondsToComplete,$diffInSeconds){
    return (int) ($approxSecondsToComplete- $diffInSeconds);
}
function displayCompletionTime($diffInSeconds,$progressCount,$maxCount,callable $display = null){
    $seconds = approxSecondsToComplete($diffInSeconds,$progressCount,$maxCount);
    $secondsRemaining = secondsRemaining($seconds,$diffInSeconds);
    $approximateTime = secondsToTime($seconds);
    $remainingTime = secondsToTime($secondsRemaining);
    if($display){
        $display($remainingTime,$approximateTime,$secondsRemaining);
    } else{
        printf(" Remaining Time $remainingTime. Will Take approximatetly $approximateTime");
    }
}
function displayExpectedTimeOfCompletion($diffInSeconds,$progressCount,$maxCount){
    displayCompletionTime($diffInSeconds,$progressCount,$maxCount,function($remTime,$approxTime){
        $rTime = explode(':',$remTime);
        $rhours=(int) $rTime[0];
        $rMins = (int) $rTime[1];
        $sTime = explode(':',now()->toTimeString());
        $rmHours =($sTime[1] + $rMins ) / 60;
        $sHour = (int) ($sTime[0] +$rhours+ $rmHours) % 24;
        $sMin = (int) (($sTime[1] + $rMins )% 60);
        printf(" Remaining Time $remTime. Will Take approximatetly $approxTime Expected Completion Time $sHour:$sMin ");
    });
}