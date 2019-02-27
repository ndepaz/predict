<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

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
function displayExpectedTimeOfCompletion($diffInSeconds,$progressCount,$maxCount,string $current_timeString){
    displayCompletionTime($diffInSeconds,$progressCount,$maxCount,function($remTime,$approxTime) use($current_timeString){
        $rTime = explode(':',$remTime);
        $rhours=(int) $rTime[0];
        $rMins = (int) $rTime[1];
        $sTime = explode(':',$current_timeString);
        $rmHours =($sTime[1] + $rMins ) / 60;
        $sHour = (int) ($sTime[0] +$rhours+ $rmHours) % 24;
        $sMin = (int) (($sTime[1] + $rMins )% 60);
        printf(" Remaining Time $remTime. Will Take approximatetly $approxTime Expected Completion Time $sHour:$sMin ");
    });
}