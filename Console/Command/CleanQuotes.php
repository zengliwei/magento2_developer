<?php
/**
 * Copyright (c) Zengliwei. All rights reserved.
 * Each source file in this distribution is licensed under OSL 3.0, see LICENSE for details.
 */

namespace CrazyCat\Developer\Console\Command;

use Magento\Framework\App\ResourceConnection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author  Zengliwei <zengliwei@163.com>
 * @url https://github.com/zengliwei/magento2_developer
 */
class CleanQuotes extends Command
{
    private const OPT_CUSTOMER_ID = 'customer_id';

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @param ResourceConnection $resource
     * @param string|null        $name
     */
    public function __construct(
        ResourceConnection $resource,
        string $name = null
    ) {
        $this->resource = $resource;
        parent::__construct($name);
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('dev:clean-quotes');
        $this->setDescription('Clean quotes');
        $this->setDefinition(
            [
                new InputOption(
                    self::OPT_CUSTOMER_ID,
                    'c',
                    InputOption::VALUE_OPTIONAL,
                    'Customer ID'
                )
            ]
        );
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $customerId = $input->getOption(self::OPT_CUSTOMER_ID);
        $conn = $this->resource->getConnection();

        try {
            if ($customerId) {
                $quoteIds = $conn->fetchCol(
                    $conn->select()
                        ->from($conn->getTableName('quote'), ['entity_id'])
                        ->where('customer_id = ?', $customerId)
                );
                $quoteItemIds = $conn->fetchCol(
                    $conn->select()
                        ->from($conn->getTableName('quote_item'), ['item_id'])
                        ->where('quote_id IN (?)', $quoteIds)
                );
                $quoteAddressIds = $conn->fetchCol(
                    $conn->select()
                        ->from($conn->getTableName('quote_address'), ['address_id'])
                        ->where('quote_id IN (?)', $quoteIds)
                );

                $conn->delete($conn->getTableName('quote_shipping_rate'), ['address_id IN (?)' => $quoteAddressIds]);
                $conn->delete($conn->getTableName('quote_address'), ['quote_id IN (?)' => $quoteIds]);
                $conn->delete($conn->getTableName('quote_id_mask'), ['quote_id IN (?)' => $quoteIds]);
                $conn->delete($conn->getTableName('quote_item_option'), ['item_id IN (?)' => $quoteItemIds]);
                $conn->delete($conn->getTableName('quote_item'), ['quote_id IN (?)' => $quoteIds]);
                $conn->delete($conn->getTableName('quote_payment'), ['quote_id IN (?)' => $quoteIds]);
                $conn->delete($conn->getTableName('quote'), ['entity_id IN (?)' => $quoteIds]);

                $output->writeln('<info>Quotes of specified customer deleted.</info>');
            } else {
                $conn->delete($conn->getTableName('quote_shipping_rate'));
                $conn->delete($conn->getTableName('quote_address'));
                $conn->delete($conn->getTableName('quote_id_mask'));
                $conn->delete($conn->getTableName('quote_item_option'));
                $conn->delete($conn->getTableName('quote_item'));
                $conn->delete($conn->getTableName('quote_payment'));
                $conn->delete($conn->getTableName('quote'));

                $output->writeln('<info>All quotes cleaned.</info>');
            }
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }
}
