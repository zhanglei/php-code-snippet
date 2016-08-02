<?php
set_time_limit(0);
function handle_http_request($host, $port) {
    $max_backlog = 16;
    $res_content = "HTTP/1.1 200 OK\n";
	$res_content .= "Content-Length: 15\n";
    $res_content .= "Content-Type: text/plain; charset=UTF-8\n";
	$res_content .= "PHP HTTP Server\n";
    $res_len = strlen($res_content);

    //Create, bind and listen to socket
    if(($socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === FALSE) {
		echo "socket_create() failed: " . socket_strerror(socket_last_error()) . "\n";
        exit;
    }   

    if((socket_bind($socket, $host, $port)) === FALSE) {
        echo "socket_bind() failed: " . socket_strerror(socket_last_error($socket)) . "\n";
        exit;
    }

    if((socket_listen($socket, $max_backlog)) === FALSE) {
		echo "socket_listen() failed: " . socket_strerror(socket_last_error($socket)) . "\n";
        exit;
    }

    //Loop
    while(TRUE) {
        if(($accept_socket = socket_accept($socket)) === FALSE) {
            continue;
        } else {
            socket_write($accept_socket, $res_content, $res_len);   
            socket_close($accept_socket);
        }
    }
}
//Run as daemon process.
function run() {
    if(($pid1 = pcntl_fork()) === 0) { //First child process
        posix_setsid(); //Set first child process as the session leader.
        if(($pid2 = pcntl_fork()) === 0) { //Second child process, which run as daemon.
            handle_http_request('127.0.0.1', 9999); 
        } else {
            exit;
        }
    } else {
        //Wait for first child process exit;
        pcntl_wait($status);
    }
}

run();

