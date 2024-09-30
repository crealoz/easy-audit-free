<?php
namespace Crealoz\EasyAudit\Console;

use Composer\Console\Input\InputOption;
use Magento\Framework\Exception\FileSystemException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Christophe Ferreboeuf <christophe@crealoz.fr>
 */
class RunAuditCommand extends Command
{
    public function __construct(
        protected \Crealoz\EasyAudit\Service\Audit $auditService
    )
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('crealoz:run:audit')
            ->setDescription('Run the audit service on request')
            ->addOption('language', 'l', InputOption::VALUE_OPTIONAL, 'Language to use for the audit service', 'en_US')
        ;
    }

    /**
     * @throws FileSystemException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = microtime(true);
        $output->writeln('Starting audit service...');

        $language = $input->getOption('language');

        $this->auditService->run($output, $language);

        $duration = microtime(true) - $start;
        // Output the duration in a human readable format
        $duration = round($duration);
        $duration = gmdate('H:i:s', $duration);
        $output->writeln(PHP_EOL.'Audit service has been run successfully in '. $duration .'.');
        return Command::SUCCESS;
    }
}
