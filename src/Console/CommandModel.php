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
        /**
         * @var \VM\Model
         */
        $model = sprintf("App\\Model\\%s", \Str::studly($input->getArgument('name')))::getModel();
        $table = \DB::getTablePrefix().$model->table();
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Continue with this action?, that\'s will to erase the ['.$table.'] table dataset?(Y/n) ', false);

        if ($helper->ask($input, $output, $question)) {
            try{
                if(\Schema::hasTable($model->table())){
                    $dataset = $model->all();
                        config('database.default') == 'mysql' && \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                        \Schema::dropIfExists($model->table());
                        \Schema::create($model->table(),fn($table)=>$model->schema($table));
                        try{
                            throw new \ErrorException("error dddd");
                            $model->insert($dataset->toArray());
                        }catch(\Exception $e){
                            $sqlStatements = null;
                            $dataset->each(function($item) use (&$sqlStatements, $table){
                                $item = $item->toArray();
                                $sqlStatements .= sprintf('INSERT INTO `'.$table.'` (`%s`) VALUES (\'%s\');'.PHP_EOL, join('`,`', array_keys($item)), join('\',\'', array_values($item)) );
                            });

                            if($sqlStatements){
                                file_put_contents($cacheFile = runtime('schema', $table, time().'.sql'), $sqlStatements);
                                $output->writeln(sprintf("<comment>The `$table` dataset cache to `$cacheFile`</comment>",  $e->getMessage()));
                            }
                            throw $e;
                        }
                }else{
                    \Schema::create($model->table(),fn($table)=>$model->schema($table));
                }
                $output->writeln(sprintf('<info>%s</info>', 'up to table ['.$table.'] success!'));
            }catch(\Exception $e){
                $output->writeln(sprintf('<error>%s</error>',  $e->getMessage()));
                return 1;
            }

        }
        return Command::SUCCESS;
    }
}
