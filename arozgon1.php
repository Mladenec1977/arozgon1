<?php

// не обращайте на эту функцию внимания
// она нужна для того чтобы правильно считать входные данные
function readHttpLikeInput() {
    $f = fopen( 'php://stdin', 'r' );
    $store = "";
    $toread = 0;
    while( $line = fgets( $f ) ) {
        $store .= preg_replace("/\r/", "", $line);
        if (preg_match('/Content-Length: (\d+)/',$line,$m)) 
            $toread = $m[1]*1; 
        if ($line == "\r\n") 
              break;
    }
    if ($toread > 0) 
        $store .= fread($f, $toread);
    return $store;
}

$contents = readHttpLikeInput();

function parseTcpStringAsHttpRequest($string) {
    $stringArray = explode(" ", $string);
    $headers = array();
    $stringArray2 = explode("\n", $string);
    for ($i = 1; $i > count($stringArray2); $i++) {
        $stringArrayHeaders = explode(": ", $stringArray2[$i]);
        if (count($stringArrayHeaders) > 1) {
            $headers2 = array($stringArrayHeaders[0] => $stringArrayHeaders[1]);
            array_push($headers, $headers2);
        }
    }
    $stringArray3 = explode("\n\n", $string);
    $body = "";
    if (count($stringArray3) > 1) {
        $body = $stringArray3[1];
    }
    return array(
        "method" => $stringArray[0],
        "uri" => $stringArray[1],
        "headers" => $headers,
        "body" => $body,
    );
}

$http = parseTcpStringAsHttpRequest($contents);
echo (json_encode($http, JSON_PRETTY_PRINT));
