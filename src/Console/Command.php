<?php
/**
* 
* @package console
* @author  dc.To
* @version 20230922
* @copyright ©2023 dc team all rights reserved.
*/
namespace VM\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Command extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;


    static public function register(){
        $application = new \Symfony\Component\Console\Application('VARIMAX', 'v3.0');
        $application->add(new CommandModel);
        $application->add(new CommandMake('controller'));
        $application->add(new CommandMake('model'));
        $application->add(new CommandMake('pipeline'));
        $application->add(new CommandMake('service'));
        $application->run();
        
    }


    public function configure()
    {        
        foreach ($this->getArguments() as $argument) {
            $this->addArgument(...$argument);
        }

        foreach ($this->getOptions() as $option) {
            $this->addOption(...$option);
        }
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        
        $this->input = $input;
        $this->output = $output;

        $name = $this->qualifyClass($this->getNameInput());
        $path = $this->getPath($name);
        // First we will check to see if the class already exists. If it does, we don't want
        // to create the class and overwrite the user's code. So, we will bail out so the
        // code is untouched. Otherwise, we will continue generating this class' files.
        if (($input->getOption('force') === false) && $this->alreadyExists($this->getNameInput())) {
            $output->writeln(sprintf('<error>%s</error>', $path . ' already exists!'));
            return 0;
        }
        // Next, we will generate the path to the location where this class' file should get
        // written. Then, we will build the class and make the proper replacements on the
        // stub files so that it gets the correctly formatted namespace and class name.
        $this->makeDirectory($path);

        file_put_contents($path, $this->buildClass($name));

        $output->writeln(sprintf('<info>%s</info>', $name . ' created successfully.'));

        $this->openWithIde($path);

        return 0;
    }

    /**
     * Parse the class name and format according to the root namespace.
     *
     * @param string $name
     * @return string
     */
    protected function qualifyClass($name)
    {
        $name = ltrim($name, '\\/');

        $name = str_replace('/', '\\', $name);

        $namespace = $this->input->getOption('namespace');
        if (empty($namespace)) {
            $namespace = $this->getDefaultNamespace();
        }

        return $namespace . '\\' . ucfirst($name);
    }

    /**
     * Determine if the class already exists.
     *
     * @param string $rawName
     * @return bool
     */
    protected function alreadyExists($rawName)
    {
        return is_file($this->getPath($this->qualifyClass($rawName)));
    }

    /**
     * Get the destination class path.
     *
     * @param string $name
     * @return string
     */
    protected function getPath($name)
    {
        return _DOC_._DS_.$this->input->getOption('app')._DS_.str_replace('\\', '/', $name).'.php';
    }

    /**
     * Build the directory for the class if necessary.
     *
     * @param string $path
     * @return string
     */
    protected function makeDirectory($path)
    {
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        return $path;
    }

    /**
     * Build the class with the given name.
     *
     * @param string $name
     * @return string
     */
    protected function buildClass($name)
    {
        $stub = file_get_contents($this->getStub());

        return $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param string $stub
     * @param string $name
     * @return $this
     */
    protected function replaceNamespace(&$stub, $name)
    {
        $stub = str_replace(
            ['%NAMESPACE%'],
            [$this->getNamespace($name)],
            $stub
        );

        return $this;
    }

    /**
     * Get the full namespace for a given class, without the class name.
     *
     * @param string $name
     * @return string
     */
    protected function getNamespace($name)
    {
        return trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param string $stub
     * @param string $name
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        $class = str_replace($this->getNamespace($name) . '\\', '', $name);

        return str_replace('%CLASS%', ucfirst($class), $stub);
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        return trim($this->input->getArgument('name'));
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the class'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['app', 'a', InputOption::VALUE_OPTIONAL, 'The application', 'app'],
            ['force', 'f', InputOption::VALUE_NONE, 'Whether force to rewrite.'],
            ['namespace', 'N', InputOption::VALUE_OPTIONAL, 'The namespace for class.', null],
        ];
    }

    

    /**
     * Get the stub file for the generator.
     */
    abstract protected function getStub(): string;

    /**
     * Get the default namespace for the class.
     */
    abstract protected function getDefaultNamespace(): string;

    /**
     * Get the editor file opener URL by its name.
     */
    protected function getEditorUrl(string $ide): string
    {
        switch ($ide) {
            case 'sublime':
                return 'subl://open?url=file://%s';
            case 'textmate':
                return 'txmt://open?url=file://%s';
            case 'emacs':
                return 'emacs://open?url=file://%s';
            case 'macvim':
                return 'mvim://open/?url=file://%s';
            case 'phpstorm':
                return 'phpstorm://open?file=%s';
            case 'idea':
                return 'idea://open?file=%s';
            case 'vscode':
                return 'vscode://file/%s';
            case 'vscode-insiders':
                return 'vscode-insiders://file/%s';
            case 'vscode-remote':
                return 'vscode://vscode-remote/%s';
            case 'vscode-insiders-remote':
                return 'vscode-insiders://vscode-remote/%s';
            case 'atom':
                return 'atom://core/open/file?filename=%s';
            case 'nova':
                return 'nova://core/open/file?filename=%s';
            case 'netbeans':
                return 'netbeans://open/?f=%s';
            case 'xdebug':
                return 'xdebug://%s';
            default:
                return '';
        }
    }

    /**
     * Open resulted file path with the configured IDE.
     */
    protected function openWithIde(string $path): void
    {
        $openEditorUrl = $this->getEditorUrl(getenv('IDE'));

        if (! $openEditorUrl) {
            return;
        }

        $url = sprintf($openEditorUrl, $path);
        switch (PHP_OS_FAMILY) {
            case 'Windows':
                exec('explorer ' . $url);
                break;
            case 'Linux':
                exec('xdg-open ' . $url);
                break;
            case 'Darwin':
                exec('open ' . $url);
                break;
        }
    }
    
}
