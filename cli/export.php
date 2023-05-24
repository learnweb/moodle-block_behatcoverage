<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This script exports the accumulated coverage files as a xml.
 *
 * @package    local_behatcoverage
 * @copyright  2023 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', 1);

// Moodle overwrites cwd with __DIR__, so we'll need to save it.
$cwd = getcwd();

global $CFG;
require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');

// Now get cli options.
list($options, $unrecognized) = cli_get_params(array(
        'output' => 'coverage-behat.xml',
        'help' => false,
), array('h' => 'help', 'o' => 'output'));

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help = <<<EOL
Export the accumulated coverage files as an xml.

Options:
--output=STRING   Path to store the coverage report. Default is coverage-behat.xml
                  in the current working directory.       
-h, --help        Print out this help.

Example:
\$sudo -u www-data /usr/bin/php local/behatcoverage/cli/export.php --output=coveragefile.xml

EOL;

    echo $help;
    die();
}

chdir($cwd);

$file = $CFG->behat_dataroot . '/behat.cov';

if (!file_exists($file)) {
    mtrace('Error: Coverage does not exist');
    die(1);
}

require $CFG->dirroot . '/vendor/autoload.php';

$coverage = include $file;

$clover = new \SebastianBergmann\CodeCoverage\Report\Clover();
$result = file_put_contents($options['output'], $clover->process($coverage));

if ($result === false) {
    mtrace('Error: Could not write to output');
    die(1);
}
