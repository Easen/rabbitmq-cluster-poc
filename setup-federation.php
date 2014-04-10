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

$upstreamFailover = array(
    'rabbit1' => array('rabbit5', 'rabbit6', 'rabbit7', 'rabbit8'),
    'rabbit2' => array('rabbit5', 'rabbit6', 'rabbit7', 'rabbit8'),
    'rabbit3' => array('rabbit5', 'rabbit6', 'rabbit7', 'rabbit8'),
    'rabbit4' => array('rabbit5', 'rabbit6', 'rabbit7', 'rabbit8'),
    
    'rabbit5' => array('rabbit1', 'rabbit2', 'rabbit3', 'rabbit4'),
    'rabbit6' => array('rabbit1', 'rabbit2', 'rabbit3', 'rabbit4'),
    'rabbit7' => array('rabbit1', 'rabbit2', 'rabbit3', 'rabbit4'),
    'rabbit8' => array('rabbit1', 'rabbit2', 'rabbit3', 'rabbit4'),
);

function executeCommand($command) {
    echo $command . PHP_EOL;
    `$command`;
}

$currentHost = gethostname();
$federationUpstream = array();

foreach ($ipMapping as $host => $ip) {
    executeCommand(sprintf("sudo echo '$ip\t$host' >> /etc/hosts"));
}

executeCommand(sprintf(
    'sudo rabbitmqctl set_cluster_name %s', 
    json_encode($currentHost)
));

foreach ($groups as $name => $collection) {
    if (in_array($currentHost, $collection) && $currentHost !== $collection[0]) {
        
        executeCommand('sudo rabbitmqctl stop_app');
        
        executeCommand(sprintf(
            'sudo rabbitmqctl join_cluster rabbit@%s', 
            $collection[0]
        ));
        
        executeCommand('sudo rabbitmqctl start_app');
    }
}
if (array_key_exists($currentHost, $upstreamFailover)) {
    if (!is_array($upstreamFailover[$currentHost])) {
        $upstreamFailover[$currentHost] = array($upstreamFailover[$currentHost]);
    } 
    
    
    
    $uris = array();
    foreach ($upstreamFailover[$currentHost] as $uriHost) {
        $uris[] = 'amqp://' . $ipMapping[$uriHost];
    }
    $settings = array(
        'uri' => $uris,
        'max-hops' => 1
    );

    executeCommand(sprintf(
        'sudo rabbitmqctl set_parameter federation-upstream upstream \'%s\'', 
        json_encode($settings)
    ));

    $federationUpstream[] = array(
        'upstream' => $upstreamFailover[$currentHost],
        'max-hops' => 1
    );
}

executeCommand(sprintf(
    'sudo rabbitmqctl set_policy --apply-to exchanges federate-me "^amq\." \'{"federation-upstream-set":"all"}\''
));

