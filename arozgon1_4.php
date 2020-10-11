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

function outputHttpResponse($statuscode, $statusmessage, $headers, $body) {
    echo 'HTTP/1.1 ' . $statuscode . ' ' . $statusmessage . "\n";
    echo 'Date:' . date(DATE_RFC822) . "\n";
    for ($i = 0; $i < count($headers); $i++) {
        echo $headers[$i][0] . ": " . $headers[$i][1] . "\n";
    }
    echo "\n";
    echo $body;
}

function processHttpRequest($method, $uri, $headers, $body) {
    $statuscode = 200;
    $statusmessage = 'Ok';
    if ($method == "GET") {
        $uri_array = explode("/", $uri);
        if ($uri_array[1] == 'sum') {
            $num_uri = explode("=", $uri_array[2]);
            if ($num_uri[0] != 'nums') {
                $statuscode = 400;
                $statusmessage = 'Bad Request';
            }
            $sumBody = explode(",", $num_uri[1]);
            $body = 0;
            for ($i = 0; $i < count($sumBody); $i++) {
                $body = $body + $sumBody[$i];
            }
        } else {
            $statuscode = 400;
            $statusmessage = 'Not Found';
            $body = 'not found';
        }
    }
    if ($method == "POST") {
        $check = false;
        if ($uri != '/api/checkLoginAndPassword') {
            $check = true;
        }
        for ($i = 0; $i < count($headers); $i++) {
            if ($check == true){
                break;
            }
            if ($headers[$i][0] == 'Content-Type' && $headers[$i][1] != 'application/x-www-form-urlencoded'){
                $check = true;
                break;
            }
        }
        if ($check) {
            $statuscode = 400;
            $statusmessage = 'Not Found';
            $body = 'not found';
        } else {
            if ($password = file_get_contents ('passwords.txt')) {
                $userPasswordArray = explode("\n", $password);
                $textPas1 = explode("&", $body);
                $textPas2 = explode("=", $textPas1[0]);
                $textPas3 = explode("=", $textPas1[1]);
                $textPasResult = $textPas2[1] . ':' . $textPas3[1];
                for ($i = 0; $i < count($userPasswordArray); $i++) {
                    if (strcmp($userPasswordArray[$i], $textPasResult)) {
                        $statuscode = 200;
                        $statusmessage = 'Ok';
                        $body = '<h1 style="color:#008000">FOUND</h1>';
                        break;
                    }
                    if ($i == (count($userPasswordArray) - 1)) {
                        $statuscode = 400;
                        $statusmessage = 'Login and password no found';
                        $body = 'login and password no found';
                    }
                }

            } else {
                $statuscode = 500;
                $statusmessage = 'Internal Server Error';
            }
        }
    }
    outputHttpResponse($statuscode, $statusmessage, $headers, $body);
}


function parseTcpStringAsHttpRequest($string) {
    $stringArray = explode(" ", $string);
    $headers = array();
    $stringArray2 = explode("\n", $string);
    for ($i = 1; $i < count($stringArray2); $i++) {
        $stringArrayHeaders = explode(": ", $stringArray2[$i]);
        if (count($stringArrayHeaders) > 1) {
            $ar_head = array($stringArrayHeaders[0], $stringArrayHeaders[1]);
            array_push($headers, $ar_head);
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
processHttpRequest($http["method"], $http["uri"], $http["headers"], $http["body"]);

