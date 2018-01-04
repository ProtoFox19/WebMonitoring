<?php
/**
 * Created by PhpStorm.
 * User: Christoph
 * Date: 29.12.2017
 * Time: 20:31
 */

namespace AppBundle\Command;


use AppBundle\Service\Crawler;
use AppBundle\Service\GenerateSitemap;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateSitemapCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:create-Sitemap')

            // the short description shown while running "php bin/console list"
            ->setDescription('Creates a new Sitemap.xml of one or all active Webs.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to create a Sitemap.xml of one or all active Webs. If you want to create a Sitemap for one Web, add the Domain or Name as an Argument')

            ->addArgument(
                'domain_name',
                InputArgument::OPTIONAL,
                'For which Site do you want to create a Sitemap.xml? If no Value given, it will create a Sitemap for all "active" websites (Valid Arguments: Name and Domain of the Web)'
            )
            ->setHidden(true);
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $domain = $input->getArgument('domain_name');
        $domainname = str_replace(['http://', 'https://', '.de', '.com', 'www.'], '', $domain);
        $path = './sitemaps/'.$domainname.'/';
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        $crawler = $this->getContainer()->get(Crawler::class);

        $dom = $crawler->crawl($domain, 10);
        $validLinks = [];
        $i=0;
        foreach ($dom->links() as $link) {
            if ($link['visited']) {
                //$output->writeln('' . $link['url'] . '');
                $validLinks[$i] = $link;
                //$validLinks[$i] = '' . $link['url'] . '';
                $i++;
            }
        }

        $sitemapGenerator = $this->getContainer()->get(GenerateSitemap::class);
        $sitemapGenerator->generateSitemap($validLinks,$domainname);
    }
}