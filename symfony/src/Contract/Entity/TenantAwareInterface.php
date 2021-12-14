<?php

namespace App\Contract\Entity;

use Ramsey\Uuid\UuidInterface;

interface TenantAwareInterface
{
   
    /**
     * @return string|null
     */
    public function getTenantId(): ?string;

    /**
     * @param UuidInterface|string $value
     *
     * @return $this
     */
    public function setTenantId($value);

}
