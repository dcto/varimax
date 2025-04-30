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
        $name = \Str::studly($input->getArgument('name'));
        $name = strstr($name, '/') ?   str_replace('/', '\\', $name) : 'App\\Model\\'.$name;
        $model = sprintf("App\\Model\\%s", $name)::getModel();
        $table = $model->getConnection()->getTablePrefix().$model->table();
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Continue it\'s?, do you wanna backup the ['.$model->table().'] table dataset?(Y/n) ', true);
        
        /**
         * @var \Illuminate\Database\Schema\Builder
         */
        $schema = \Schema::getFacadeRoot();
        $schema->disableForeignKeyConstraints();
        $schema->blueprintResolver(function($table, \Closure $callback = null){
            return make(\Blueprint::class, compact('table', 'callback'));
        });
        
        $dataset = null;
        
        try{
            if($schema->hasTable($model->table())){
                if ($helper->ask($input, $output, $question)) {
                    $dataset = $model->all();
                    $schema->rename($model->table(), $newTable = $model->table().'_'.date('ymdHis'));
                    $output->writeln(sprintf("<comment>Backup table [%s] to: [%s]</comment>", $model->table(), $newTable));
                }
                $schema->dropIfExists($model->table());
            }
            
            $callback = null;
            $schema->create($model->table(), function($blueprint) use($model, &$callback){
                $callback = $model->schema($blueprint);
            });

            $model->isDirty() && $model->save();
            $callback instanceof \Closure && $callback();
            
            $output->writeln(sprintf('<info>up to table schema [%s] success!</info>', $table));
        }catch(\Exception $e){
            if($dataset){
                $datafile = runtime('schema', $model->table(), time().'.sql');
                $dataset->each(function($item) use ($table, $datafile){
                    $item = $item->toArray();
                    $sql = sprintf('INSERT INTO `%s` (`%s`) VALUES (\'%s\');'.PHP_EOL, $table, join('`,`', array_keys($item)), join('\',\'', array_values($item)) );
                    file_put_contents($datafile, $sql, FILE_APPEND);
                });
                $output->writeln(sprintf("<comment>The [%s] dataset cache to: %s</comment>", $table, $datafile));
            }
            $output->writeln(sprintf('<error>%s</error>',  $e->getMessage()));

            return 1;
        }
        
        return Command::SUCCESS;
    }
}
