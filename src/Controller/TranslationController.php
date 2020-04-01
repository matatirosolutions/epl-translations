<?php

namespace App\Controller;

use App\Entity\Page;
use App\Entity\Translation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/translation")
 */
class TranslationController extends AbstractController
{
    /**
     * @Route("/strings", name="strings")
     */
    public function index(EntityManagerInterface $em)
    {
        return $this->render('translation/strings.html.twig', [
            'translations' => $em->getRepository(Translation::class)
                ->findAll(),
        ]);
    }

    /**
     * @Route("/string/{uuid}", name="string_edit")
     */
    public function saveString(string $uuid, EntityManagerInterface $em, Request $request): JsonResponse
    {
        $translation = $em->getRepository(Translation::class)->find($uuid);
        $translation->setEnglish($request->request->get('text'));
        $em->flush();

        return new JsonResponse(['success' => true]);
    }

    /**
     * @Route("/pages", name="pages")
     */
    public function pages(EntityManagerInterface $em)
    {
        return $this->render('translation/pages.html.twig', [
            'pages' => $em->getRepository(Page::class)
                ->findAll(),
        ]);
    }

    /**
     * @Route("/page/{uuid}", name="page", methods={"GET"})
     */
    public function page(EntityManagerInterface $em, string $uuid)
    {
        $repo = $em->getRepository(Page::class);
        return $this->render('translation/page.html.twig', [
            'pages' => $repo->findAll(),
            'page' => $repo->find($uuid)
        ]);
    }

    /**
     * @Route("/page/{uuid}", name="page_post", methods={"POST"})
     */
    public function pagePost(string $uuid, EntityManagerInterface $em, Request $request)
    {
        $repo = $em->getRepository(Page::class);
        $page = $repo->find($uuid);
        $page->setEnglish($request->request->get('text'));
        $em->flush();

        return new JsonResponse(['success' => true]);
    }
}
