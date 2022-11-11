<?php

namespace Reverb\Servers\ApiGateway;

use Reverb\Contracts\ConnectionManager;
use Reverb\Server as ReverbServer;

class Server
{
    public function __construct(protected ReverbServer $server, protected ConnectionManager $manager)
    {
    }

    /**
     * Handle the incoming API Gateway request.
     *
     * @param  \Reverb\Servers\ApiGateway\Request  $request
     * @return void
     */
    public function handle(Request $request)
    {
        match ($request->event()) {
            'CONNECT' => $this->server->open(
                $this->connection($request->connectionId())
            ),
            'DISCONNECT' => $this->server->close(
                $this->connection($request->connectionId())
            ),
            'MESSAGE' => $this->server->message(
                $this->connection($request->connectionId()),
                $request->message()
            )
        };
    }

    /**
     * Get a Reverb connection from the API Gateway request.
     *
     * @param  string  $connectionId
     * @return \Reverb\Contracts\Connection
     */
    protected function connection(string $connectionId): Connection
    {
        if (! $managedConnection = $this->manager->get($connectionId)) {
            $managedConnection = $this->manager->connect(
                new Connection($connectionId)
            );
        }

        return $managedConnection;
    }
}