cat >> /etc/apt/sources.list <<EOT
deb http://www.rabbitmq.com/debian/ testing main
EOT

wget http://www.rabbitmq.com/rabbitmq-signing-key-public.asc
apt-key add rabbitmq-signing-key-public.asc

apt-get update

apt-get install -q -y screen htop vim curl wget php5-cli
apt-get install -q -y rabbitmq-server


# RabbitMQ Plugins
service rabbitmq-server stop

echo 'AnyAlphaNumericStringWillDo' > /var/lib/rabbitmq/.erlang.cookie
echo '[{rabbit, [{loopback_users, []}]}].' > /etc/rabbitmq/rabbitmq.config
pkill beam
pkill epmd

rabbitmq-plugins enable rabbitmq_management
rabbitmq-plugins enable rabbitmq_federation
rabbitmq-plugins enable rabbitmq_federation_management
rabbitmq-plugins enable rabbitmq_stomp
service rabbitmq-server start

rabbitmq-plugins list


