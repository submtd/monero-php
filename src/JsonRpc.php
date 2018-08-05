<?php

namespace Submtd\MoneroPhp;

use Http\Client\Common\Plugin\AuthenticationPlugin;
use Http\Client\Common\PluginClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Client\Common\Plugin\HeaderSetPlugin;
use Http\Message\Authentication\Wsse;

abstract class JsonRpc
{
    protected $url;
    protected $username;
    protected $password;

    public $plugins = [
        'Authentication',
        'Header',
    ];

    public function __construct($url, $username = null, $password = null)
    {
        $this->url = $url;
        $this->username = $username;
        $this->password = $password;
    }

    private function getPlugins()
    {
        $plugins = [];
        foreach ($this->plugins as $plugin) {
            if (!method_exists($this, 'get' . $plugin . 'Plugin')) {
                continue;
            }
            $plugins[] = call_user_func([$this, 'get' . $plugin . 'Plugin']);
        }
        return $plugins;
    }

    private function getAuthenticationPlugin()
    {
        return new AuthenticationPlugin(new Wsse($this->username, $this->password));
    }

    private function getHeaderPlugin()
    {
        return new HeaderSetPlugin([
            'Content-Type' => 'application/json',
        ]);
    }

    private function getClient()
    {
        return new PluginClient(HttpClientDiscovery::find(), $this->getPlugins());
    }

    private function getRequest($method, $parameters)
    {
        $json = [
            'jsonrpc' => '2.0',
            'id' => 0,
            'method' => $method,
            'params' => $parameters,
        ];
        $messageFactory = MessageFactoryDiscovery::find();
        return $messageFactory->createRequest('POST', $this->url, [], json_encode($json));
    }

    public function request($method, array $parameters = [])
    {
        return $this->getClient()->sendRequest($this->getRequest($method, $parameters));
    }
}
