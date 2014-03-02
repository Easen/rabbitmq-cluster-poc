<?php

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

foreach($ipMapping as $name => $ip) {
    try {
        $client = $factory->createClient(array('host' => $ip));

        $client->connect()
            ->then(
                function ($client) use ($loop, $name) {
                    $client->subscribe('/topic/foo.#', function ($frame) use ($name) {
                        echo "[$name] Message received: {$frame->body}\n";
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


$client = $factory->createClient(array('host' => $argv[1]));
$client
    ->connect()
    ->then(function ($client) use ($loop) {
        $loop->addPeriodicTimer(3, function () use ($client) {
            
            $client->send('/topic/foo.test', 'le message');
            echo "Sent Le Message\n";
        });
    });


$loop->run();