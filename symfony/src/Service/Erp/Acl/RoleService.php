<?php

namespace App\Service\Erp\Acl;

use App\Entity\Erp\Acl\Role;
use App\Entity\Erp\Settings;
use App\Repository\Erp\Acl\RoleRepository;
use App\Repository\Erp\ModuleToTenantRepository;
use App\Repository\Erp\SettingsRepository;


class RoleService
{
    
    /**
     * @var ModuleToTenantRepository
     */
    protected $moduleToTenantRepository;
    
    /**
     * @var SettingsRepository
     */
    protected $settingsRepository;
    
    /**
     * @var RoleRepository
     */
    protected $roleRepository;
    
    
    public function __construct(
        ModuleToTenantRepository $moduleToTenantRepository, 
        SettingsRepository $settingsRepository, 
        RoleRepository $roleRepository
    ) {
        $this->moduleToTenantRepository = $moduleToTenantRepository;
        $this->settingsRepository = $settingsRepository;
        $this->roleRepository = $roleRepository;
    }
        
    public function findDefaultRoles(): array
    {
        $tenantModuleIds = $this->moduleToTenantRepository->findTenantModuleIds();
        $allModuleSettings = $this->settingsRepository->findBy(['module' => $tenantModuleIds]);
        $roleCodes = [];
        
        /** @var Settings $settings */
        foreach ($allModuleSettings as $settings) {
            if ($settings->hasOption(Settings\SettingsOptions::DEFAULT_ROLE_CODE)) {
                $roleCodes[] = $settings->getOption(Settings\SettingsOptions::DEFAULT_ROLE_CODE);
            }
        }
        
        return $this->roleRepository->findBy(['code' => $roleCodes]);
    }
    
    public function findAdminRole(): Role
    {
        return $this->roleRepository->findAdminRole();
    }

}
