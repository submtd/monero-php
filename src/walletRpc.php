<?php

namespace Submtd\MoneroPhp;

class WalletRpc extends JsonRpc
{
    public function getBalance($account_index)
    {
        return $this->request('getbalance', ['account_index' => $account_index]);
    }
}
