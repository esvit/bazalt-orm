<?php

$link = mysql_connect('localhost', 'root', '');
if (!$link) {
    die('Could not connect: ' . mysql_error());
}

$sqlFile = dirname(__FILE__) . '/sakila.sql';

if(!file_exists($sqlFile)) {
    exit('DB dump not found');
}
print "Init DB\nStart\n";
$lines = file($sqlFile);
$buff = '';
foreach ($lines as $line_num => $line) {
    $line = trim($line);
    if(strlen($line) > 0 && strstr($line,'--') === false) {
        if(strstr($line,';') === false) {
            $buff .= $line."\n";
            continue;
        } else {
            $buff .= $line."\n";
            $line = $buff;
            $buff = '';
        }
        $result = mysql_query($line, $link);
        if (!$result) {
            die(mysql_error() . "\nQuery:".$line);
        }        
        print '.';
    }
}
print "\nDone";

mysql_close($link);