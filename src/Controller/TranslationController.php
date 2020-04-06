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
     * @Route("/", name="translation_home")
     */
    public function index(EntityManagerInterface $em)
    {
        return $this->render('translation/index.html.twig');
    }

    /**
     * @Route("/strings", name="strings")
     */
    public function strings(EntityManagerInterface $em)
    {
        return $this->render('translation/strings.html.twig', [
            'translations' => $em->getRepository(Translation::class)
                ->findAll(),
        ]);
    }

    /**
     * @Route("/string/{uuid}/{language}", name="string_edit")
     */
    public function saveString(EntityManagerInterface $em, Request $request, string $uuid, string $language): JsonResponse
    {
        $translation = $em->getRepository(Translation::class)->find($uuid);
        $translation->{"set$language"}($request->request->get('text'));
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
     * @Route("/page/{uuid}/{language}", name="page_post", methods={"POST"})
     */
    public function pagePost(EntityManagerInterface $em, Request $request, string $uuid, string $language)
    {
        $repo = $em->getRepository(Page::class);
        $page = $repo->find($uuid);
        $page->{"set$language"}($request->request->get('text'));
        $em->flush();

        return new JsonResponse(['success' => true]);
    }
}
