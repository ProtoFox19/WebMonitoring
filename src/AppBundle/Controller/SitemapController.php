<?php

namespace AppBundle\Controller;

use AppBundle\Entity\SitemapSettings;
use AppBundle\Entity\Web;
use AppBundle\Form\EditSitemapSettingsFormType;
use AppBundle\Service\SitemapService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Service\Crawler;
use AppBundle\Command\CreateSitemapCommand;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;

class SitemapController extends Controller
{
    /**
     * @Route("/{id}/sitemap_options", name="sitemap_options")
     * @param Request $request
     * @param Web $web
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showSitemapOptionAction(Request $request, Web $web)
    {
        $sitemapSetting = $web->getSitemapSettings();
        if (is_null($sitemapSetting)) {   //when the path is called the first time and the web has no settinginformation (case if the web is not created over the webinterface), set a default setting automatically
            $newSitemapSetting = $this->get(SitemapService::class)->createDefaultSitemapSetting($web);

            $em = $this->getDoctrine()->getManager();
            $em->persist($newSitemapSetting);
            $em->flush();
            //return $this->redirectToRoute('sitemap_options',array('id' => $web->getId()));
            $sitemapSetting = $newSitemapSetting;
        }

        $form = $this->createForm(EditSitemapSettingsFormType::class, $sitemapSetting);
        $form->handleRequest($request);
        $sitemapSettingtest = $form->getData();
        if ($form->isSubmitted() && $form->isValid()) {
            $sitemapSetting = $form->getData();
            $sitemapSetting->setWeb($web);  //for safety
            $em = $this->getDoctrine()->getManager();
            $em->persist($sitemapSetting);
            $em->flush();

            $this->addFlash('success', 'Sitemap Seetings for ' . $web->getName() . ' is updated correctly.');

            return $this->redirectToRoute('sitemap_options', array('id' => $web->getId()));
        }
        $lastMod = '';
        $domain = str_replace(['http://', 'https://', 'www.'], '', $web->getDomain());
        if (substr($domain, -1) == '/') {
            $domain = substr($domain, 0, -1);
        }
        if (file_exists($this->get('kernel')->getRootDir() . '/../sitemaps/' . $domain . '/sitemap.zip')) {
            $xmlFile = new File($this->get('kernel')->getRootDir() . '/../sitemaps/' . $domain . '/sitemap.zip');
            $lastMod = date("Y-m-d H:i:s", $xmlFile->getMTime());
        }

        return $this->render('sitemap/show.html.twig', [
            'web' => $web,
            'lastMod' => $lastMod,
            'test' => preg_split('/\r\n|\r|\n/', $sitemapSettingtest->getNotIncludedPath()),
            'editSitemapForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/sitemapDownload", name="sitemapDownload")
     * @param Web $web
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadAction(Web $web)
    {
        $domain = str_replace(['http://', 'https://', 'www.'], '', $web->getDomain());
        if (substr($domain, -1) == '/') {
            $domain = substr($domain, 0, -1);
        }

        $xmlFile = new File($this->get('kernel')->getRootDir() . '/../sitemaps/' . $domain . '/sitemap.zip');
        if (!$xmlFile->isFile()) {
            throw $this->createNotFoundException();
        }

        return $this->file($xmlFile, $web->getName() . '_sitemap.zip');
    }

    /**
     * Just for Testing!
     * @Route("/{id}/crawling_web", name="crawling_web")
     * @param Web $web
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function crawlingAction(Web $web)
    {

        set_time_limit(0);
        $links = [];
        $crawler = $this->get(Crawler::class);

        $dom = $crawler->crawl($web->getDomain(), 10);

        $test = $dom->testvariable;
        $testzwei = $dom->testvariablezwei;
        $i = 0;
        foreach ($dom->links() as $link) {
            /*$links[$i] = ''.$link['url'].'';
            $i++;*/
            if ($link['visited']) {
                $links[$i] = '' . $link['url'] . '';
                $i++;
            }
        }

        return $this->render('sitemap/show_crawling.html.twig', [
            'web' => $web,
            'links' => $links,
            'test' => $test,
            'testzwei' => $testzwei
        ]);
    }


    /**
     * Just for Testing!
     * @Route("/{id}/create_sitemap", requirements={"id" = "\d+"}, name="create_sitemap")
     * @param Web $web
     * @param KernelInterface $kernel
     * @return
     */
    public function createSitemapAction(Web $web, KernelInterface $kernel)
    {
        /* $test='';
         $testzwei ='';
         $content ='';
         $domain = str_replace(['http://', 'https://', '.de', '.com', 'www.'], '', $web->getDomain());
         $process = new Process('php bin/console app:create-Sitemap TestingDomain.de');
         $process->setWorkingDirectory('../');
         $process->run();

           $application = new Application($kernel);
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


        /* if (substr(php_uname(), 0, 7) == "Windows"){
             chdir('C:\xampp\htdocs\Symfony\WebMonitoring');
             // $test = getcwd();
             // pclose(popen("start /B " . "php " . " " . escapeshellarg("bin/console " . (new CreateSitemapCommand())->getName().' '. $web->getDomain()), "r"));
             //$test = "start /B " . "php bin/console" . " " . (new CreateSitemapCommand())->getName()." ". $domain;
             pclose(popen("start /B " . "php bin/console" . " " . (new CreateSitemapCommand())->getName(). " " . $domain, 'r')); //TODO kommt nicht mit bin/console klar
         } else {
             $testzwei = 'wut';
             exec("./" . "php" . " " . escapeshellarg("C:\xampp\htdocs\Symfony\WebMonitoring\src\AppBundle\Service\GenerateSitemap.php") . " > /dev/null &");
         }

         //$this->addFlash('success', 'The Sitemap will be generated. It may take some time.' . $process);
         //return $this->redirectToRoute('homepage');
         return $this->render('sitemap/show_crawling.html.twig', [
             'web' => $web,
             'links' => $process->getOutput(),
             'test' => $test,
             'testzwei' => $testzwei
         ]);*/
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            chdir('../');
            //$test = getcwd();
            $testzwei = system('php bin/console '.(new CreateSitemapCommand())->getName().' '. $web->getDomain() ." > NUL");
        }else{
            chdir('../');
            $test = getcwd();
            $testzwei = shell_exec('php bin/console '.(new CreateSitemapCommand())->getName().' '. $web->getDomain() ." >/dev/null 2>&1 &");
        }
        $this->addFlash('success', 'Sitemap will be generated for ' . $web->getName() . '. It may take a few Minutes.');

        return $this->redirectToRoute('sitemap_options', array('id' => $web->getId()));

    }
}
