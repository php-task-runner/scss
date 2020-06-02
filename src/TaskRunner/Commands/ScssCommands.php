<?php

declare(strict_types=1);

namespace TaskRunner\Scss\TaskRunner\Commands;

use Consolidation\AnnotatedCommand\CommandData;
use OpenEuropa\TaskRunner\Commands\AbstractCommands;
use Robo\Collection\CollectionBuilder;
use Robo\Task\Assets\loadTasks;
use Symfony\Component\Console\Input\InputOption;

/**
 * Command wrapper for the "taskScss" task that is included in Robo.
 *
 * @see \Robo\Task\Assets\loadTasks
 */
class ScssCommands extends AbstractCommands
{
    use loadTasks;

    /**
     * List of formatters that is offered by the ScssPhp compiler.
     *
     * @see \ScssPhp\ScssPhp\Formatter
     */
    protected const SCSS_FORMATTERS = ['compact', 'compressed', 'crunched', 'expanded', 'nested'];

    /**
     * Compiles SCSS.
     *
     * @command assets:compile-scss
     *
     * @param string $input_file The path to the SCSS file to process
     * @param string $output_file The path where to store the compiled CSS file
     * @option style Set the output format (compact, compressed, crunched, expanded, or nested)
     * @option import-dir Set an import path
     *
     * @param array $options
     *
     * @return \Robo\Collection\CollectionBuilder
     */
    public function compileScss(string $input_file, string $output_file, array $options = [
        'style' => InputOption::VALUE_REQUIRED,
        'import-dir' => [],
    ]): CollectionBuilder
    {
        $scss = $this->taskScss([$input_file => $output_file]);

        if ($options['style']) {
            $scss->setFormatter('ScssPhp\\ScssPhp\\Formatter\\' . ucfirst($options['style']));
        }

        foreach ($options['import-dir'] as $import_dir) {
            $scss->addImportPath($import_dir);
        }

        return $this->collectionBuilder()->addTask($scss);
    }

    /**
     * @hook pre-validate assets:compile-scss
     */
    public function preValidateCompileScss(CommandData $commandData): void
    {
        $input = $commandData->input();
        $style = $input->getOption('style');
        if ($style) {
            // Ensure case insensitive matching for the style option.
            $input->setOption('style', strtolower($style));
        }
    }

    /**
     * @hook validate assets:compile-scss
     */
    public function validateCompileScss(CommandData $commandData): void
    {
        $input = $commandData->input();
        $input_file = $input->getArgument('input_file');
        if (!is_file($input_file) || !is_readable($input_file)) {
            throw new \Exception(sprintf('Input file "%s" does not exist or is not readable', $input_file));
        }

        $style = $input->getOption('style');
        if ($style && !in_array($style, self::SCSS_FORMATTERS)) {
            throw new \Exception(sprintf('Unknown style "%s"', $style));
        }

        $import_dirs = $input->getOption('import-dir');
        foreach ($import_dirs as $import_dir) {
            if (!is_dir($import_dir) || !is_readable($import_dir)) {
                throw new \Exception(sprintf('Import dir "%s" does not exist or is not readable', $import_dir));
            }
        }
    }

}
