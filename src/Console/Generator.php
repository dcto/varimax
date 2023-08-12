<?php
/**
* 
* @package varimax
* @author  dc.To
* @version 20230806
* @copyright ©2023 dc team all rights reserved.
*/
namespace VM\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Generator
 * @package VM\Console
 */
class Generator extends Command {

    

    protected function toCamelCase($str, $toOne = false)
    {
        $array = explode('_', $str);
        $result = $toOne ? ucfirst($array[0]) : $array[0];
        $len = count($array);
        if ($len > 1) {
            for ($i = 1; $i < $len; $i++) {
                $result .= ucfirst($array[$i]);
            }
        }
        return $result;
    }
}