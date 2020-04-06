<?php

namespace App\Command;

use App\Entity\Translation;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use SimpleXMLElement;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TranslationExportCommand extends Command
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    protected static $defaultName = 'translation:export:strings';

    /**
     * TranslationExportCommand constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Export translations')
            ->addArgument('path', InputArgument::OPTIONAL, 'File, including full path to export to. Existing files will be overwritten.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $translations = $this->entityManager->getRepository(Translation::class)
                ->findAll();
            $this->writeToFile(
                $input->getArgument('path'),
                $this->convertFromDB($translations)
            );
        } catch (RuntimeException $exception) {
            $io->error($exception->getMessage());
        }

        $io->write('Done!');
        return 0;
    }

    /**
     * @param Translation[]|object[] $translations
     * @return SimpleXMLElement
     */
    private function convertFromDB($translations): SimpleXMLElement
    {
        $xml = $this->createEmptyXliffFile();

        foreach($translations as $translation) {
            $trans = $xml->{'file'}->{'body'}->addChild('trans-unit');
            $trans->addAttribute('id', $this->cleanContent($translation->getStringId()));
            $trans->addAttribute('resname', $this->cleanContent($translation->getStringId()));
            $trans->addChild('source', $this->cleanContent($translation->getFrench()));
            $trans->addChild('target', $this->cleanContent($translation->getEnglish()));
        }

        return $xml;
    }

    private function createEmptyXliffFile(): SimpleXMLElement
    {
        $xml = new SimpleXMLElement('<xliff xmlns="urn:oasis:names:tc:xliff:document:1.2" version="1.2"/>');
        $file = $xml->addChild('file');
        $file->addAttribute('source-language', 'fr');
        $file->addAttribute('target-language', 'en');
        $file->addAttribute('datatype', 'plaintext');
        $file->addAttribute('original', 'file.ext');
        $file->addChild('body');

        return $xml;
    }

    private function cleanContent($string)
    {
        $clean = trim(
            htmlspecialchars($string, ENT_QUOTES)
        );

        if(strpos($clean, '<') !== false) {
            return sprintf('<![CDATA[%s]]>', $clean);
        }

        return $clean;
    }

    private function writeToFile(string $path, SimpleXMLElement $xml): void
    {
        file_put_contents($path, $xml->asXML());
    }
}
