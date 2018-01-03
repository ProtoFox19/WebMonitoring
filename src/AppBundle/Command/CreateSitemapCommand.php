<?php
/**
 * Created by PhpStorm.
 * User: Christoph
 * Date: 29.12.2017
 * Time: 20:31
 */

namespace AppBundle\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateSitemapCommand extends Command
{

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:create-Sitemap')

            // the short description shown while running "php bin/console list"
            ->setDescription('Creates a new Sitemap.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to create a Sitemap...')

            ->addArgument(
                'domain_name',
                InputArgument::OPTIONAL,
                'for wich Site do you want to create a Sitemap? If no Value given, than it will create a Sitemap for all "active" websites'
            )
            ->setHidden(true);
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $domain = $input->getArgument('domain_name');
        mkdir('./test4/test7/'.$domain, 0777, true);
    }
}