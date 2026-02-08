<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class PublishRegisteredUser
{
    public function __construct() {}

    public function handle(UserRegistered $event): void
    {
        $config = config('queue.connections.rabbitmq.hosts')[0];
        $connection = new AMQPStreamConnection(
            $config['host'],
            $config['port'],
            $config['user'],
            env('RABBITMQ_PASSWORD'),
            $config['vhost']
        );

        $channel = $connection->channel();

        $channel->queue_declare(
            'user-events',
            false,
            true,
            false,
            false
        );
        $data = json_encode([
            'name' => $event->user->name,
            'email' => $event->user->email,
            'user_id' => $event->user->id
        ]);

        $msg = new AMQPMessage($data);

        $channel->basic_publish($msg, '', 'user-events');

        $channel->close();
        $connection->close();
    }
}
