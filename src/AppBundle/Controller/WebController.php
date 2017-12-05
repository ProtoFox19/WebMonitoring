<?php
/**
 * Created by PhpStorm.
 * User: Christoph
 * Date: 27.11.2017
 * Time: 20:40
 */

namespace AppBundle\Controller;



use AppBundle\Form\WebFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WebController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function showMainPageAction()
    {
        $webs = $this->getWebs();
        return $this->render('web/show.html.twig', [
            'testVariable' => 'hallo',
            'page' => 'start',
            'webs' => $webs
        ]);
    }

    private function getWebs(){
        $em = $this->getDoctrine()->getManager();
        $webs = $em->getRepository('AppBundle:Web')
            ->findAll();
        return $webs;
    }

    /**
     * @Route("/new", name="newWeb")
     */
    public function newWebAction(Request $request)
    {
        $form = $this->createForm(WebFormType::class);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $web = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($web);
            $em->flush();

            $this->addFlash('success', 'Web is stored correctly.');

            return $this->redirectToRoute('homepage');
        }

        return $this->render('web/new.html.twig', [
            'webForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/delete", requirements={"id" = "\d+"}, name="delete_web")
     */
    public function deleteWebAction($id){
        $em = $this->getDoctrine()->getManager();
        $web = $em->getRepository('AppBundle:Web')->find($id);

        if (!$web) {
            throw $this->createNotFoundException(
                'No web found for id '.$id
            );
        }

        $em->remove($web);
        $em->flush();

        return $this->redirectToRoute('homepage');
    }
}