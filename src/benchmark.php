<?php

error_reporting(-1);

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

$server = !!$argv[1];

if ($server) {
    foreach($ipMapping as $name => $ip) {
        try {
            $factory->createClient(array('host' => $ip))->connect()
                ->then(
                    function ($client) use ($loop, $name) {
                        $count = 0;
                        $startTime = 0;
                        $client->subscribe('/topic/foo.test', function ($frame) use ($name, $client, &$count, &$startTime) {
                            if ($frame->body == 'fin') {
                                $client->disconnect();
                                $duration = microtime(true) - $startTime;
                                echo "[$name] Message Count {$count} Took {$duration}\n";
                            }
                            if ($count == 0) {
                                $startTime = microtime(true);
                            }
                            $count++;
                            //echo "[$name] Message received: {$frame->body}\n";
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
} else {

    $client = $factory->createClient(array('host' => '192.168.40.10'));
    $client
        ->connect()
        ->then(function ($client) use ($loop, $client) {

            $starttime = microtime(true);
            $total = 100;

            for ($i = 0; $i <= $total - 1; $i++) {
                $client->send('/topic/foo.test', $i);
            }
            $client->send('/topic/foo.test', 'fin');

            $client->disconnect();
            $duration = microtime(true) - $starttime;
            echo "Sent messages, took $duration\n";
        });
}

$loop->run();
