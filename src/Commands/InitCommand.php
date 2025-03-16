<?php

namespace Beam\BeamCore\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

#[AsCommand(
    name: 'init',
    description: 'Init',
    hidden: false
)]
class InitCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fileContent = Yaml::parseFile(__DIR__ . '/beam-base.yaml');
        $yamlContent = Yaml::dump($fileContent);

        $filePath = appBasePath() . 'beam.yaml';

        file_put_contents($filePath, $yamlContent);

        $output->writeln('');
        $output->writeln('  ✅  <info>Beam has been successfully configured!</info>');
        $output->writeln('');
        $output->writeln('  ✏️ <info>A file with the settings was created in the root of your project: </info>');
        $output->writeln('');

        return Command::SUCCESS;
    }
}
