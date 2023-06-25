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

namespace phpth\dns\provider;

use phpth\dns\params\SaveParams;
use phpth\dns\params\RecordParam;
use phpth\dns\exception\DnsException;

/**
 * @doc https://help.aliyun.com/document_detail/29745.html?spm=a2c4g.39863.0.0.266968ab6Rdk1B 公共参数
 * @doc https://next.api.aliyun.com/document/Alidns/2015-01-09/GetTxtRecordForVerify api 文档
 */
class AliDns extends Provider
{
    /**
     * @var int request ali dns domain timeout second
     */
    public int $timeout = 600;

    /**
     * @var int connect ali dns domain timeout second
     */
    public int $connectTimeout = 90;

    /**
     * @var string user main domain string
     */
    protected string $domain;

    /**
     * @var string
     */
    protected string $accessKeyId;

    /**
     * @var string
     */
    protected string $accessKeySecret;

    /**
     * default ali yun endpoint target domain
     */
    protected string $endpoint = 'alidns.aliyuncs.com';

    /**
     * @var string
     */
    protected string $version = '2015-01-09';

    protected const DEFAULT_ENDPOINT = 'alidns.aliyuncs.com';

    // txt验证的功能。取值：
    public const TXT_ADD_RECORD_TYPE = 'ADD_SUB_DOMAIN';
    public const TXT_RETRIEVAL_TYPE = 'RETRIEVAL';

    /**
     * @param string $domain
     * @param string $accessKeyId
     * @param string $accessKeySecret
     * @param string $endpoint
     * @throws DnsException
     */
    public function __construct(string $domain, string $accessKeyId, string $accessKeySecret, string $endpoint = self::DEFAULT_ENDPOINT)
    {
        if(!$accessKeyId && !$accessKeySecret){
            throw new DnsException('secret info can\'t empty');
        }
        $this->domain = $domain;
        $this->accessKeyId = $accessKeyId;
        $this->accessKeySecret = $accessKeySecret;
        $this->endpoint = $endpoint;
    }

    /**
     * @param int $page
     * @param int $pageSize
     * @param bool|null $enabled
     * @return array
     * @throws DnsException
     */
    public function getRecordList(int $page = 1, int $pageSize = Provider::DEFAULT_PAGE_SIZE, ?bool $enabled = null): array
    {
        $params = [
            'Action'=> 'DescribeDomainRecords',
            'DomainName'=> $this->domain,
            'PageNumber'=> max($page, 1),
            'PageSize'=> $pageSize,
        ];
        if($enabled !== null){
            $params['Status'] = $enabled? 'Enable': 'Disable';
        }
        $res = $this->doRequest($params);
        if(!empty($res['Message'])){
            throw new DnsException("update dns record failed, error: {$res['Message']}".(empty($res['Recommend'])?'': ", error doc: {$res['Recommend']}"));
        }
        $data = [
            'list'=> $res['DomainRecords']['Record'],
            'page'=> $res['PageNumber'],
            'pageSize'=> $res['PageSize']?? 0,
            'total'=> $res['TotalCount']??0,
        ];
        if($data['list']){
            foreach($data['list'] as $k=> $v){
                $attr = $this->getRecordParam($v);
                $data['list'][$k] = $attr;
            }
        }
        return $data;
    }

    /**
     * @param string $recordId
     * @return RecordParam|null
     * @throws DnsException
     */
    public function getRecordDetail(string $recordId): ?RecordParam
    {
        $params = [
            'Action'=> 'DescribeDomainRecordInfo',
            'RecordId' => $recordId
        ];
        $res = $this->doRequest($params);
        if(!$res){
            return null;
        }
        if(!empty($res['Message'])){
            throw new DnsException("update dns record failed, error: {$res['Message']}".(empty($res['Recommend'])?'': ", error doc: {$res['Recommend']}"));
        }
        return $this->getRecordParam($res);
    }

    /**
     * @param string $recordId
     * @param SaveParams $saveObject
     * @return bool
     * @throws DnsException
     */
    public function updateRecord(string $recordId, SaveParams $saveObject): bool
    {
        $params = [
            'Action' => 'UpdateDomainRecord',
            'RecordId'=> $recordId,
            'RR'=> $saveObject->rr,
            'Type'=> $saveObject->type,
            'Value'=> $saveObject->value,
            'TTL'=> $saveObject->ttl,
            'Line'=> $saveObject->line,
        ];
        if($saveObject->lang){
            $params['Lang'] = $saveObject->lang;
        }
        if($saveObject->priority){
            $params['Priority'] = $saveObject->priority;
        }
        if($saveObject->remark){
            $this->updateRecordRemark($recordId, $saveObject->remark);
        }
        $res = $this->doRequest($params);
        if(!empty($res['Message'])){
            throw new DnsException("update dns record failed, error: {$res['Message']}".(empty($res['Recommend'])?'': ", error doc: {$res['Recommend']}"));
        }
        return true;
    }

    /**
     * @param string $recordId
     * @return bool
     * @throws DnsException
     */
    public function delRecord(string $recordId): bool
    {
        $params = [
            'Action' => 'DeleteDomainRecord',
            'RecordId'=> $recordId,
        ];
        $res = $this->doRequest($params);
        if(!empty($res['Message'])){
            throw new DnsException("update dns record failed, error: {$res['Message']}".(empty($res['Recommend'])?'': ", error doc: {$res['Recommend']}"));
        }
        return true;
    }

    /**
     * @param SaveParams $saveObject
     * @return bool
     * @throws DnsException
     */
    public function addRecord(SaveParams $saveObject): bool
    {
        $params = [
            'Action' => 'AddDomainRecord',
            'DomainName' => $this->domain,
            'RR' => $saveObject->rr,
            'Type' => $saveObject->type,
            'Value' => $saveObject->value,
            'Line' => $saveObject->line,
            'TTL' => $saveObject->ttl
        ];
        if($saveObject->lang){
            $params['Lang'] = $saveObject->lang;
        }
        if($saveObject->priority){
            $params['Priority'] = $saveObject->priority;
        }
        $res = $this->doRequest($params);
        if(!empty($res['Message'])){
            throw new DnsException("update dns record failed, error: {$res['Message']}".(empty($res['Recommend'])?'': ", error doc: {$res['Recommend']}"));
        }
        if(empty($res['RecordId'])){
            throw new DnsException('can\'t get added record id');
        }
        if($saveObject->remark){
            $this->updateRecordRemark($res['RecordId'], $saveObject->remark);
        }
        return true;
    }

    /**
     * @param string $recordId
     * @param string $remark
     * @return bool
     * @throws DnsException
     */
    public function updateRecordRemark(string $recordId, string $remark): bool
    {
        $params = [
            'Action' => 'UpdateDomainRecordRemark',
            'RecordId'=> $recordId,
            'Remark' => $remark,
        ];
        $res = $this->doRequest($params);
        if(!empty($res['Message'])){
            throw new DnsException("update dns record failed, error: {$res['Message']}".(empty($res['Recommend'])?'': ", error doc: {$res['Recommend']}"));
        }
        return true;
    }

    /**
     * @param string|null $domain
     * @param string|null $value
     * @param string $type
     * @return array
     * @throws DnsException
     */
    public function addTxtRecord(?string $domain = null, ?string $value = null, string $type = self::TXT_ADD_RECORD_TYPE): array
    {
        if($domain === null){
            if(empty($domain)){
                throw new DnsException("domain can't empty");
            }
            $domain = $this->domain;
        }
        $params = [
            'Action' => 'GetTxtRecordForVerify',
            'Type'=> $type,
            'DomainName'=> $domain,
        ];
        if($value){
            $params['Value'] = $value;
        }
        $res = $this->doRequest($params);
        if(!empty($res['Message'])){
            throw new DnsException("update dns record failed, error: {$res['Message']}".(empty($res['Recommend'])?'': ", error doc: {$res['Recommend']}"));
        }
        return [
            'value'=> $res['Value']??'',
            'domainName'=> $res['DomainName']??'',
            'rr'=> $res['RR']
        ];
    }

    /**
     * @param array $params
     * @return array
     * @throws DnsException
     */
    private function doRequest(array $params): array
    {
        $url = "{$this->endpoint}/";
        $data = [
            'Format' => 'JSON',
            'Version' => $this->version,
            'AccessKeyId' => $this->accessKeyId,
            'SignatureMethod' => 'HMAC-SHA1',
            'Timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
            'SignatureVersion' => '1.0',
            'SignatureNonce' => md5($params['Action'] . uniqid(md5(microtime(true)), true)) . microtime(),
        ];
        $data = array_merge($data, $params);
        $data['Signature'] = $this->sign($data);
        $ret = $this->postRequest($url, http_build_query($data));
        $res = json_decode($ret, true)?:[];
        if(!$res){
            throw new DnsException("request failed: $ret");
        }
        return $res;
    }

    /**
     * @param array $params
     * @param string $method
     * @return string
     */
    private function sign(array $params, string $method = 'POST'): string
    {
        ksort($params);
        $queryStr = '';
        foreach ($params as $key => $value) {
            $queryStr .= '&' . $this->percentEncode($key) . '=' . $this->percentEncode($value);
        }
        $stringToSign = $method . '&%2F&' . $this->percentencode(substr($queryStr, 1));
        return  base64_encode(hash_hmac("sha1", $stringToSign, $this->accessKeySecret . "&", true));
    }

    /**
     * @param $str
     * @return string
     */
    private function percentEncode($str): string
    {
        $res = urlencode($str);
        $res = preg_replace('/\+/', '%20', $res);
        $res = preg_replace('/\*/', '%2A', $res);
        return preg_replace('/%7E/', '~', $res);
    }

    /**
     * @param array $recordInfo
     * @return RecordParam
     */
    public function getRecordParam(array $recordInfo): RecordParam
    {
        $attr = new RecordParam();
        $attr->status = $recordInfo['Status'];
        $attr->type = $recordInfo['Type'];
        $attr->remark = $recordInfo['Remark'] ?? null;
        $attr->ttl = $recordInfo['TTL'];
        $attr->recordId = $recordInfo['RecordId'];
        $attr->priority = $recordInfo['Priority'] ?? null;
        $attr->rr = $recordInfo['RR'];
        $attr->domainName = $recordInfo['DomainName'];
        $attr->weight = $recordInfo['Weight'] ?? null;
        $attr->value = $recordInfo['Value'];
        $attr->line = $recordInfo['Line'];
        $attr->locked = $recordInfo['Locked'] ?? null;
        return $attr;
    }
}

