<?php

namespace Submtd\MoneroPhp;

use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;

abstract class JsonRpc
{
    protected $url;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function request($method, array $parameters = [])
    {
        $json = [
            'jsonrpc' => '2.0',
            'id' => 0,
            'method' => $method,
            'params' => $parameters,
        ];
        $messageFactory = MessageFactoryDiscovery::find();
        $request = $messageFactory->createRequest('POST', $this->url, ['Content-Type' => 'application/json'], json_encode($json));
        $client = HttpClientDiscovery::find();
        $result = $client->sendRequest($request);
        return $result->getBody()->getContents();
    }
}
