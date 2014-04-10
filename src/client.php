<?php

ini_set('default_socket_timeout', 1);

require_once 'vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$factory = new React\Stomp\Factory($loop);

$ipMapping = array(
    'rabbit1' => '192.168.40.10',
    'rabbit2' => '192.168.40.11',
    'rabbit3' => '192.168.40.12',
    'rabbit4' => '192.168.40.13',
    'rabbit5' => '192.168.40.14',
    'rabbit6' => '192.168.40.15',
    'rabbit7' => '192.168.40.16',
    'rabbit8' => '192.168.40.17',
);

$count = 0;
foreach($ipMapping as $name => $ip) {
    $count++;
    try {
        $client = $factory->createClient(array('host' => $ip));

        $client->connect()
            ->then(
                function ($client) use ($loop, $name, $ip, $count, $ipMapping) {
                    $client->subscribe('/topic/foo.#', function ($frame) use ($name) {
                        echo "[$name] Message received: {$frame->body}\n";
                    });
 
                    $loop->addTimer($count / 2, function () use ($client, $loop, $ip, $name, $ipMapping) {
                        $loop->addPeriodicTimer(count($ipMapping) / 2, function () use ($client, $ip, $name) {
                            $msg = microtime(true);
                            $client->send('/topic/foo.test', $msg);
                            printf("Sent [%s] to %s (%s)\n", $msg, $name, $ip);
                        });
                    });
                },
                function ($reason) {
                    echo "Reject: [$reason]";
                }
            );
    } catch (\Exception $ex) {
        echo $ex->getMessage() . PHP_EOL;
    }
        
}


$loop->run();