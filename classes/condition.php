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

use enrol_arlo\Arlo\AuthAPI\Exception\XMLDeserializerException;

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
        //print_object($structure);die;
        print_object($structure);
        $this->allow = $structure->allow;
    }

    /**
     * Saves tree data back to structure object.
     *
     * @return object \stdClass object ready to be made into JSON
     */
    public function save() {
        return (object)['allow' => $this->allow, 'type' => 'arlo', 'foo' => 'bar'];
    }

    /**
     * Get a users enrolment instance for their course.
     *
     * @param int $courseid The id of the course to get the enrolment instance for
     * @param int $userid The user id to get the enrolment instance for
     * @return false|mixed False if no user enrolments found or the specific user enrolment record
     * @throws \dml_exception A DML specific exception is thrown for any errors
     */
    protected function get_enrolment_instance(int $courseid, int $userid) {
        global $DB;

        $ueselect = "SELECT ue.*";
        $uefrom = "FROM {user_enrolments} ue";
        $uejoin = "JOIN {enrol} e ON e.id = ue.enrolid";
        $uewhere = "WHERE ue.userid = :userid AND e.courseid = :courseid AND e.enrol = :enrol";
        $ueparams = ['userid' => $userid, 'courseid' => $courseid, 'enrol' => 'arlo'];
        $userenrolment = $DB->get_records_sql("$ueselect $uefrom $uejoin $uewhere", $ueparams);
        if (count($userenrolment) > 1) {
            // Alert of some stuff.
            debugging("Somehow the user with id '$userid' is in a bunch of enrolment methods. Not sure this should be possible.");
        }
        return reset($userenrolment);
    }

    /**
     * Check if the arlo order has been paid.
     *
     * @param int $registrationsourceid The Arlo registration id to check
     * @return bool True if paid. False if not paid or (at this stage) an exception happened
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
            debugging($exception->getMessage());
            return false;
        } catch (\moodle_exception $exception) {
            if ($exception->getMessage() == 'error/httpstatus:404') {
                // No order, no cry.
                return true;
            }
            debugging($exception->getMessage());
            return false;
        } catch (XMLDeserializerException $exception) {
            // This should never happen because this is already checked in frontend class "allow_add".
            debugging($exception->getMessage());
            return false;
        } catch (\Exception $exception) {
            // Some other exception happened
            debugging($exception->getMessage());
            return false;
        }
    }

    /**
     * This is the thing that checks if an arlo order has been paid for.
     * It also checks if the person has an associated arlo registration.
     * This is available to all of those that do not have an arlo registration.
     *
     * @param bool $not Set true if we are inverting the condition
     * @param \core_availability\info $info Item we're checking
     * @param bool $grabthelot Performance hint: if true, caches information
     *   required for all course-modules, to make the front page and similar
     *   pages work more quickly (works only for current user)
     * @param int $userid User ID to check availability for
     * @return bool True if available
     * @throws \dml_exception
     */
    public function is_available($not, \core_availability\info $info, $grabthelot, $userid) {
        global $DB;
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

    /**
     * Obtains a string describing this restriction (whether or not
     * it actually applies). Used to obtain information that is displayed to
     * students if the activity is not available to them, and for staff to see
     * what conditions are.
     *
     * The $full parameter can be used to distinguish between 'staff' cases
     * (when displaying all information about the activity) and 'student' cases
     * (when displaying only conditions they don't meet).
     *
     * If implementations require a course or modinfo, they should use
     * the get methods in $info.
     *
     * The special string <AVAILABILITY_CMNAME_123/> can be returned, where
     * 123 is any number. It will be replaced with the correctly-formatted
     * name for that activity.
     *
     * @param bool $full Set true if this is the 'full information' view
     * @param bool $not Set true if we are inverting the condition
     * @param \core_availability\info $info Item we're checking
     * @return \lang_string|string Information string (for admin) about all restrictions on this item
     * @throws \coding_exception When a coding exception occurs 🤣.
     */
    public function get_description($full, $not, \core_availability\info $info) {
        $allow = $not ? !$this->allow : $this->allow;
        // Todo: Make lang strings. In the very very rare case where the condition is NOT.
        return $allow ? get_string('requires_must', 'availability_arlo') : get_string('requires_mustnot', 'availability_arlo');
    }

    /**
     * Obtains a representation of the options of this condition as a string, for debugging.
     *
     * @return string Text representation of parameters.
     */
    protected function get_debug_string() {
        // NGL, not actually sure what needs to go here.
        return $this->allow ? 'y' : 'n';
    }
}
