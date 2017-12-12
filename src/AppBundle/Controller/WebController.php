<?php
/**
 * Created by PhpStorm.
 * User: Christoph
 * Date: 27.11.2017
 * Time: 20:40
 */

namespace AppBundle\Controller;



use AppBundle\Entity\Web;
use AppBundle\Form\EditWebFormType;
use AppBundle\Form\WebFormType;
use AppBundle\Service\WebService;
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
        $webs = $this->get('AppBundle\Service\WebService')->getWebs();  //new way, autowireing guess the right constructor of WebService, so in service.yml it must no longer be included
       /* $webService = new WebService($this->getDoctrine()->getManager());
        $webs = $webService->getWebs();*/
        return $this->render('web/show.html.twig', [
            'webs' => $webs
        ]);
    }



    /**
     * @Route("/new", name="new_web")
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
     * @Route("/{id}/edit", name="edit_web")
     */
    public function editWebAction(Request $request, Web $web)
    {
        $form = $this->createForm(EditWebFormType::class, $web);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $web = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($web);
            $em->flush();

            $this->addFlash('success', 'Web is updated correctly.');

            return $this->redirectToRoute('homepage');
        }

        return $this->render('web/edit.html.twig', [
            'editWebForm' => $form->createView()
        ]);
    }


    /**
     * @Route("/{id}/delete", requirements={"id" = "\d+"}, name="delete_web")
     */
    public function deleteWebAction(Web $web){
        $em = $this->getDoctrine()->getManager();
       /* $web = $em->getRepository('AppBundle:Web')->find($id);

        if (!$web) {
            throw $this->createNotFoundException(
                'No web found for id '.$id
            );
        }*/

        $em->remove($web);
        $em->flush();

        $this->addFlash('success', 'Web was deleted.');

        return $this->redirectToRoute('homepage');
    }
}