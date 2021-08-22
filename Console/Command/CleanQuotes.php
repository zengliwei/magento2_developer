<?php
/*
 * Copyright (c) 2020 Zengliwei
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFINGEMENT. IN NO EVENT SHALL THE AUTHORS
 * OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Common\Developer\Console\Command;

use Magento\Framework\App\ResourceConnection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package Common\Developer
 * @author  Zengliwei <zengliwei@163.com>
 * @url https://github.com/zengliwei/magento2_banner
 */
class CleanQuotes extends Command
{
    private const OPT_CUSTOMER_ID = 'customer_id';

    private ResourceConnection $resource;

    public function __construct(
        ResourceConnection $resource,
        string $name = null
    ) {
        $this->resource = $resource;
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
