<?php

namespace App\Entity\Erp\Acl;

class PermissionStatus
{
    
    /**
     * @var bool
     */
    protected $isGranted = false;
    
    /**
     * @var array
     */
    protected $options;
    
    
    public function __construct(bool $isGranted, array $options = [])
    {
        $this->isGranted = $isGranted;
        $this->options = $options;
    }
    
    public function isGranted(): bool
    {
        return $this->isGranted;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
