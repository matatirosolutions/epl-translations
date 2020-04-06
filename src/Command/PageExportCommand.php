<?php

namespace App\Command;

use App\Entity\Page;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PageExportCommand extends Command
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var string */
    protected $projectRoot;

    protected static $defaultName = 'translation:export:pages';

    /**
     * TranslationExportCommand constructor.
     * @param EntityManagerInterface $entityManager
     * @param string $projectDir
     */
    public function __construct(EntityManagerInterface $entityManager, string $projectRoot)
    {
        $this->entityManager = $entityManager;
        $this->projectRoot = $projectRoot;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Export page translations')
            ->addArgument('path', InputArgument::OPTIONAL, 'Path to export to. Existing files will be overwritten.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $template = $this->loadTemplate();

            /** @var Page[] $pages */
            $pages = $this->entityManager->getRepository(Page::class)
                ->findAll();
            foreach($pages as $page) {
                $this->writeToFile(
                    $this->setPageFilePath($input->getArgument('path'), $page),
                    $this->populateTemplate($template, $page)
                );
            }

        } catch (RuntimeException $exception) {
            $io->error($exception->getMessage());
        }

        $io->write('Done!');
        return 0;
    }

    private function loadTemplate()
    {
        return file_get_contents($this->projectRoot . DIRECTORY_SEPARATOR .
            'templates' . DIRECTORY_SEPARATOR .
            'pages-template.html.twig');
    }

    private function setPageFilePath(string $path, Page $page)
    {
        return $path . (substr($path, -1) === DIRECTORY_SEPARATOR ? '' : DIRECTORY_SEPARATOR) .
            $page->getFile() . '.html.twig';
    }

    private function populateTemplate($template, Page $page): string
    {
        return str_replace('#content#', $page->getEnglish(), $template);
    }

    private function writeToFile(string $path, string $content): void
    {
        file_put_contents($path, $content);
    }
}
