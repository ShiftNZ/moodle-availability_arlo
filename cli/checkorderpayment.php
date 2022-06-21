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
 * CLI script to check Arlo order payment status for availability_arlo.
 *
 * @package     availability_arlo
 * @author      Donald Barrett <donaldb@skills.org.nz>
 * @copyright   2022 onwards, Skills Consulting Group Ltd
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This is a CLI script.
define('CLI_SCRIPT', true);

// Config file.
require_once(dirname(__FILE__, 5) . '/config.php');

global $DB;

// Other things to require.
require_once("{$CFG->libdir}/clilib.php");
require_once("{$CFG->libdir}/cronlib.php");

// We may need a lot of memory here.
@set_time_limit(0);
raise_memory_limit(MEMORY_HUGE);

// CLI options.
list($options, $unrecognized) = cli_get_params(
// Long names.
    [
        'help' => false,
        'no-verbose' => false,
        'no-debugging' => false,
        'print-logo' => false
    ],
    // Short names.
    [
        'h' => 'help',
        'nv' => 'no-verbose',
        'nd' => 'no-debugging',
        'pl' => 'print-logo'
    ]
);

if (function_exists('cli_logo') && $options['print-logo']) {
    // Show a logo.
    cli_logo();
    echo PHP_EOL;
}

if ($unrecognized) {
    $unrecognized = implode("\n ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

// Show help.
if ($options['help']) {
    $help =
        "CLI script to check Arlo order payment status.

Please note you must execute this script with the same uid as apache!

Options:
-nv, --no-verbose       Disables output to the console.
-h, --help              Print out this help.
-nd, --no-debugging     Disables debug messages.
-pl, --print-logo       Prints a cool CLI logo if available.

Example:
Run script with default parameters  - \$sudo -u www-data /usr/bin/php checkorderpayment.php\n
";
    echo $help;
    die;
}

// Set debugging.
if (!$options['no-debugging']) {
    @error_reporting(E_ALL | E_STRICT);
    @ini_set('display_errors', '1');
}

// Start output log.
$trace = new text_progress_trace();
$trace->output(get_string('pluginname', 'availability_arlo') . ' CLI script to check Arlo order payment status.');

// Say some stuff like debugging is whatever.
if (!$options['no-debugging']) {
    $trace->output("Debugging is enabled.");
} else {
    $trace->output("Debugging has been disabled.");
}

// Set verbosity and output stuff.
if ($options['no-verbose']) {
    $trace->output("Verbose output has been disabled.\n");
    $trace = new null_progress_trace();
} else {
    $trace->output("Verbose output is enabled.\n");
}

// Start timing.
$timenow = time();
$trace->output("Server Time: " . date('r', $timenow) . "\n");
$starttime = microtime();

// Get the arlo users.
$registrations = $DB->get_records('enrol_arlo_registration');

$arlopluginconfig = new \enrol_arlo\local\config\arlo_plugin_config();
$arloclient = \enrol_arlo\local\client::get_instance();
$arlorequesturi = new \enrol_arlo\Arlo\AuthAPI\RequestUri();
$arlorequesturi->setHost($arlopluginconfig->get('platform'));

foreach ($registrations as $registration) {
    $arlorequesturi->setResourcePath("registrations/{$registration->sourceid}");
    $arlorequesturi->addExpand('OrderLine/Order');
    $request = new \GuzzleHttp\Psr7\Request('GET', $arlorequesturi->output(true));
    $response = $arloclient->send_request($request);
    try {
        $arloregistration = \enrol_arlo\local\response_processor::process($response);
        $order = $arloregistration->getOrderLine()->Order;
        $trace->output($order->OrderID.' '.($order->MarkedAsPaidDateTime ?? 'not paid'));
    } catch (moodle_exception $exception) {
        // Ignore. This user must not have stuff.
        $trace->output("Exception happened - {$exception->getMessage()}");
    }
}

// Finish timing.
$difftime = microtime_diff($starttime, microtime());
$trace->output("\nScript execution took {$difftime} seconds\n");
