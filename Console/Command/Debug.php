<?php
/**
 * Copyright (c) Zengliwei. All rights reserved.
 * Each source file in this distribution is licensed under OSL 3.0, see LICENSE for details.
 */

namespace CrazyCat\Developer\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author  Zengliwei <zengliwei@163.com>
 * @url https://github.com/zengliwei/magento2_developer
 */
class Debug extends Command
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var State
     */
    private $state;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param State                  $state
     * @param string|null            $name
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        State $state,
        string $name = null
    ) {
        $this->objectManager = $objectManager;
        $this->state = $state;
        parent::__construct($name);
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('dev:debug');
        $this->setDescription('Namespace for debug');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->emulateAreaCode(
            Area::AREA_CRONTAB,
            function () {
            }
        );
        $output->writeln('<info>Run completed.</info>');
    }
}
