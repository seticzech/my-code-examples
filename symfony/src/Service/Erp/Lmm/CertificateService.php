<?php

namespace App\Service\Erp\Lmm;

use App\Contract\Entity\Lmm\CertificateTemplateInterface;
use App\Entity\Erp\App;
use App\Entity\Erp\Lmm\CourseCertificate\DefaultTemplate;
use App\Entity\Erp\Lmm\CourseToUser;
use App\Entity\Erp\Mlm\File;
use App\Entity\Erp\Module;
use App\Entity\Erp\Settings;
use App\Exception\Service\EntityNotFoundException;
use App\Repository\Erp\AppRepository;
use App\Repository\Erp\Lmm\CourseCertificate\CustomTemplateRepository;
use App\Repository\Erp\Lmm\CourseCertificate\DefaultTemplateRepository;
use App\Repository\Erp\SettingsRepository;
use App\Service\Erp\BrandingService;
use App\Service\FileService;
use Nucleos\DompdfBundle\Factory\DompdfFactoryInterface;
use Twig\Environment;

class CertificateService
{
    
    /**
     * @var DompdfFactoryInterface
     */
    protected $dompdfFactory;
    
    /**
     * @var SettingsRepository
     */
    protected $settingsRepository;
    
    /**
     * @var AppRepository
     */
    protected $appRepository;
    
    /**
     * @var FileService
     */
    protected $fileService;
    
    /**
     * @var DefaultTemplateRepository
     */
    protected $defaultTemplateRepository;
    
    /**
     * @var CustomTemplateRepository
     */
    protected $customTemplateRepository;
    
    /**
     * @var BrandingService
     */
    protected $brandingService;
    
    /**
     * @var Environment
     */
    protected $twig;
    
    
    public function __construct(
        DompdfFactoryInterface $dompdfFactory,
        SettingsRepository $settingsRepository,
        AppRepository $appRepository,
        FileService $fileService,
        DefaultTemplateRepository $defaultTemplateRepository,
        CustomTemplateRepository $customTemplateRepository,
        BrandingService $brandingService,
        Environment $twig
    )
    {
        $this->settingsRepository = $settingsRepository;
        $this->appRepository = $appRepository;
        $this->fileService = $fileService;
        $this->dompdfFactory = $dompdfFactory;
        $this->defaultTemplateRepository = $defaultTemplateRepository;
        $this->customTemplateRepository = $customTemplateRepository;
        $this->brandingService = $brandingService;
        $this->twig = $twig;
    }
    
    public function generateCertificate(CourseToUser $courseToUser)
    {
        $settings = $this->getLmmSettings();
        
        $certificateTemplate = $this->getCertificateTemplate($settings);
        
        $template = $this->twig->createTemplate($certificateTemplate->getTemplate());
        
        $appLogo = $this->getAppSettingsLogo();
        
        $html = $template->render([
            'certificate_id' => $courseToUser->getId(),
            'tenant_email' => $settings->getOption(Settings\SettingsOptions::LMM_CERTIFICATE_EMAIL),
            'tenant_name' => $settings->getOption(Settings\SettingsOptions::LMM_CERTIFICATE_GRANTER),
            'academy_name' => $settings->getOption(Settings\SettingsOptions::LMM_ACADEMY_NAME),
            'course_title' => $courseToUser->getCourse()->getTitle(),
            'student_name' => $courseToUser->getUser()->getFullName(),
            'certified_at' => $courseToUser->getFinishedAt()->format('j. n. Y'),
            'logo_url' => $this->brandingService->getLogoAbsolutePath($courseToUser->getUser()->getTenantId()),
            'logo_base64' => $appLogo ? $this->fileService->getFileBase64($appLogo) : null,
            'logo_base64_mimetype' => $appLogo ? $appLogo->getMimeType() : null
        ]);
        
        return $this->generatePdf($html);
    }
    
    protected function getLmmSettings(): Settings
    {
        $settings = $this->settingsRepository->findSettingsByModuleCode(Module::MODULE_LMM_CODE);
        
        if (!$settings) {
            throw new EntityNotFoundException('Settings for lmm module not found.');
        }
        
        return $settings;
    }
    
    protected function getCertificateTemplate(Settings $settings): CertificateTemplateInterface
    {
        $template = null;
        if ($settings->getOption(Settings\SettingsOptions::LMM_DEFAULT_CERTIFICATE)) {
            $template = $this->getCertificateTemplateById(
                $settings->getOption(Settings\SettingsOptions::LMM_DEFAULT_CERTIFICATE)
            );
        } 
        
        if (!$template) {
            $template = $this->getDefaultTemplate();
        }
        
        if (!$template) {
            throw new EntityNotFoundException('No valid certificate template found.');
        }
        
        return $template;
    }
    
    protected function getCertificateTemplateById(string $id): ?CertificateTemplateInterface
    {
        $template = $this->customTemplateRepository->find($id);

        if (!$template) {
            $template = $this->defaultTemplateRepository->find($id);
        }
        
        return $template;
    }
    
    protected function getDefaultTemplate(): ?DefaultTemplate
    {
        return $this->defaultTemplateRepository->findOneBy(['isDefault' => 1]);
    }
    
    protected function getAppSettingsLogo(): ?File
    {
        $logo = null;
        
        /** @var App $app */
        $app = $this->appRepository->findOneBy(['code' => 'blackbelt']);
        $logoImageId = $app->getSettings()->getOptionLogoImageId();
        if ($logoImageId) {
            $logo = $this->fileService->findFileById($logoImageId);
        }
        
        return $logo;
    }
    
    protected function generatePdf(string $html): string
    {
        $dompdf = $this->dompdfFactory->create();
        $dompdf->loadHtml($html, 'UTF-8');
        
        $dompdf->setPaper('A4', 'landscape');
        
        $dompdf->render();
        
        return $dompdf->output(['compress' => 0]);
    }

}
