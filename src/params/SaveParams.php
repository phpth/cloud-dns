<?php

namespace phpth\dns\params;

/**
 * AliDns attr doc
 * @doc https://next.api.aliyun.com/document/Alidns/2015-01-09/AddDomainRecord
 */
class SaveParams
{
    /**
     * @var string ali dns default value 'en'
     */
    public string $lang = '';

    /**
     * @var string subDomain name, example value: www
     */
    public string $rr;

    /**
     * @var string
     * @doc https://help.aliyun.com/document_detail/29805.html?spm=api-workbench.API%20Document.0.0.4fbd125ecHCHzL
     * @warning  txt验证的功能。取值：ADD_SUB_DOMAIN 、 RETRIEVAL
     */
    public string $type = 'A';

    /**
     * @var string ip value
     */
    public string $value;

    /**
     * @var string 解析线路
     * @doc https://help.aliyun.com/document_detail/29807.html?spm=api-workbench.API%20Document.0.0.4fbd125ecHCHzL
     */
    public string $line = 'default';

    /**
     * @var int 解析生效时间， 个人版至少 600, 企业版至少60， 企业旗舰版至少1
     */
    public int $ttl = 600;

    /**
     * @var int 优先级，1-50 值越低，优先级越高
     */
    public int $priority = 1;

    /**
     * @var string 解析记录备注
     */
    public string $remark = '';

    /**
     * @var bool|null 解析记录状态
     */
    public ?bool $status = null;

}
