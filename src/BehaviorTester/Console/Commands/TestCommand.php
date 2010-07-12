<?php

namespace BehaviorTester\Console\Commands;

use \Symfony\Components\Console\Command\Command;
use \Symfony\Components\Console\Input\InputInterface;
use \Symfony\Components\Console\Input\InputArgument;
use \Symfony\Components\Console\Input\InputOption;
use \Symfony\Components\Console\Output\OutputInterface;
use \Symfony\Components\Finder\Finder;

use \Gherkin\Feature;
use \Gherkin\Background;
use \Gherkin\Scenario;
use \Gherkin\ScenarioOutline;

use \BehaviorTester\FeatureRuner;
use \BehaviorTester\Definitions\StepsContainer;
use \BehaviorTester\Printers\ConsolePrinter;

class TestCommand extends Command
{
    protected function configure()
    {
        $this->setName('test');

        $this->setDefinition(array(
            new InputArgument('features', InputArgument::OPTIONAL, 'Features folder', './features')
        ));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $basePath = realpath(dirname($input->getArgument('features')));

        // Init test printer
        $printer = new ConsolePrinter($output, $basePath);

        // Read steps definition from files
        $finder = new Finder();
        $files = $finder->files()->name('*.php')->in($basePath . '/steps');
        $steps = new StepsContainer();
        try {
            foreach ($files as $file) {
                require $file;
            }
        } catch (Redundant $e) {
            $output->writeln(sprintf("<failed>%s</failed>\n",
                strtr($e, array($basePath . '/' => ''))
            ));
        }

        // Read feature files
        $finder = new Finder();
        $files = $finder->files()->name('*.feature')->in($input->getArgument('features'));

        foreach ($files as $file) {
            $runer = new FeatureRuner($file, $printer, $steps);
            $runer->run();
            $output->writeln('');
        }
    }
}
