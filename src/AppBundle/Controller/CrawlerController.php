<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Web;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Service\Crawler;
use AppBundle\Command\CreateSitemapCommand;
use Symfony\Component\HttpKernel\KernelInterface;

class CrawlerController extends Controller
{
    /**
     * @Route("/{id}/sitemap_options", name="sitemap_options")
     * @param Web $web
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showSitemapOption(Web $web){
        return $this->render('crawler/show.html.twig', [
            'web' => $web,
        ]);
    }

    /**
     * @Route("/{id}/crawling_web", name="crawling_web")
     * @param Web $web
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function crawlingAction(Web $web)
    {

        set_time_limit(0);
        $links =[];
        $crawler = $this->get(Crawler::class);

        $dom = $crawler->crawl($web->getDomain(), 10);
        //TODO rausnehmen
        $test = $dom->testvariable;
        $testzwei = $dom->testvariablezwei;
        $i=0;
        foreach ($dom->links() as $link) {
            /*$links[$i] = ''.$link['url'].'';
            $i++;*/
            if ($link['visited']) {
                $links[$i] = ''.$link['url'].'';
                $i++;
            }
        }

        return $this->render('crawler/show_crawling.html.twig', [
            'web' => $web,
            'links' => $links,
            'test' => $test,
            'testzwei' => $testzwei
        ]);
    }


    /**
     * @Route("/{id}/create_sitemap", requirements={"id" = "\d+"}, name="create_sitemap")
     * @param Web $web
     * @param KernelInterface $kernel
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createSitemapAction(Web $web, KernelInterface $kernel){
        $test='';
        $testzwei ='';
        $content ='';
        $domain = str_replace(['http://', 'https://', '.de', '.com', 'www.'], '', $web->getDomain());

        /*  $application = new Application($kernel);
          $application->setAutoExit(false);

          $input = new ArrayInput(array(
              'command' => 'app:create-Sitemap',
              // (optional) define the value of command arguments
              'domain_name' => 'testing',
          ));

          // You can use NullOutput() if you don't need the output
          $output = new NullOutput();
          //$output = new BufferedOutput();
          $application->run($input, $output);

          // return the output, don't use if you used NullOutput()
          //$content = $output->fetch();

          // return new Response(""), if you used NullOutput()
          //return new Response("");
          */

        /*if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            chdir('C:\xampp\htdocs\Symfony\WebMonitoring');
            //$test = getcwd();
            $testzwei = system('php bin/console '.(new CreateSitemapCommand())->getName().' '. $web->getDomain() ." > NUL");
        }else{
            shell_exec(""."\htdocs\Symfony\WebMonitoring\src\AppBundle\Service\SitemapGenerator.php"." >/dev/null 2>&1 &");
        }*/


        if (substr(php_uname(), 0, 7) == "Windows"){
            chdir('C:\xampp\htdocs\Symfony\WebMonitoring');
            // $test = getcwd();
            // pclose(popen("start /B " . "php " . " " . escapeshellarg("bin/console " . (new CreateSitemapCommand())->getName().' '. $web->getDomain()), "r"));
            //$test = "start /B " . "php bin/console" . " " . (new CreateSitemapCommand())->getName()." ". $domain;
            pclose(popen("start /B " . "php bin/console" . " " . (new CreateSitemapCommand())->getName(). " " . $domain, 'r')); //TODO kommt nicht mit bin/console klar
        } else {
            $testzwei = 'wut';
            exec("./" . "php" . " " . escapeshellarg("C:\xampp\htdocs\Symfony\WebMonitoring\src\AppBundle\Service\GenerateSitemap.php") . " > /dev/null &");
        }

        $this->addFlash('success', 'The Sitemap will be generated. It may take some time.');

        return $this->redirectToRoute('homepage');
    }
}
