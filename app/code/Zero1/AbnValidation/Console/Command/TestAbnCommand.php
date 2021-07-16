<?php
namespace Zero1\AbnValidation\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zero1\AbnValidation\Model\AbnValidator;

class TestAbnCommand extends Command
{
    const OPTION_ABN = 'abn';

    protected $abnValidator;

    public function __construct(
        AbnValidator $abnValidator
    ){
        $this->abnValidator = $abnValidator;
        return parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('zero1:abn:test');
        $this->setDescription('Test a specific ABN');
        $this->addOption(
            self::OPTION_ABN,
            null,
            InputOption::VALUE_REQUIRED,
            'ABN to test'
        );
        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return null|int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $abn = $input->getOption(self::OPTION_ABN);
        $debug = $input->getOption('verbose');
        if(!$abn){
            $output->writeln('<error>You must provide an ABN.</error>');
            return 2;
        }
        $output->writeln('<comment>ABN: '.$abn.'</comment>');
        $output->writeln('<comment>valid: '.json_encode($this->abnValidator->isValid($abn, $debug)).'</comment>');

        return 0;
    }
}