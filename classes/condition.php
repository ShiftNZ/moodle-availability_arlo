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
 * Main class for availability_arlo.
 *
 * @package     availability_arlo
 * @author      Donald Barrett <donaldb@skills.org.nz>
 * @copyright   2022 onwards, Skills Consulting Group Ltd
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_arlo;

/**
 * Condition main class.
 */
class condition extends \core_availability\condition {
    /** @var bool $allow */
    protected $allow;

    /**
     * Constructor.
     *
     * @param \stdClass $structure Data structure from JSON decode
     */
    public function __construct($structure) {
        // It is also a good idea to check for invalid values here and
        // throw a coding_exception if the structure is wrong.
        $this->allow = $structure->allow;
    }

    public function save() {
        return (object)['allow' => $this->allow];
    }

    protected function get_enrolment_instance(int $courseid, int $userid) {
        global $DB;

        $ueselect = "SELECT ue.*";
        $uefrom = "FROM {user_enrolments} ue";
        $uejoin = "JOIN {enrol} e ON e.id = ue.enrolid";
        $uewhere = "WHERE ue.userid = :userid AND e.courseid = :courseid AND e.enrol = :enrol";
        $ueparams = ['userid' => $userid, 'courseid' => $courseid, 'enrol' => 'arlo',];
        $userenrolment = $DB->get_records_sql("$ueselect $uefrom $uejoin $uewhere", $ueparams);
        if (count($userenrolment) > 1) {
            // Alert of some stuff.
        }
        return reset($userenrolment);
    }

    /**
     * @param $registrationsourceid
     * @return bool
     */
    protected function arlo_order_has_been_paid($registrationsourceid) : bool {
        global $CFG;
        require_once("$CFG->dirroot/enrol/arlo/vendor/autoload.php");
        try {
            // Get the arlo plugin config.
            $arlopluginconfig = new \enrol_arlo\local\config\arlo_plugin_config();

            // Set the arlo api request uri.
            $arlorequesturi = new \enrol_arlo\Arlo\AuthAPI\RequestUri();
            $arlorequesturi->setHost($arlopluginconfig->get('platform'));
            $arlorequesturi->setResourcePath("registrations/$registrationsourceid");
            $arlorequesturi->addExpand('OrderLine/Order');

            // Send the request.
            $request = new \GuzzleHttp\Psr7\Request('GET', $arlorequesturi->output(true));
            $response = \enrol_arlo\local\client::get_instance()->send_request($request);

            // Process the response.
            $arloregistration = \enrol_arlo\local\response_processor::process($response);

            if (empty($arloregistration->getOrderLine()->Order->MarkedAsPaidDateTime)) {
                return false;
            } else {
                return true;
            }
        } catch (\GuzzleHttp\Exception\GuzzleException $exception) {
            // Todo: A message needs to be displayed.
        } catch (\moodle_exception $exception) {
            // Todo: A message needs to be displayed.
        } catch (\Exception $exception) {
            // Todo: A message needs to be displayed.
        }
    }

    public function is_available($not, \core_availability\info $info, $grabthelot, $userid) {
        global $DB;
        // This should be the place where things are checked.
        $course = $info->get_course();
        $userenrolment = $this->get_enrolment_instance($course->id, $userid);
        if (empty($userenrolment)) {
            // User is not enrolled via arlo.
            return true;
        }
        $arloregistration = $DB->get_record('enrol_arlo_registration', ['enrolid' => $userenrolment->enrolid, 'userid' => $userid]);
        if (empty($arloregistration)) {
            // User not part of an Arlo registration.
            return true;
        }
        return $this->arlo_order_has_been_paid($arloregistration->sourceid);
    }

    public function get_description($full, $not, \core_availability\info $info) {
        $allow = $not ? !$this->allow : $this->allow;
        // Todo: Make lang strings. In the very very rare case where the condition is NOT.
        return $allow ? get_string('requires_must', 'availability_arlo') : get_string('requires_mustnot', 'availability_arlo');
    }

    protected function get_debug_string() {
        return $this->allow ? 'YES' : 'NO';
    }
}
