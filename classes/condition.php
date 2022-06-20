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
        // Check the structure.
        $this->allow = $structure->allow;

        // It is also a good idea to check for invalid values here and
        // throw a coding_exception if the structure is wrong.
    }

    public function save() {
        return (object)['allow' => true];
    }

    public function is_available($not, \core_availability\info $info, $grabthelot, $userid) {
        // This should be the place where things are checked.
        return true;
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
