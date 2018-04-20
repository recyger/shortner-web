<?php

namespace App\Controller;

use App\Entity\View;
use App\Form\ViewType;
use App\Repository\ViewRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/view")
 */
class ViewController extends Controller
{
    /**
     * @Route("/", name="view_index", methods="GET")
     */
    public function index(ViewRepository $viewRepository): Response
    {
        return $this->render('view/index.html.twig', ['views' => $viewRepository->findAll()]);
    }

    /**
     * @Route("/new", name="view_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $view = new View();
        $form = $this->createForm(ViewType::class, $view);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($view);
            $em->flush();

            return $this->redirectToRoute('view_index');
        }

        return $this->render('view/new.html.twig', [
            'view' => $view,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="view_show", methods="GET")
     */
    public function show(View $view): Response
    {
        return $this->render('view/show.html.twig', ['view' => $view]);
    }

    /**
     * @Route("/{id}/edit", name="view_edit", methods="GET|POST")
     */
    public function edit(Request $request, View $view): Response
    {
        $form = $this->createForm(ViewType::class, $view);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('view_edit', ['id' => $view->getId()]);
        }

        return $this->render('view/edit.html.twig', [
            'view' => $view,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="view_delete", methods="DELETE")
     */
    public function delete(Request $request, View $view): Response
    {
        if ($this->isCsrfTokenValid('delete'.$view->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($view);
            $em->flush();
        }

        return $this->redirectToRoute('view_index');
    }
}
