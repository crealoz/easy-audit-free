<?php
namespace Crealoz\EasyAudit\Console;

use Symfony\Component\Console\Input\InputOption;
use Crealoz\EasyAudit\Model\AuditStorage;
use Magento\Framework\Exception\FileSystemException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Christophe Ferreboeuf <christophe@crealoz.fr>
 */
class RunAuditCommand extends Command
{
    protected \Crealoz\EasyAudit\Service\Audit $auditService;
    /**
     * @readonly
     */
    protected AuditStorage $auditStorage;
    public function __construct(
        \Crealoz\EasyAudit\Service\Audit $auditService,
        AuditStorage $auditStorage
    )
    {
        $this->auditService = $auditService;
        $this->auditStorage = $auditStorage;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('crealoz:audit:run')
            ->setDescription('Run the audit service on request')
            ->addOption(
                'language',
                'l',
                InputOption::VALUE_OPTIONAL,
                'Language to use for the audit service',
                'en_US'
            )
            ->addOption(
                'ignored-modules',
                'i',
                InputOption::VALUE_OPTIONAL,
                'List of modules to ignore',
                ''
            )
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

        $ignoredModules = $input->getOption('ignored-modules');
        if (!empty($ignoredModules)) {
            $this->auditStorage->setIgnoredModules(explode(',',$ignoredModules));
        }

        $this->auditService->run($output, $language);

        $duration = microtime(true) - $start;
        // Output the duration in a human-readable format
        $duration = round($duration);
        $duration = gmdate('H:i:s', $duration);
        $output->writeln(PHP_EOL.'Audit service has been run successfully in '. $duration .'.');
        return Command::SUCCESS;
    }
}
