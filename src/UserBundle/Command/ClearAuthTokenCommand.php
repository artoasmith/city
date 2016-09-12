<?php
/**
 * Created by PhpStorm.
 * User: N1
 * Date: 12.09.16
 * Time: 16:23
 */

namespace UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearAuthTokenCommand extends ContainerAwareCommand
{
    protected function configure() {
        $this
            ->setDescription('Clear old auth clients and tokens')
            ->setName('userbundle:auth:clear:token')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $em = $this->getContainer()->get('doctrine')->getManager();

        $query = 'DELETE FROM `client` WHERE `date`<NOW()';
        $stmt = $em->getConnection()
            ->prepare(
                $query
            );
        $stmt->execute();

        $query = 'DELETE FROM `access_token` WHERE `date`<NOW()';
        $stmt = $em->getConnection()
            ->prepare(
                $query
            );
        $stmt->execute();
    }
}