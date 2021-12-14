<?php

namespace App\Service\Erp;

use App\Entity\Erp\Module;
use App\Entity\Erp\ResetPasswordToken;
use App\Entity\Erp\Settings;
use App\Entity\Erp\User;
use App\Exception\AuthenticationException;
use App\Exception\Service\EntityNotFoundException;
use App\Exception\Service\Password\ResetPasswordTokenExpiredException;
use App\Repository\Erp\ResetPasswordTokenRepository;
use App\Repository\Erp\SettingsRepository;
use App\Repository\Erp\UserRepository;
use DateInterval;
use DateTime;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PasswordService 
{
    
    const RESET_TOKEN_VALIDITY_INTERVAL = 'PT1H';
        
    /**
     * @var UserRepository
     */
    protected $userRepository;
        
    /**
     * @var ResetPasswordTokenRepository
     */
    protected $resetPasswordTokenRepository;
        
    /**
     * @var SettingsRepository
     */
    protected $settingsRepository;
        
    /**
     * @var MailerInterface
     */
    protected $mailer;
        
    /**
     * @var UserPasswordEncoderInterface
     */
    protected $passwordEncoder;
    
    
    public function __construct(
        ResetPasswordTokenRepository $resetPasswordTokenRepository,
        UserRepository $userRepository,
        SettingsRepository $settingsRepository,
        UserPasswordEncoderInterface $encoder,
        MailerInterface $mailer
    )
    {
        $this->resetPasswordTokenRepository = $resetPasswordTokenRepository;
        $this->userRepository = $userRepository;
        $this->settingsRepository = $settingsRepository;
        $this->passwordEncoder = $encoder;
        $this->mailer = $mailer;
    }
    
    public function resetPassword(User $user, string $redirectToUrl): ResetPasswordToken
    {
        $passwordResetToken = $this->createResetPasswordToken($user);
        
        $this->sendResetPasswordEmail($user, $redirectToUrl, $passwordResetToken);
        
        return $passwordResetToken;
    }
    
    public function createResetPasswordToken(User $user): ResetPasswordToken
    {
        $passwordResetToken = new ResetPasswordToken();
        $passwordResetToken->setToken($this->createTokenString($user));
        $passwordResetToken->setValidUntil($this->createValidUntil());
        $passwordResetToken->setUser($user);
        $passwordResetToken->setTenantId($user->getTenantId());
        
        $this->resetPasswordTokenRepository->save($passwordResetToken);
        
        return $passwordResetToken;
    }
    
    protected function createTokenString(User $user): string
    {
        return hash("sha256", $user->getId().time());
    }
    
    protected function createValidUntil(): DateTime
    {
        $validUntil = new DateTime;
        $validUntil->add(new DateInterval(self::RESET_TOKEN_VALIDITY_INTERVAL));
        
        return $validUntil;
    }
    
    protected function sendResetPasswordEmail(
        User $user, 
        string $redirectToUrl, 
        ResetPasswordToken $passwordToken
    ) {
        $settings = $this->getCoreSettings();
        
        $senderMail = $settings->getOption(Settings\SettingsOptions::CORE_TENANT_DEFAULT_SENDER_EMAIL);
        $tenantName = $settings->getOption(Settings\SettingsOptions::CORE_TENANT_NAME);
        
        $email = new Email;
        $email->from(new Address($senderMail, $tenantName ?: ''));
        $email->to(new Address($user->getEmail(), $user->getFullName()));
        $email->subject("Požadavek na reset hesla");
        $email->text("Dobrý den,\n"
            . "přijali jsme Váš požadavek na reset hesla. Pokračujte kliknutím na odkaz "
            . "$redirectToUrl?token={$passwordToken->getToken()} a nastavením nového hesla.");
        
        $this->mailer->send($email);
    }
        
    protected function getCoreSettings(): Settings
    {
        $settings = $this->settingsRepository->findSettingsByModuleCode(Module::MODULE_CORE_CODE);
        
        if (!$settings || !$settings->getOption(Settings\SettingsOptions::CORE_TENANT_DEFAULT_SENDER_EMAIL)) {
            throw new EntityNotFoundException("Settings for core module not found. Sender email required.");
        }
        
        return $settings;
    }
    
    public function findPasswordToken(string $token): ResetPasswordToken
    {
        /** @var ResetPasswordToken $passwordToken */
        $passwordToken = $this->resetPasswordTokenRepository->findOneBy(['token' => $token]);
        
        if (!$passwordToken) {
            throw new EntityNotFoundException("Token '$token' not found.");
        }
        
        if ($passwordToken->getValidUntil() < new DateTime()) {
            throw new ResetPasswordTokenExpiredException(
                "Token expired on " . $passwordToken->getValidUntil()->format('Y-m-d H:i:s')
            );
        }
        
        return $passwordToken;
    }
    
    public function setNewPassword(string $token, string $newPassword): User
    {
        $resetPasswordToken = $this->findPasswordToken($token);
            
        $user = $resetPasswordToken->getUser();
        $user->setPassword($this->passwordEncoder->encodePassword($user, $newPassword));

        $this->userRepository->save($user);
        
        $this
            ->resetPasswordTokenRepository
            ->remove($resetPasswordToken)
            ->saveAll();

        return $user;
    }

    public function changePassword(User $user, string $currentPassword, string $newPassword): User
    {
        if (!$this->passwordEncoder->isPasswordValid($user, $currentPassword)) {
            throw new AuthenticationException('Current passwords does not match.');
        }

        $user->setPassword($this->passwordEncoder->encodePassword($user, $newPassword));

        $this->userRepository->save($user);

        return $user;
    }

}
