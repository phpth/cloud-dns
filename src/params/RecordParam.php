<?php

namespace phpth\dns\params;

/**
 * dns list detail
 */
class RecordParam
{
    /**
     * @var string 当前的解析记录状态。Enable
     */
    public string $status;

    /**
     * @var string 记录类型。MX
     */
    public string $type;

    /**
     * @var string|null 备注。
     */
    public ?string $remark;

    /**
     * @var int 缓存时间设置。单位：秒。600
     */
    public int $ttl;

    /**
     * @var string 解析记录ID。
     */
    public string $recordId;

    /**
     * @var int|null mx记录的优先级。1
     */
    public ?int $priority;

    /**
     * @var string 主机记录。 www
     */
    public string $rr;

    /**
     * @var string 域名名称。example.com
     */
    public string $domainName;

    /**
     * @var int|null 负载均衡权重。2
     */
    public ?int $weight;

    /**
     * @var string 记录值。124.71.0.217
     */
    public string $value;

    /**
     * @var string 解析线路。 default
     */
    public string $line;

    /**
     * @var bool|null 当前解析记录锁定状态。 false
     */
    public ?bool $locked;
}
