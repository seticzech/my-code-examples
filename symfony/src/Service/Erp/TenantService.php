<?php

namespace App\Service\Erp;

use App\Entity\Erp\LanguageToTenant;
use App\Entity\Erp\Module;
use App\Entity\Erp\ModuleToTenant;
use App\Entity\Erp\Settings;
use App\Entity\Erp\Settings\SettingsOptions;
use App\Entity\Erp\User;
use App\Entity\Sys\Tenant;
use App\Repository\Erp\LanguageRepository;
use App\Repository\Erp\LanguageToTenantRepository;
use App\Repository\Erp\ModuleRepository;
use App\Repository\Erp\ModuleToTenantRepository;
use App\Repository\Erp\SettingsRepository;
use App\Repository\Erp\UserRepository;
use App\Repository\Sys\TenantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Throwable;

class TenantService
{
    
    /**
     * @var TenantRepository
     */
    protected $tenantRepository;

    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var Acl\RoleService
     */
    protected $roleService;

    /**
     * @var LanguageRepository
     */
    protected $languageRepository;

    /**
     * @var LanguageToTenantRepository
     */
    protected $languageToTenantRepository;

    /**
     * @var ModuleRepository
     */
    protected $moduleRepository;

    /**
     * @var ModuleToTenantRepository
     */
    protected $moduleToTenantRepository;

    /**
     * @var SettingsRepository
     */
    protected $settingsRepository;

    /**
     * @var UserPasswordEncoderInterface
     */
    protected $userPasswordEncoder;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;
    
    
    public function __construct(
        TenantRepository $tenantRepository, 
        UserRepository $userRepository, 
        Acl\RoleService $roleService, 
        LanguageRepository $languageRepository, 
        LanguageToTenantRepository $languageToTenantRepository, 
        ModuleRepository $moduleRepository, 
        ModuleToTenantRepository $moduleToTenantRepository, 
        SettingsRepository $settingsRepository, 
        UserPasswordEncoderInterface $userPasswordEncoder,
        EntityManagerInterface $entityManager
    ) {
        $this->tenantRepository = $tenantRepository;
        $this->userRepository = $userRepository;
        $this->roleService = $roleService;
        $this->languageRepository = $languageRepository;
        $this->languageToTenantRepository = $languageToTenantRepository;
        $this->moduleRepository = $moduleRepository;
        $this->moduleToTenantRepository = $moduleToTenantRepository;
        $this->settingsRepository = $settingsRepository;
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->entityManager = $entityManager;
    }

    public function initializeTenant(Tenant $tenant)
    {
        $this->tenantRepository->beginTransaction();

        try {
            $this->tenantRepository->save($tenant);
            
            $this->entityManager->getConnection()->exec("SET app.current_tenant = '{$tenant->getId()}'");
            
            $this->initializeTenantLanguages($tenant);
            $this->initializeTenantModules($tenant);
            
            $this->tenantRepository->commit();
        } catch (Throwable $exc) {
            $this->tenantRepository->rollback();
            throw $exc;
        }
    }
    
    protected function initializeTenantLanguages(Tenant $tenant)
    {
        $languages = $this->languageRepository->findAll();
        
        $sortOrder = 0;
        foreach ($languages as $language) {
            $languageToTenant = new LanguageToTenant;
            $languageToTenant->setTenantId($tenant->getId());
            $languageToTenant->setLanguage($language);
            $languageToTenant->setIsDefault(false);
            $languageToTenant->setIsActive(true);
            $languageToTenant->setSortOrder(++$sortOrder);
            
            $this->languageToTenantRepository->save($languageToTenant);
        }
    }
    
    protected function initializeTenantModules(Tenant $tenant)
    {
        $modules = $this->moduleRepository->findAll();
        
        foreach ($modules as $module) {
            $this->initializeTenantModule($tenant, $module);
        }
    }
    
    protected function initializeTenantModule(Tenant $tenant, Module $module)  
    {
        $moduleToTenant = new ModuleToTenant;
        $moduleToTenant->setTenantId($tenant->getId());
        $moduleToTenant->setModule($module);
        
        $this->moduleToTenantRepository->save($moduleToTenant);
        
        $moduleSettings = new Settings;
        $moduleSettings->setTenantId($tenant->getId());
        $moduleSettings->setModule($module);
        $moduleSettings->setOptions(SettingsOptions::getDefaultOptions($module->getCode()));
        
        $this->settingsRepository->save($moduleSettings);
    }
    
    public function createDefaultUsers(Tenant $tenant, string $domain): array
    {
        $users = [
            $this->createDefaultUser($tenant, $domain, 'admin'),
            $this->createDefaultUser($tenant, $domain, 'user')
        ];
        
        foreach ($users as $user) {
            $this->userRepository->save($user);
        }
        
        return $users;
    }
    
    protected function createDefaultUser(Tenant $tenant, string $domain, string $type = 'user'): User
    {
        $defaultRoles = $this->roleService->findDefaultRoles();
        
        $user = new User();
        $user->setEmail("{$type}@{$domain}");
        $user->setFirstName('Default');
        $user->setLastName(ucfirst($type));
        $user->setPassword($this->userPasswordEncoder->encodePassword($user, 'secret'));
        $user->setTenantId($tenant->getId());
        
        foreach ($defaultRoles as $defaultRole) {
            $user->addUserRole($defaultRole);
        }
        
        if ($type === 'admin') {
            $user->addUserRole($this->roleService->findAdminRole());
        }
        
        return $user;
    }
    
    public function getModules(): array
    {
        return array_map(function(ModuleToTenant $moduleToTenant) {
            return $moduleToTenant->getModule();
        }, $this->moduleToTenantRepository->findAll());
    }
}
