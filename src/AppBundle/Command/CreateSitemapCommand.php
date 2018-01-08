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
use AppBundle\Service\WebService;
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
        //ini_set('memory_limit', 277872640);
        $domain = $input->getArgument('domain_name');
        $web = NULL;
        if($domain!== NULL){
            $web = $this->getContainer()->get(WebService::class)->getWebByNameOrDomain($domain);
            if($web !== NULL){
                if($web->getActive() && $web->getSitemapSettings()->getActive()){
                    $this->generateSitemap($web->getDomain());
                    //$output->writeln($web->getDomain());
                } else {
                    $output->writeln('The Web or the SitemapSettings for the Web isn\'t active. Set it active when you want to create a Sitemap.xml');
                }
            } else {
                $output->writeln('No Web is found under this name!');
            }
        } else{
            $webs = $this->getContainer()->get(WebService::class)->getWebs();
            foreach ($webs as $web){
                if(!is_null($web->getSitemapSettings())){                               //Wenn Webs are not created with the Webinterface, they might not have SitemapSettings yet
                    if($web->getActive() && $web->getSitemapSettings()->getActive()){
                        $test = $this->generateSitemap($web->getDomain());
                        /*foreach ($test as $link){
                            $output->writeln($link['url']);
                        }
                        $output->writeln('------------------------------');*/
                    }
                }
            }
        }


    }

    public function generateSitemap($domain){
        $domainname = str_replace(['http://', 'https://', 'www.'], '', $domain);
        $path = './sitemaps/'.$domainname.'/';
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        $crawler = $this->getContainer()->get(Crawler::class);

        $dom = $crawler->crawl($domain, 10);
        $validLinks = array();
        $i=0;
        foreach ($dom->links() as $link) {
            if ($link['visited']) {
                $validLinks[$i] = $link;
                $i++;
            }
        }
        $dom->setLinksBack();

        //TODO for testing (sitemaps over 50000 links) - everything is alright
        /*$validLinks = [];
        for($i=0; $i < 112856; $i++){
            $validLinks[$i]['url'] = 'nureintest.de'.$i;
        }*/

        $sitemapGenerator = $this->getContainer()->get(GenerateSitemap::class);
        $sitemapGenerator->generateSitemap($validLinks,$domain);

        return $dom->links();
    }
}