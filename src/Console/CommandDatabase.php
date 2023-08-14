<?php
/**
* 
* @package app
* @author  dc.To
* @version 20230814
* @copyright ©2023 dc team all rights reserved.
*/
namespace VM\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * @Command
 */
class CommandDatabase extends \Symfony\Component\Console\Command\Command
{
    public function __construct()
    {
        parent::__construct('model:up');
        $this->addArgument('name', InputArgument::REQUIRED);
        $this->setDescription('up to database from a model');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $name =  ucfirst($input->getArgument('name'));
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Continue with this action?, that\'s will to erase the ['.$name.'] table dataset?(Y/n) ', false);

        if ($helper->ask($input, $output, $question)) {
            try{
                /**
                 * @var $model \VM\Model
                 */
                $model = make("\\App\\Model\\".$name);
                config('database.default') == 'mysql' && \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                \Schema::dropIfExists($model->getTable());
                $model::up();
                $output->writeln(sprintf('<info>%s</info>', 'up to table ['.$name.'] success!'));
            }catch(\Exception $e){
                $output->writeln(sprintf('<fg=red>%s</>',  $e->getMessage()));
                return 1;
            }

        }
        return Command::SUCCESS;
    }
}
