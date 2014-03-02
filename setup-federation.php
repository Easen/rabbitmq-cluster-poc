#!/usr/bin/php
<?php

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

$groups = array(
    'first' => array(
        'rabbit1',
        'rabbit2',
        'rabbit3',
        'rabbit4',
    ),
    'second' => array(
        'rabbit5',
        'rabbit6',
        'rabbit7',
        'rabbit8',
    ),
);

$failover = array(
    'rabbit1' => 'rabbit5',
    'rabbit2' => 'rabbit6',
    'rabbit3' => 'rabbit7',
    'rabbit4' => 'rabbit8',
);

$upstreamFailover = array_flip($failover);

function executeCommand($command) {
    echo $command . PHP_EOL;
    `$command`;
}

$currentHost = gethostname();

$federationUpstream = array();

foreach ($groups as $name => $collection) {
    if (in_array($currentHost, $collection)) {
        foreach ($collection as $host) {
            if ($host === $currentHost) {
                continue;
            }
            
            $settings = array(
                'uri' => 'amqp://' . $ipMapping[$host],
                'max-hops' => 1
            );
            
            executeCommand(sprintf(
                'sudo rabbitmqctl set_parameter federation-upstream %s \'%s\'', 
                $host, 
                json_encode($settings)
            ));
            
            $federationUpstream[] = array(
                'upstream' => $host,
                'max-hops' => 1
            );
        }
    }
}
if (array_key_exists($currentHost, $upstreamFailover)) {
    $settings = array(
        'uri' => 'amqp://' . $ipMapping[$upstreamFailover[$currentHost]],
        'max-hops' => 2
    );

    executeCommand(sprintf(
        'sudo rabbitmqctl set_parameter federation-upstream %s \'%s\'', 
        $upstreamFailover[$currentHost], 
        json_encode($settings)
    ));

    $federationUpstream[] = array(
        'upstream' => $upstreamFailover[$currentHost],
        'max-hops' => 2
    );
}

executeCommand(sprintf(
    'sudo rabbitmqctl set_parameter federation-upstream-set test \'%s\'', 
    json_encode($federationUpstream)
));


executeCommand(sprintf(
    'sudo rabbitmqctl set_parameter federation local-nodename \'%s\'', 
    json_encode($currentHost)
));


executeCommand(sprintf(
    'sudo rabbitmqctl set_policy federate-me "^amq\." \'{"federation-upstream-set":"test"}\''
));

