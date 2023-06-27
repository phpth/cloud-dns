<?php

namespace phpth\dns\provider;

use phpth\dns\params\SaveParams;
use phpth\dns\params\RecordParam;
use phpth\dns\exception\DnsException;

abstract class Provider
{
    public const DEFAULT_PAGE_SIZE = 500;

    abstract public function getRecordList(int $page, int $pageSize = Provider::DEFAULT_PAGE_SIZE, ?bool $enabled = null): array;

    abstract public function getRecordDetail(string $recordId): ?RecordParam;

    abstract public function updateRecord(string $recordId, SaveParams $saveObject): bool;

    abstract public function delRecord(string $recordId): bool;

    abstract public function addRecord(SaveParams $saveObject): bool;

    /**
     * @param string $url
     * @param $data
     * @param array $header
     * @param array $options
     * @param int $connectTimeout
     * @param int $timeout
     * @return string
     * @throws DnsException
     */
    protected function postRequest(string $url, $data, array $header = [], array $options = [], int $connectTimeout = 90, int $timeout = 900): string
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if(empty($options[CURLOPT_TIMEOUT]) ){
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        }
        if(empty($options[CURLOPT_CONNECTTIMEOUT])){
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connectTimeout);
        }
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        if($header){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        if($options){
            foreach($options as $k=> $v){
                curl_setopt($ch, $k, $v);
            }
        }
        $ret = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if($erNo = curl_errno($ch)){
            throw new DnsException("request $url failed, error no: $erNo, error info: ".curl_error($ch)."http code: ".$httpCode."\r\nresponse content: $ret");
        }
        return $ret;
    }
}
