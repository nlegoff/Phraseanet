<?php

/*
 * This file is part of Phraseanet
 *
 * (c) 2005-2014 Alchemy
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemy\Phrasea\Command\SearchEngine;

use Alchemy\Phrasea\Command\Command;
use Alchemy\Phrasea\SearchEngine\Elastic\Indexer;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class IndexPopulateCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('searchengine:index:populate')
            ->setDescription('Populate search index with record data')
            ->addOption(
                'databox_id',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                '',
                null
            );;
    }

    protected function doExecute(InputInterface $input, OutputInterface $output)
    {

        $databox_ids = $input->getOption('databox_id');

        $this->container['elasticsearch.indexer']->populateIndex($databox_ids);
    }
}
