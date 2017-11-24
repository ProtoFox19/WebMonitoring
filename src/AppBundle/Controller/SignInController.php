<?php
/**
 * Created by PhpStorm.
 * User: Christoph
 * Date: 24.11.2017
 * Time: 06:39
 */

namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

class SignInController
{
    /**
     * @Route("/SignIn")
     */
    public function showAction()
    {
        return new Response("Hallo World");
    }
}