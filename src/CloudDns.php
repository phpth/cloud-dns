<?php
// +----------------------------------------------------------------------
// | cloud-dns
// +----------------------------------------------------------------------
// | Copyright (c) 2023
// +----------------------------------------------------------------------
// | Licensed MIT
// +----------------------------------------------------------------------
// | Author: js
// +----------------------------------------------------------------------
// | Date: 2023-05-02
// +----------------------------------------------------------------------
// | Time: 下午 03:22
// +----------------------------------------------------------------------

namespace phpth\dns;

use phpth\dns\params\SaveParams;
use phpth\dns\provider\AliDns;
use phpth\dns\provider\Provider;
use phpth\dns\exception\DnsException;

class CloudDns
{
    /**
     * ali yun dns
     */
    public const TYPE_ALI_CLOUD_DNS = 'ali';

    /**
     * support method, it will add more support feature
     */
    protected const CLOUD_DNS_PROVIDER = [
        'ali'=> AliDns::class
    ];

    /**
     * @param string $domain
     * @param string $keyId
     * @param string $keySecret
     * @param string $type
     * @return AliDns
     * @throws DnsException
     */
    public static function getDnsOperatorInstance(string $domain, string $keyId, string $keySecret, string $type)
    {
        if(empty(self::CLOUD_DNS_PROVIDER[$type])){
            throw new DnsException("not yet support dns operator type: $type");
        }
        if(!preg_match('/[\w-]+\.[\w-]+/', $domain)){
            throw new DnsException('please provide right of domain');
        }
        /**
         * @see AliDns
         */
        $class = self::CLOUD_DNS_PROVIDER[$type];
        return new $class($domain, $keyId, $keySecret);
    }

    /**
     * @return SaveParams
     */
    public static function getSaveParams(): SaveParams
    {
        return new SaveParams();
    }
}
