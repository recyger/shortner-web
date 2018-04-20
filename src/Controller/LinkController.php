<?php

namespace App\Controller;

use App\Entity\Link;
use App\Entity\User;
use App\Entity\View;
use App\Form\LinkType;
use App\Repository\LinkRepository;
use recyger\encry\int\Decoder;
use recyger\encry\int\Encoder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/link")
 */
class LinkController extends Controller
{
    /**
     * @TODO Вынести в bundle со своими настройками
     * @var string Словарь для шифрования id
     */
    private $dictonary = 'aKjDAg3rYNdmpin81u7wTIZCfXt2LP6G';

    /**
     * @Route("/", name="link_index", methods="GET")
     */
    public function index(LinkRepository $linkRepository): Response
    {
        return $this->render('link/index.html.twig', ['links' => $linkRepository->findAll()]);
    }

    /**
     * @Route("/new", name="link_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $link = new Link();
        $form = $this->createForm(LinkType::class, $link);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // FIXME: Необходимо организовать проверку на то что ссылка жива, и вообще является ссылкой
            $em = $this->getDoctrine()->getManager();
            // FIXME: Хардкод из за недостатка времени
            $link->setUser($em->getRepository(User::class)->find(1));
            $link->setCreatedAt(new \DateTime());
            $em->persist($link);
            $em->flush();

            return $this->redirectToRoute('link_show', ['id' => $link->getId()]);
        }

        return $this->render('link/new.html.twig', [
            'link' => $link,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="link_show", methods="GET")
     * @throws \recyger\encry\int\exceptions\InProgressException
     */
    public function show(Link $link): Response
    {
        $encoder = new Encoder($this->dictonary);
        $link->setShortner($encoder->encode($link->getId()));

        return $this->render('link/show.html.twig', ['link' => $link]);
    }

    /**
     * @Route("/{id}/edit", name="link_edit", methods="GET|POST")
     */
    public function edit(Request $request, Link $link): Response
    {
        $form = $this->createForm(LinkType::class, $link);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('link_edit', ['id' => $link->getId()]);
        }

        return $this->render('link/edit.html.twig', [
            'link' => $link,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="link_delete", methods="DELETE")
     */
    public function delete(Request $request, Link $link): Response
    {
        if ($this->isCsrfTokenValid('delete'.$link->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($link);
            $em->flush();
        }

        return $this->redirectToRoute('link_index');
    }

    /**
     * @Route("/{link}", name="link_redirect", methods="GET")
     * @throws \recyger\encry\int\exceptions\InProgressException
     */
    public function shortner(Request $request, string $link): Response
    {
        // Декодируем идентификатор
        $decoder = new Decoder($this->dictonary);
        $id = $decoder->decode($link);
        /** @var Link $link */
        $link = $this->getDoctrine()->getRepository(Link::class)->find($id);

        // Записываем просмотор
        // TODO: Необходимо реализовать очередь, что бы не мучать бд
        $em = $this->getDoctrine()->getManager();
        $view = new View();
        $view->setAgent($request->headers->get('User-Agent'));
        $view->setCreatedAt(new \DateTime());

        $em->persist($view);

        // Добавляем к ссылке
        $link->addView($view);

        $em->persist($link);
        $em->flush();

        return $this->redirect($link->getUrl());
    }
}
