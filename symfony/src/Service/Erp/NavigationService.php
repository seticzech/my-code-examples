<?php

namespace App\Service\Erp;

use App\Repository\Erp\NavigationRepository;


class NavigationService
{
    
    /**
     * @var NavigationRepository
     */
    protected $navigationRepository;
    
    /**
     * @var TenantService
     */
    protected $tenantService;
    
    
    public function __construct(NavigationRepository $navigationRepository, TenantService $tenantService)
    {
        $this->navigationRepository = $navigationRepository;
        $this->tenantService = $tenantService;
    }
    
    public function getAdminNavigation(): array
    {
        $modules = $this->tenantService->getModules();
        
        return $this->navigationRepository->findBy(['module' => $modules, 'parent' => null], ['sortOrder' => 'asc']);
    }

}
