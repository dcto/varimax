<?php

namespace VM\Console;

/**
 * @Command
 */
class CommandMake extends Command
{

    protected $make;

    public function __construct($make)
    {
        $this->make = $make;
        parent::__construct('make:'.$this->make);
        $this->setDescription('make a '.$this->make.' class');
    }

    protected function getStub(): string
    {
        return __DIR__ . '/stubs/'.$this->make.'.stub';
    }

    protected function getDefaultNamespace(): string
    {
        return ucfirst($this->make);
    }

    protected function getPath($name)
    {
        return $this->make=='model' ? _DOC_.'/model/'. class_basename($name).'.php' : parent::getPath($name);
    }
}
