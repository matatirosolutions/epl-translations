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
            ->addArgument('locale', InputArgument::REQUIRED,
                'The language identifier which should be exported as the target language, i.e. en_NZ or se')
            ->addArgument('path', InputArgument::REQUIRED,
                'File, including full path to export to. Existing files will be overwritten.')
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
            $translations = $this->entityManager->getRepository(Translation::class)
                ->findAll();
            $this->writeToFile(
                $input->getArgument('path'),
                $input->getArgument('locale'),
                $this->convertFromDB($input->getArgument('locale'), $translations)
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
    private function convertFromDB(string $locale, $translations): SimpleXMLElement
    {
        $xml = $this->createEmptyXliffFile($locale);

        foreach($translations as $translation) {
            $trans = $xml->{'file'}->{'body'}->addChild('trans-unit');
            $trans->addAttribute('id', $this->cleanContent($translation->getStringId()));
            $trans->addAttribute('resname', $this->cleanContent($translation->getStringId()));
            $trans->addChild('source', $this->cleanContent($translation->getFrench()));

            switch($locale){
                case 'en_NZ':
                    $trans->addChild('target', $this->cleanContent($translation->getEnglish()));
                    break;
                case 'se':
                    $trans->addChild('target', $this->cleanContent($translation->getSwedish()));
                    break;
            }
        }

        return $xml;
    }

    private function createEmptyXliffFile($language): SimpleXMLElement
    {
        $xml = new SimpleXMLElement('<xliff xmlns="urn:oasis:names:tc:xliff:document:1.2" version="1.2"/>');
        $file = $xml->addChild('file');
        $file->addAttribute('source-language', 'fr');
        $file->addAttribute('target-language', $language);
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

    private function writeToFile(string $path, string $locale, SimpleXMLElement $xml): void
    {
        $file = $path . (substr($path, -1) === DIRECTORY_SEPARATOR ? '' : DIRECTORY_SEPARATOR) .
            'translations' . DIRECTORY_SEPARATOR .
            'messages+intl-icu.' . $locale . '.xlf';

        $dom = dom_import_simplexml($xml)->ownerDocument;
        $dom->formatOutput = true;

        file_put_contents($file, $dom->saveXML());
    }
}
