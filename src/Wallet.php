<?php

namespace Submtd\MoneroPhp;

class Wallet extends JsonRpc
{
    public function getBalance($account_index = 0)
    {
        return $this->request('getbalance', ['account_index' => $account_index]);
    }
}
