<?php

namespace App\Service\Erp;

class BrandingService
{
    
    /**
     * @var string
     */
    protected $projectDirAbsolutePath;
    
    /**
     * @var string
     */
    protected $defaultLogoRelativePath;
    
    
    public function __construct(string $projectDirAbsolutePath, string $defaultLogoRelativePath)
    {
        $this->projectDirAbsolutePath = $projectDirAbsolutePath;
        $this->defaultLogoRelativePath = $defaultLogoRelativePath;
    }
    
    public function getLogoAbsolutePath(string $tenantId): string
    {
        $logo = $this->getTenantLogoAbsolutePath($tenantId);
        
        if (!$logo) {
            $logo = $this->projectDirAbsolutePath.'/'.$this->defaultLogoRelativePath;
        }
        
        return $logo;
    }
    
    protected function getTenantLogoAbsolutePath(string $tenantId): ?string
    {
        $extensions = ['jpg', 'png', 'svg'];
        $tenantLogoDirectory = $this->projectDirAbsolutePath.'/var/uploads/'.$tenantId.'/branding';
        
        foreach ($extensions as $extension) {
            $logoAbsolutePath = $tenantLogoDirectory.'/logo.'.$extension;
            
            if (file_exists($logoAbsolutePath)) {
                return $logoAbsolutePath;
            }
        }
        
        return null;
    }

}
