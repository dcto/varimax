<?php

namespace VM\Console;

/**
 * @Command
 */
class CommandModel extends Command
{
    public function __construct()
    {
        parent::__construct('make:model');
        $this->setDescription('make a model class');
    }

    protected function getStub(): string
    {
        return __DIR__ . '/stubs/model.stub';
    }

    protected function getDefaultNamespace(): string
    {
        return 'App\\Model';
    }

    protected function getPath($name)
    {
        return _DOC_.'/model/'. class_basename($name).'.php';
    }
}
