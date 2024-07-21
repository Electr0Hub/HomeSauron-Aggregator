<?php

function ipLookupByHostName(string $targetHostName): string|false
{
    // Using socket server IP address to get the IP of network (kind of 192.168.2.*)
    $socketIp = config('streaming.socket.ip');

    $baseIP = preg_replace('/\.\d+$/', '', $socketIp);
    $start = 1;
    $end = 255;
    $port = 81;

    for ($i = $start; $i <= $end; $i++) {
        $ip = $baseIP . '.' . $i;

        $socket = @fsockopen($ip, $port, $errno, $errstr, 1);

        if ($socket) {
            fclose($socket);
            $hostname = gethostbyaddr($ip);

            if ($hostname === $targetHostName) {
                return $ip;
            }
        }
    }

    return false;
}
