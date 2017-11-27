<?php
/**
 * Created by PhpStorm.
 * User: Christoph
 * Date: 27.11.2017
 * Time: 20:40
 */

namespace AppBundle\Controller;


use FOS\UserBundle\Controller\SecurityController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class WebController extends SecurityController
{
    /**
     * @Route("/", name="homepage")
     */
    public function showAction()
    {
        return $this->render('web/show.html.twig', [
            'testVariable' => 'hallo'
        ]);
    }
}