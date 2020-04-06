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
            ->addArgument('locale', InputArgument::REQUIRED,
                'The locale which should be exported as the target language, i.e. en_NZ or se')
            ->addArgument('path', InputArgument::OPTIONAL,
                'Path to the EPL project templates folder. Existing files will be overwritten.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if(!in_array($input->getArgument('locale'), ['en_NZ', 'se'])) {
            $io->error('The only supported locale are \'en_NZ\' and \'se\'');
            return -1;
        }

        try {
            $template = $this->loadTemplate();

            /** @var Page[] $pages */
            $pages = $this->entityManager->getRepository(Page::class)
                ->findAll();
            $filePath = $this->verifyFilePath(
                $input->getArgument('path'),
                $input->getArgument('locale')
            );

            foreach($pages as $page) {
                $this->writeToFile(
                    $this->setPageFilePath($filePath, $page),
                    $this->populateTemplate($input->getArgument('locale'), $template, $page)
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

    private function verifyFilePath(string $path, string $locale): string
    {
        $fullPath = $path . (substr($path, -1) === DIRECTORY_SEPARATOR ? '' : DIRECTORY_SEPARATOR) .
            'templates' . DIRECTORY_SEPARATOR .
            'content' . DIRECTORY_SEPARATOR .
            $locale . DIRECTORY_SEPARATOR;

        if(!is_dir($fullPath)) {
            if (!mkdir($fullPath) && !is_dir($fullPath)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $fullPath));
            }
        }

        return $fullPath;
    }

    private function setPageFilePath(string $path, Page $page): string
    {
        return $path . $page->getFile() . '.html.twig';
    }

    private function populateTemplate(string $language, string $template, Page $page): string
    {
        if('se' === $language) {
            return str_replace('#content#', $page->getSwedish(), $template);
        }

        return str_replace('#content#', $page->getEnglish(), $template);
    }

    private function writeToFile(string $path, string $content): void
    {
        file_put_contents($path, $content);
    }
}
