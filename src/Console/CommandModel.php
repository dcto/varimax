<?php
/**
* 
* @package app
* @author  dc.To
* @version 20230814
* @copyright ©2023 dc team all rights reserved.
*/
namespace VM\Console;

use Illuminate\Database\QueryException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * @Command
 */
class CommandModel extends \Symfony\Component\Console\Command\Command
{
    public function __construct()
    {
        parent::__construct('model:schema');
        $this->addArgument('name', InputArgument::REQUIRED);
        $this->setDescription('up to database from a model');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $name =  "\App\\Model\\". ucfirst($input->getArgument('name'));
        /**
         * @var $model \VM\Model
         */
        $model =  new $name();
        $table = \DB::getTablePrefix().$model->table();
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Continue with this action?, that\'s will to erase the ['.$table.'] table dataset?(Y/n) ', false);

        if ($helper->ask($input, $output, $question)) {
            try{
                if(\Schema::hasTable($model->table())){
                    $dataset = $model->all();
                    try{
                        config('database.default') == 'mysql' && \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                        \Schema::dropIfExists($model->table());
                        \Schema::create($model->table(),fn($table)=>$model->schema($table));
                        $model->insert($dataset->toArray());
                    }catch(\Exception $e){
                        $sqlStatement = '';
                        $dataset->each(function($item) use (&$sqlStatement){
                         $item = $item->toArray();
                            $sqlStatement .= sprintf('INSERT INTO `'.$table.'` (`%s`) VALUES (\'%s\');', join('`,`', array_keys($item)), join('\',\'', array_values($item)) );
                        });
                        file_put_contents(runtime($table.time().'.sql'), $sqlStatement);
                    }
                }else{
                    \Schema::create($model->table(),fn($table)=>$model->schema($table));
                }
                $output->writeln(sprintf('<info>%s</info>', 'up to table ['.$table.'] success!'));
            }catch(\Exception $e){
                $output->writeln(sprintf('<fg=red>%s</>',  $e->getMessage()));
                return 1;
            }

        }
        return Command::SUCCESS;
    }
}
