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
            ->setDescription('Import an xlf file')
            ->addArgument('path', InputArgument::REQUIRED, 'Path to the file to upload')
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
        if(!file_exists($path)) {
            throw new \RuntimeException(sprintf('File at %s does not exist', $path));
        }

        return simplexml_load_string (
            file_get_contents($path)
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
            ->find($id);
        if(null === $translation) {
            $translation = (new Translation())
                ->setUuid($id)
                ->setEnglish($trans->{'target'});
            $this->entityManager->persist($translation);
        }
        return $translation;
    }
}
