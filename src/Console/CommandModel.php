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
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Continue with this action?, that\'s will to erase the ['.$model->table().'] table dataset?(Y/n) ', false);
        
        /**
         * @var \Illuminate\Database\Schema\Builder
         */
        $schema = \Schema::getFacadeRoot();
        $schema->disableForeignKeyConstraints();
        $schema->blueprintResolver(function($table, \Closure $callback = null){
            return make(\Blueprint::class, compact('table', 'callback'));
        });
        
        $dataset = null;
        if ($helper->ask($input, $output, $question)) {
            try{
                if($schema->hasTable($model->table())){
                    $dataset = $model->all();
                    $schema->dropIfExists($model->table());
                }
                $schema->create($model->table(), fn($blueprint)=>$model->schema($blueprint)) ;
            
                $dataset && $model->insert($dataset->toArray());
                $model->isDirty() && $model->save();
                $output->writeln(sprintf('<info>%s</info>', 'up to table ['.$model->table().'] success!'));
            }catch(\Exception $e){
                if($dataset){
                    $datafile = runtime('schema', $model->table(), time().'.sql');
                    $dataset->each(function($item) use ($model, $datafile){
                        $item = $item->toArray();
                        $sql = sprintf('INSERT INTO `'.$model->table().'` (`%s`) VALUES (\'%s\');'.PHP_EOL, join('`,`', array_keys($item)), join('\',\'', array_values($item)) );
                        file_put_contents($datafile, $sql, FILE_APPEND);
                    });
                    $output->writeln(sprintf("<comment>The `$model->table()` dataset cache to `$datafile `</comment>",  $e->getMessage()));
                }
                $output->writeln(sprintf('<error>%s</error>',  $e));

                return 1;
            }

        }
        return Command::SUCCESS;
    }
}
