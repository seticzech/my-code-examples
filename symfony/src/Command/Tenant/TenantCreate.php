<?php

namespace App\Command\Tenant;

use App\Command\CommandAbstract;
use App\Entity\Erp\User;
use App\Entity\Sys\Tenant;
use App\Entity\Sys\TenantHost;
use App\Exception\InvalidArgumentException;
use App\Service\Erp\TenantService;
use App\Service\Helper\ContainerParametersHelper;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TenantCreate extends CommandAbstract
{

    /**
     * @var string
     */
    protected static $defaultName = 'app:tenant:create';

    /**
     * @var TenantService
     */
    protected $tenantService;


    public function __construct(
        ContainerParametersHelper $helper,
        TenantService $tenantService
    ) {
        parent::__construct($helper);

        $this->tenantService = $tenantService;
    }

    protected function configure()
    {
        $this
            ->setDescription('Creates a new tenant')
            ->addArgument('name',InputArgument::REQUIRED, 'Tenant name')
            ->addArgument('code',InputArgument::REQUIRED, 'Tenant unique code')
            ->addOption('host', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Set tenant host URL; if not specified, tenant code and APP_DOMAIN from .env will be used', null)
            ->addOption('active', 'a', InputOption::VALUE_OPTIONAL, 'Set tenant active', false)
            ->addOption('id', null, InputOption::VALUE_OPTIONAL, 'UUID id for tenant; will be generated if not specified', null)
            ->addOption('domain', 'd', InputOption::VALUE_OPTIONAL, 'Domain name for emails of created users; if not specified, APP_DOMAIN from .env will be used', null)
            ->addOption('nousers', 'u', InputOption::VALUE_OPTIONAL, 'Do not create basic users', false);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        try {
            // arguments
            $code = $input->getArgument('code');
            $name = $input->getArgument('name');
            // options
            $active = $input->getOption('active');
            $domain = $input->getOption('domain');
            $hosts = $input->getOption('host');
            $id = $input->getOption('id');
            $noUsers = $input->getOption('nousers');

            if (!$domain) {
                if (!array_key_exists('APP_DOMAIN', $_ENV)) {
                    throw new InvalidArgumentException('Unknown app domain, users cannot be created. Please check APP_DOMAIN parameter in .env or use --domain argument.');
                }

                $domain = $_ENV['APP_DOMAIN'];
            }

            if (!$hosts) {
                $hosts = ["{$code}.{$domain}"];
            }

            $users = [];

            $tenant = $this->createTenant($code, $name, $hosts,$active !== false, $id);
            
            $this->tenantService->initializeTenant($tenant);

            if ($noUsers === false) {
                $users = $this->tenantService->createDefaultUsers($tenant, $domain);
            }

            $io->success('Tenant successfully created.');
            $io->definitionList(
                ['Tenant ID' => $tenant->getId()],
                ['Name' => $tenant->getName()],
                ['Code' => $tenant->getCode()],
                ['Hosts' => implode(',', array_map(
                    static function (TenantHost $tenantHost): string { return $tenantHost->getHost(); },
                    $tenant->getHosts()->toArray()
                ))],
                ['Active' => $tenant->getIsActive() ? 'Yes' : 'No'],
                ['Created users' => count($users)]
            );

            /**
             * @var int $index
             * @var User $user
             */
            foreach ($users as $index => $user) {
                $index++;

                $io->definitionList(
                    ["User {$index} email" => $user->getEmail()],
                    ["User {$index} password" => 'secret']
                );
            }
        } catch (\Exception $e) {
            $io->error($e->getMessage());

            return 1;
        }

        return 0;
    }

    private function createTenant(string $code, string $name, array $hosts, bool $active, ?string $id): Tenant
    {
        $tenant = new Tenant();
        $tenant
            ->setCode($code)
            ->setName($name)
            ->setIsActive($active);

        foreach ($hosts as $host) {
            $tenant->addHost($host);
        }

        if ($id) {
            if (!Uuid::isValid($id)) {
                throw new InvalidArgumentException("Argument 'id' must be an UUID v4");
            }

            $tenant->setId(Uuid::fromString($id));
        }

        return $tenant;
    }

}
