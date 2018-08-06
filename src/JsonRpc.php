<?php

namespace Submtd\MoneroPhp;

use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;

abstract class JsonRpc
{
    protected $url;
    protected $status = [
        'code' => null,
        'message' => null,
    ];
    protected $content;
    protected $errors = [];

    public function __construct($url)
    {
        $this->url = $url;
    }

    private function updateStatus($code, $message)
    {
        $this->status['code'] = $code;
        $this->status['message'] = $message;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getStatusCode()
    {
        return $this->status['code'];
    }

    public function getStatusMessage()
    {
        return $this->status['message'];
    }

    public function getContent()
    {
        return $this->content;
    }

    public function updateContent($content)
    {
        $this->content = $content;
    }

    private function addError($code, $message)
    {
        $this->errors[] = [
            'code' => $code,
            'message' => $message,
        ];
    }

    public function hasErrors()
    {
        return (bool) count($this->errors);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getLastError()
    {
        return end($this->getErrors());
    }

    public function getLastErrorCode()
    {
        return isset($this->getLastError()['code']) ? $this->getLastError()['code'] : false;
    }

    public function getLastErrorMessage()
    {
        return isset($this->getLastError()['message']) ? $this->getLastError()['message'] : false;
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
        try {
            $result = $client->sendRequest($request);
            $this->updateStatus($result->getStatusCode(), $result->getReasonPhrase());
            if ($this->getStatusCode() != 200) {
                $this->addError($this->getStatusCode(), $this->getStatusMessage());
                return false;
            }
            $this->updateContent($result->getBody()->getContents());
            return $this->getContent();
        } catch (\Exception $e) {
            $this->updateStatus($e->getCode(), $e->getMessage());
            $this->addError($this->getStatusCode(), $this->getStatusMessage());
            return false;
        }
    }
}
