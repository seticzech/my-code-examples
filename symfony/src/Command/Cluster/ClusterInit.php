<?php

namespace App\Command\Cluster;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class ClusterInit extends ClusterAbstract
{

    /**
     * @var string
     */
    protected static $defaultName = 'app:cluster:init';


    protected function configure()
    {
        $this->setDescription('Init database server cluster, create necessary users')
            ->addArgument('root_pwd', InputArgument::REQUIRED, 'Password of DB root user (e.g. postgres)')
            ->addArgument('hostname', InputArgument::OPTIONAL, 'PostgreSQL hostname. Using unix domain socket if not set.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $output->writeln('Initializing database cluster...');

            $this->checkConfig();

            $rootPwd = $input->getArgument('root_pwd');
            $hostname = $input->getArgument('hostname');

            $sqlFile = $this->prepareSqlFile('init.sql', 'cluster', $this->createEnvPlaceholders());
            
            $processArgs = [
                'psql', '-U', 'postgres', '-q', '-f', $sqlFile
            ];
            
            if ($hostname) {
                $processArgs[] = '-h';
                $processArgs[] = $hostname;
            }

            $process = new Process($processArgs, null, ['PGPASSWORD' => $rootPwd]);
            $process->run(function ($type, $buffer) use ($output) {
                $output->write($buffer);
            });

            $output->writeln('Done.');

            unlink($sqlFile);
        } catch (Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");

            return 1;
        }

        return 0;
    }

}
