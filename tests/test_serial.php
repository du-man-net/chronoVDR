<?php

/* open the tty, configure the IO */
$fd = dio_open('/dev/ttyACM0', O_RDWR | O_NOCTTY | O_NONBLOCK);
if (!$fd) {
    /* handle the failure to open the file */
} else {
    dio_fcntl($fd, F_SETFL, O_SYNC);
    dio_tcsetattr($fd, array('baud' => 115200,
        'bits' => 8,
        'stop' => 1,
        'parity' => 0));
    /* save the file descriptor in a session var */
    //$_SESSION['fd'] = $fd;
    $data = '';
    $d = '*';

    while ($d != 'i') {
        $d = dio_read($fd, 1);
    }
    $data .= $d;
    
    for($i=1;$i<32;$i++){
        $d = dio_read($fd, 1);
        //var_dump($d);
        $data .= $d;
    }
    
    for($i=1;$i<32;$i++){
        $d = dio_read($fd, 1);
        //var_dump($d);
        $data .= $d;
    }
    
    for($i=1;$i<32;$i++){
        $d = dio_read($fd, 1);
        //var_dump($d);
        $data .= $d;
    }
    
    echo $data;
}