<?php

namespace App\Command;

use App\Entity\Translation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TranslationImportCommand extends Command
{

    /** @var EntityManagerInterface */
    protected $entityManager;

    protected static $defaultName = 'translation:import';

    /**
     * TranslationImportCommand constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription(
                'Import an xlf file - this will update the French to match existing translations '.
                '(without changing the English or Swedish if they exist), plus create any new translations.')
            ->addArgument('path', InputArgument::REQUIRED,
                'Path to the file folder containing messages+intl-icu.fr.xlf')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->parseFile(
                $io,
                $this->loadXLFFile($input->getArgument('path'))
            );
        } catch (\RuntimeException $except) {
            $io->error($except->getMessage());
            return -1;
        }


        return 0;
    }

    private function parseFile(SymfonyStyle $io, \SimpleXMLElement $xml)
    {
        foreach ($xml->file->body->{'trans-unit'} as $trans) {
            $id = $this->getTranslationId($trans);
            $translation = $this->loadOrCreateTranslation($id, $trans);
            $translation->setFrench($trans->{'source'});
            $this->entityManager->flush();

            $io->writeln(
                sprintf('Wrote translation %s to FileMaker', $id)
            );
        }
    }

    private function loadXLFFile(string $path)
    {
        $file = $path . (substr($path, -1) === DIRECTORY_SEPARATOR ? '' : DIRECTORY_SEPARATOR) .
            'translations' . DIRECTORY_SEPARATOR .
            'messages+intl-icu.fr.xlf';

        if(!file_exists($file)) {
            throw new \RuntimeException(
                sprintf('File messages+intl-icu.fr.xlf does not exist in the translations folder at path %s', $path)
            );
        }

        return simplexml_load_string (
            file_get_contents($file)
        );
    }

    private function getTranslationId(\SimpleXMLElement $trans): string
    {
        foreach($trans->attributes() as $key => $value) {
            if('id' === $key) {
                return (string)$value;
            }
        }

        throw new \RuntimeException('Missing ID in translation key');
    }

    private function loadOrCreateTranslation(string $id, \SimpleXMLElement $trans): Translation
    {
        $translation = $this->entityManager->getRepository(Translation::class)
            ->findOneBy(['stringId' => $id]);
        if(null === $translation) {
            $translation = (new Translation())
                ->setStringId($id)
                ->setEnglish($trans->{'target'})
                ->setSwedish($trans->{'target'});
            $this->entityManager->persist($translation);
        }
        return $translation;
    }
}
