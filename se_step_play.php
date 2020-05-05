<?php
 
include_once "/lib/sd_spc.php";
 
$step_play_speed = 1000; // ms per bar
$step_play_dir = +1;
$step_play_sid = array(0, 0, 0, 0);
 
function step_play_setup($play_id, $sid, $vref = 8, $mode = "half", $accel = 10000)
{
    global $step_play_sid;
 
    if(spc_request_sys($sid, "get did") != "40002403")
        exit("step_play_setup: expansion not found\r\n");
 
    spc_request_dev($sid, "set vref stop 2");
    spc_request_dev($sid, "set vref drive $vref");
    spc_request_dev($sid, "set mode $mode");
    spc_request_dev($sid, "set accel $accel");
 
    $step_play_sid[$play_id] = $sid;
}
 
function step_play_tempo($tempo)
{
    global $step_play_speed;
 
    $step_play_speed = (int)(1000.0 / ($tempo / 60.0));
}
 
function step_play_dir($dir)
{
    global $step_play_dir;
 
    if($dir >= 0)
        $step_play_dir = +1;
    else
        $step_play_dir = -1;
}
 
function step_play_encode($score)
{
    global $step_play_speed;
 
    $score_array = explode(" ", $score);
    $score_count = count($score_array);
 
    $score_encoded = "";
 
    for($i = 0; $i < $score_count; $i++)
    {
        if(!($note = $score_array[$i]))
            continue;
 
        if(strtoupper($note[0]) == "R")
        { /* rest */
            $dur_fp = (float)substr($note, 1);
            $dur_ms = (int)round($step_play_speed / $dur_fp);
 
            $score_encoded .= int2bin(0, 2);
            $score_encoded .= int2bin($dur_ms, 2);
        }
        else
        { /* tone */
            $tone = (int)$note[0] * 12; // octave * 12
 
            // C  C# D  D# E  F  F# G  G# A A# B
            // 0  1  2  3  4  5  6  7  8  9 10 11
 
            switch(strtoupper($note[1]))
            {
                case "C":
                    $tone += 0;
                    break;
                case "D":
                    $tone += 2;
                    break;
                case "E":
                    $tone += 4;
                    break;
                case "F":
                    $tone += 5;
                    break;
                case "G":
                    $tone += 7;
                    break;
                case "A":
                    $tone += 9;
                    break;
                case "B":
                    $tone += 11;
                    break;
                default:
                    exit("encode_score: unknown tone '" . $note[1] . "'\r\n");
                    break;
            }
 
            if($note[2] == "#")
            {
                $tone++;
                $dur_fp = (float)substr($note, 3);
            }
            else
            if($note[2] == "b")
            {
                $tone--;
                $dur_fp = (float)substr($note, 3);
            }
            else
                $dur_fp = (float)substr($note, 2);
 
            $dur_ms = (int)($step_play_speed / $dur_fp);
 
            $octave = $tone / 12;
            $tone = $tone % 12;
 
            $freqA = 440.0 * pow(2, ($octave - 4));
            $freq = (int)round($freqA * pow(2, ($tone - 9) / 12.0));
 
            $score_encoded .= int2bin($freq, 2);
            $score_encoded .= int2bin($dur_ms, 2);
        }
    }
 
    return $score_encoded;
}
 
function step_play_melody($melody)
{
    global $step_play_dir;
    global $step_play_sid;
 
    while($melody)
    {
        $freq = bin2int($melody, 0, 2);
        $dur_ms = bin2int($melody, 2, 2);
 
        if($freq)
        {
            if($step_play_dir > 0)
                spc_request_dev($step_play_sid[0], "goto +1000000 $freq");
            else
                spc_request_dev($step_play_sid[0], "goto -1000000 $freq");
            usleep($dur_ms * 1000);
            spc_request_dev($step_play_sid[0], "stop 0");
        }
        else // zero frequency is 'rest'
            usleep($dur_ms * 1000);
 
        $melody = substr($melody, 4);
    }
}
 
function step_play_harmony($melody1, $melody2)
{
    global $step_play_dir;
    global $step_play_sid;
 
    $pid_st0 = pid_open("/mmap/st0");
    pid_ioctl($pid_st0, "start");
 
    $melody = array($melody1, $melody2);
    $melody_next_ms = array(0, 0);
 
    while($melody[0] || $melody[1] || $melody_next_ms[0] || $melody_next_ms[1])
    {
        for($i = 0; $i < 2; $i++)
        {
            if($melody_next_ms[$i])
            {
                if($melody_next_ms[$i] <= pid_ioctl($pid_st0, "get count"))
                {
                    spc_request_dev($step_play_sid[$i], "stop 0");
                    $melody_next_ms[$i] = 0;
                }
            }
            else
            {
                if($melody[$i])
                {
                    $freq = bin2int($melody[$i], 0, 2);
                    $dur_ms = bin2int($melody[$i], 2, 2);
 
                    $melody[$i] = substr($melody[$i], 4);
 
                    if($freq)
                    {
                        if($step_play_dir > 0)
                            spc_request_dev($step_play_sid[$i], "goto +1000000 $freq");
                        else
                            spc_request_dev($step_play_sid[$i], "goto -1000000 $freq");
                    }
 
                    $melody_next_ms[$i] = pid_ioctl($pid_st0, "get count") + $dur_ms;
                }
            }
        }
    }
 
    pid_close($pid_st0);
}
 
?>
 
