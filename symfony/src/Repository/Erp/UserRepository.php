<?php

namespace App\Repository\Erp;

use App\Entity\Erp\User;
use App\Contract\OAuth2\UserRepositoryInterface as OAuth2UserRepositoryInterface;
use App\Exception\InvalidArgumentException;
use App\Exception\NotFoundException;
use App\Repository\RepositoryAbstract;
use Doctrine\Persistence\ManagerRegistry;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method findOneByEmail(string $email): ?User
 */
class UserRepository extends RepositoryAbstract
    implements PasswordUpgraderInterface, OAuth2UserRepositoryInterface, UserRepositoryInterface
{

    /**
     * @var string
     */
    protected static $entityClass = User::class;

    /**
     * @var UserPasswordEncoderInterface
     */
    protected $encoder;


    public function __construct(ManagerRegistry $registry, UserPasswordEncoderInterface $encoder)
    {
        parent::__construct($registry);

        $this->encoder = $encoder;
    }

    public function createUser(): ?User
    {
        return new User();
    }

    public function findAllFiltered(array $filters = [])
    {
        $criteria = [];

        foreach ($filters as $key => $val) {
            switch ($key) {
                case 'approved':
                    $criteria['isApproved'] = !!intval($val);
                    break;
                case 'client':
                    $criteria['isClient'] = !!$val;
                    break;
                case 'company':
                    $criteria['isCompany'] = !!$val;
                    break;
                case 'rejected':
                    $criteria['isRejected'] = !!intval($val);
                    break;
            }
        }

        return empty($criteria)
            ? $this->findAll()
            : $this->findBy($criteria);
    }

    public function findOneByEmailAndTenantId(string $email, string $tenantId): ?User
    {
        $qbl = $this->createQueryBuilder('u');

        $qbl->where('u.email = :email')
            ->andWhere('u.tenantId = :tenantId')
            ->setParameter('email', $email)
            ->setParameter('tenantId', $tenantId);

        $result = $qbl->getQuery()->execute();

        return !empty($result) ? $result[0] : null;
    }

    public function generateAuthCode(User $user, int $expiresIn = 3600): self
    {
        $code = md5(microtime(true));
        $expiresAt = time() + $expiresIn;

        $user->setAuthCode($code . '|' . $expiresAt);

        return $this;
    }

    /**
     * OAuth2
     *
     * @param string $username
     * @param string $password
     * @param string $grantType
     * @param ClientEntityInterface $clientEntity
     *
     * @return UserEntityInterface|User|object|null
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function getUserEntityByUserCredentials($username, $password, $grantType, ClientEntityInterface $clientEntity)
    {
        /** @var User $user */
        $user = $this->findOneBy(['email' => $username]);

        if (!$user) {
            throw new NotFoundException('User not found.');
        }

        // authentication against generated one time auth code without need to know password
        if (strpos($password, 'auth|') === 0) {
            $code = substr($password, 5);
            list($plainCode, $validUntil) = explode('|', $code);

            if ($validUntil && ($validUntil < time())) {
                $user->clearAuthCode();
                $this->save($user);

                throw new NotFoundException('Authorization code expired.');
            }

            if ($code !== $user->getAuthCode()) {
                throw new NotFoundException('User not found.');
            }

            $user->clearAuthCode();
            $this->save($user);

            return $user;
        }

        // Due to security reasons we return NOT FOUND even for bad password
        if (!$this->validatePassword($user, $password)) {
            throw new NotFoundException('User not found.');
        }

        return $user;
    }

    public function setUserPassword(User $user, string $plainPassword): self
    {
        $user->setPassword(
            $this->encoder->encodePassword($user, $plainPassword)
        );

        return $this;
    }

    /**
     * Symfony security
     *
     * @param $identifier
     *
     * @throws UsernameNotFoundException
     *
     * @return UserInterface|User|object|null
     */
    public function loadUserByUsername($identifier)
    {
        $user = $this->findOneByEmail($identifier);

        if (!$user) {
            throw new UsernameNotFoundException('User not found.');
        }

        return $user;
    }

    /**
     * Symfony security
     *
     * @param UserInterface $user
     */
    public function refreshUser(UserInterface $user)
    {

    }

    /**
     * @param UserInterface $user
     * @param string $newEncodedPassword
     *
     * @throws InvalidArgumentException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->save($user);
    }

    public function supportsClass(string $class)
    {

    }

    public function validatePassword(User $user, string $password): bool
    {
        return $this->encoder->isPasswordValid($user, $password);
    }

}
