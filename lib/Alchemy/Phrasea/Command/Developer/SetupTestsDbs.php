<?php

/*
 * This file is part of Phraseanet
 *
 * (c) 2005-2014 Alchemy
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemy\Phrasea\Command\Developer;

use Alchemy\Phrasea\Command\Command;
use Alchemy\Phrasea\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class SetupTestsDbs extends Command
{
    public function __construct()
    {
        parent::__construct('ini:setup-tests-dbs');

        $this->setDescription('Setup dbs for tests environment');
    }

    protected function doExecute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->container['phraseanet.configuration']->isSetup()) {
            throw new RuntimeException(sprintf(
                'Phraseanet is not setup. You can run <info>bin/setup system::install</info> command to install Phraseanet.'
            ));
        }

        $settings = Yaml::parse(file_get_contents(__DIR__.'/../../../../../hudson/InstallDBs.yml'));

        $dbs = array();

        $dbs[] = $settings['database']['ab_name'];
        $dbs[] = $settings['database']['db_name'];

        $schema = $this->container['EM']->getConnection()->getSchemaManager();

        foreach ($dbs as $name) {
            $output->writeln('Creating database "'.$name.'"...<info>OK</info>');
            $schema->dropAndCreateDatabase($name);
        }

        $this->container['EM']->getConnection()->executeUpdate('
            GRANT ALL PRIVILEGES ON '.$settings['database']['ab_name'].'.* TO \''.$settings['database']['user'].'\'@\''.$settings['database']['host'].'\' IDENTIFIED BY \''.$settings['database']['password'].'\' WITH GRANT OPTION
        ');

        $this->container['EM']->getConnection()->executeUpdate('
            GRANT ALL PRIVILEGES ON '.$settings['database']['db_name'].'.* TO \''.$settings['database']['user'].'\'@\''.$settings['database']['host'].'\' IDENTIFIED BY \''.$settings['database']['password'].'\' WITH GRANT OPTION
        ');

        $this->container['EM']->getConnection()->executeUpdate('SET @@global.sql_mode= ""');

        return 0;
    }
}
