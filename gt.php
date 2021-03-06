#!/usr/bin/env php
<?php
date_default_timezone_set('Asia/Kolkata');

$logfile = getenv('HOME') . "/.gtimelog/timelog.txt";
if (!file_exists($logfile)) {
    fputs(STDERR,"$logfile: File not found\n");
    return -1;
}
$away=false;
$argv2 = $argv;
unset($argv2[0]);
$fullarg = implode(" ",$argv2);
#Reading
$L = fopen($logfile,"r");
fseek($L,-200,SEEK_END);
$last_dt = $last_time = $lc = 0;
while(!feof($L))
{
    $line = trim(fgets($L));
    $lc++;
    if(!empty($line))
    {
        //echo "$lc: $line\n";
        $ss = explode(' ',$line,3);
        if(count($ss)<3)
            continue;
        list($last_dt,$comment) = $ss;

        $ss = explode(': ',$line,2);
        if(count($ss)<2)
            continue;
        $last_time = strtotime($ss[0]);
        $last_comment = $ss[1];

        if(!empty($argv[1]))
        {
            if("last" == $argv[1])
                $fullarg = $last_comment;
            else if("away" == $argv[1])
            {
                $away = true;
                $fullarg = $last_comment;
            }
        }
    }
}
fclose($L);

function difftime()
{
    global $last_time;
    $diff = time() - $last_time;
    $hh = gmdate("H",$diff);
    $mm = gmdate("i",$diff) . "m";
    if(intval($hh)>0)
        $mm = "{$hh}h" . $mm;
    return $mm;
}

if(empty($argv[1])) //if not arg given, we just show time spent doing the last item
{
    $mm = difftime();
    echo "$mm: $last_comment\n";
    return -1;
}

#Writing
$L = fopen($logfile,"a");
fseek($L,-200,SEEK_END);

$today_date = date('Y-m-d');
if(strstr($last_dt,$today_date) === false) //Not same day
    fputs($L,"\n");

//mark a time period as away between last and this and resume the work
if($away)
{
    $newline = sprintf("%s: away **",date('Y-m-d H:i'));
    fputs($L,$newline . "\n");
}

$newline = sprintf("%s: %s",date('Y-m-d H:i'),$fullarg);
fputs($L,$newline . "\n");
echo difftime() . ": $fullarg\n";
fclose($L);
