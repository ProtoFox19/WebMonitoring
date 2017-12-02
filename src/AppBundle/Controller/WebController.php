<?php
/**
 * Created by PhpStorm.
 * User: Christoph
 * Date: 27.11.2017
 * Time: 20:40
 */

namespace AppBundle\Controller;



use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class WebController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function showMainPage()
    {
        return $this->render('web/show.html.twig', [
            'testVariable' => 'hallo',
            'page' => 'start'
        ]);
    }
}