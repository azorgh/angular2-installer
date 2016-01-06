<?php

namespace PallMallShow\Installer\Console;

use ZipArchive;
use RuntimeException;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NewCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('create')
            ->setDescription('Create a new Angular2 application.')
            ->addArgument('name', InputArgument::REQUIRED);
    }

    /**
     * Execute the command.
     *
     * @param  InputInterface  $input
     * @param  OutputInterface  $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->verifyApplicationDoesntExist(
            $directory = getcwd().'/'.$input->getArgument('name'),
            $output
        );

        $this->verifyNpmIsInstalled();

        $output->writeln('<info>Crafting application...</info>');
            
        $this->extract(getcwd().'\angular.zip', $directory);


        $output->writeln('<info>Installing packages dependencies...</info>');

        $dep_process = new Process('npm install', $directory, null, null, null);
        $dep_process->run();

        $output->writeln('<info>Compiling your default app...</info>');

        $compil_process = new Process('npm run tsc', $directory, null, null, null);
        $compil_process->run();

        $output->writeln('<comment>Application ready! Build something amazing by typing `npm start` in your project folder.</comment>');
    }

    /**
     * Verify that the application does not already exist.
     *
     * @param  string  $directory
     * @return void
     */
    protected function verifyApplicationDoesntExist($directory, OutputInterface $output)
    {
        if (is_dir($directory)) {
            throw new RuntimeException('Application already exists!');
        }
    }

    /**
     * Verify npm command is available by launch npm -v command and check if line returned was like `X.X.X`.
     *
     * @return void
     */
    protected function verifyNpmIsInstalled(){
        $process = new Process('npm -v', '/', null, null, null);

        $process->run(function($type, $line){
            if(!preg_match('/^(\d+\.)?(\d+\.)?(\*|\d+)$/', trim($line))){
                throw new RuntimeException('NodeJS is probably not installed on your system !');
            }
        });
    }


    /**
     * Extract the zip file into the given directory.
     *
     * @param  string  $zipFile
     * @param  string  $directory
     * @return $this
     */
    protected function extract($zipFile, $directory)
    {
        $archive = new ZipArchive;

        $archive->open($zipFile);

        $archive->extractTo($directory);

        $archive->close();

        return $this;
    }
}
