<?php
/**
 * Created by PhpStorm.
 * User: Christoph
 * Date: 23.12.2017
 * Time: 17:06
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Web;
use AppBundle\Service\Crawler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CrawlerController extends Controller
{

    /**
     * @Route("/{id}/crawler_options", name="crawler_options")
     * @param Web $web
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showCrawlingOption(Web $web){
        return $this->render('crawler/show.html.twig', [
            'web' => $web,
        ]);
    }

    /**
     * @Route("/{id}/crawling", name="crawling")
     * @param Web $web
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function crawlingAction(Web $web)
    {

        //set_time_limit(0);
        $links =[];
        $crawler = $this->get(Crawler::class);

        $dom = $crawler->crawl($web->getDomain(), 10);
        $test = $dom->testvariable;
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
            'test' => $test
        ]);
    }
}