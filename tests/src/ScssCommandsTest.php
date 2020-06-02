<?php

declare(strict_types=1);

namespace TaskRunner\Scss\Tests;

use Composer\Autoload\ClassLoader;
use OpenEuropa\TaskRunner\TaskRunner;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Tests the command wrappers for Robo "assets" tasks.
 *
 * @coversDefaultClass \TaskRunner\Scss\TaskRunner\Commands\ScssCommands
 * @phpcs:disable SlevomatCodingStandard.Arrays.TrailingArrayComma
 */
final class ScssCommandsTest extends TestCase
{

    /**
     * @param string $style
     *   The CSS style to be generated.
     * @param string $expected
     *   The expected compiled CSS.
     *
     * @covers ::compileScss
     * @dataProvider compileScssDataProvider
     */
    public function testCompileScss(string $style, string $expected): void
    {
        $command = sprintf(
            'assets:compile-scss --working-dir=%s --style=%s %s %s',
            sys_get_temp_dir(),
            $style,
            __DIR__ . '/../fixtures/example.scss',
            'output.css'
        );
        $input = new StringInput($command);
        $output = new BufferedOutput();
        $runner = new TaskRunner($input, $output, $this->getClassLoader());
        $runner->run();

        $actual = file_get_contents(sys_get_temp_dir() . '/output.css');
        $this->assertEquals($expected, $actual);
    }

    /**
     * Data provider for ::testCompileScss().
     *
     * @return array[]
     *   An array of test cases, each test case an array with two elements:
     *   - A string containing the CSS style to be generated.
     *   - A string containing the expected compiled CSS.
     */
    public function compileScssDataProvider(): array
    {
        return [
            [
                'compact',
                <<<CSS
 nav ul { margin:0; }

 nav ul li { color:#111; }


CSS
            ],
            [
                'compressed',
                <<<CSS
nav ul{margin:0}nav ul li{color:#111}
CSS
            ],
            [
                'crunched',
                <<<CSS
nav ul{margin:0}nav ul li{color:#111}
CSS
            ],
            [
                'expanded',
                <<<CSS
nav ul {
  margin: 0;
}
nav ul li {
  color: #111;
}

CSS
            ],
            [
                'nested',
                <<<CSS
nav ul {
  margin: 0; }
  nav ul li {
    color: #111; }

CSS
            ],
        ];
    }

    /**
     * Returns the Composer classloader.
     *
     * @return \Composer\Autoload\ClassLoader
     */
    protected function getClassLoader(): ClassLoader
    {
        return require __DIR__ . '/../../vendor/autoload.php';
    }
}
