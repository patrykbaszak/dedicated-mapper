<?php

namespace PBaszak\DedicatedMapper\Command;

use PBaszak\DedicatedMapper\Config;
use PBaszak\DedicatedMapper\Tests\assets\Dummy;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

require_once __DIR__ . '/../../tests/assets/Dummy.php';

#[AsCommand(
    name: 'mapper:create-config',
    description: 'Creates mapper config for specific class.',
)]
class CreateConfig extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        file_put_contents('output.yaml', Yaml::dump((new Config(Dummy::class))->reflect()->export(), 256, 4));

        return Command::SUCCESS;
    }
}
