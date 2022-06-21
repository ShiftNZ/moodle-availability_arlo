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
 * Front-end class for availability_arlo.
 *
 * @package     availability_arlo
 * @author      Donald Barrett <donaldb@skills.org.nz>
 * @copyright   2022 onwards, Skills Consulting Group Ltd
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_arlo;

/**
 * Front-end class.
 */
class frontend extends \core_availability\frontend {
    /**
     * Gets additional parameters for the plugin's initInner function.
     *
     * This is just the same in the parent class. If we do need to do some stuff, we'll
     * chuck it in here.
     *
     * @param \stdClass $course Course object
     * @param \cm_info|null $cm Course-module currently being edited (null if none)
     * @param \section_info|null $section Section currently being edited (null if none)
     * @return array Array of parameters for the JavaScript function
     */
    protected function get_javascript_init_params($course, \cm_info $cm = null, \section_info $section = null) {
        return [];
    }

    /**
     * Do all the things to ensure that the access restriction can be applied.
     * This will only work on page load.
     *
     * @param \stdClass $course Course object
     * @param \cm_info|null $cm Course-module currently being edited (null if none)
     * @param \section_info|null $section Section currently being edited (null if none)
     * @return bool True if adding this restriction is allowed
     * @throws \moodle_exception When a moodle_exception occurs
     */
    protected function allow_add($course, \cm_info $cm = null, \section_info $section = null) {
        // These classes need to exist.
        $orderclass = 'enrol_arlo\Arlo\AuthAPI\Resource\Order';
        $orderlineclass = 'enrol_arlo\Arlo\AuthAPI\Resource\OrderLine';
        if (!(class_exists($orderclass) && class_exists($orderlineclass))) {
            debugging("The enrol_arlo plugin is missing the classes for Order '$orderclass' and OrderLine '$orderlineclass'");
            return false;
        }
        if (is_null($cm)) {
            // New course module.
            return true;
        }
        // Existing course module.
        $context = $cm->context;
        $coursemodule = $cm->get_modinfo()->get_cm($context->instanceid);
        $coursemoduleavailability = json_decode($coursemodule->availability);
        if (empty($coursemoduleavailability)) {
            // Has no restrictions.
            return true;
        }
        if (!isset($coursemoduleavailability->c)) {
            return true;
        }
        if (!is_array($coursemoduleavailability->c)) {
            return true;
        }
        foreach ($coursemoduleavailability->c as $item) {
            if (isset($item->type) && $item->type === 'arlo') {
                return false;
            }
        }
        return true;
    }
}
