<?php


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;
use Symfony\Component\HttpClient\HttpClient;

require __DIR__ . '/../vendor/autoload.php';


(new SingleCommandApplication())
    ->setName('file:download')
    ->addArgument('url', InputArgument::OPTIONAL, 'File url ', "https://proof.ovh.net/files/100Mb.dat")
    ->setCode(function (InputInterface $input, OutputInterface $output): int {
        $progressBar = new ProgressBar($output, 100);
        $url = $input->getArgument('url');
        $httpClient = HttpClient::create();
        $response = $httpClient->request('GET', $url, [
            'on_progress' => function (int $dlNow, int $dlSize, array $info) use ($progressBar) {
                if ($dlSize && $dlNow > 0 ){
                    $progressBar->setProgress(intval($dlNow*100 / $dlSize));
                    if ($dlNow == $dlSize){
                        $progressBar->finish();
                    }
                }
            }
        ]);
        $filHandler = fopen('./file.dat' , 'w');
        foreach ($httpClient->stream($response) as $chunk) {
            fwrite($filHandler, $chunk->getContent());
        }

        return Command::SUCCESS;
    })
    ->run();