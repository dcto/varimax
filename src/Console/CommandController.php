<?php

namespace VM\Console;

/**
 * @Command
 */
class CommandController extends Command
{
    public function __construct()
    {
        parent::__construct('make:controller');
        $this->setDescription('make a controller class');
    }

    protected function getStub(): string
    {
        return __DIR__ . '/stubs/controller.stub';
    }

    protected function getDefaultNamespace(): string
    {
        return 'App\\Controller';
    }
}
