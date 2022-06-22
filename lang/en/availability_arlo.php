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
 * English language strings for availability_arlo.
 *
 * @package     availability_arlo
 * @author      Donald Barrett <donaldb@skills.org.nz>
 * @copyright   2022 onwards, Skills Consulting Group Ltd
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Default langstring.
$string['pluginname'] = 'Restriction by Arlo order payment status';
$string['privacy:metadata'] = 'The Restriction by Arlo order payment status does not store any personal data.';
$string['description'] = 'Prevent access until the Order in Arlo has been paid for.';
$string['title'] = 'Order paid for in Arlo';
$string['description_allow'] = '';
$string['requires_must'] = 'Arlo Order has been paid for';
$string['requires_must_withdetail'] = 'Arlo Order has been paid for (Registration ID: {$a})';
$string['requires_mustnot'] = 'Arlo Order has NOT been paid for';
$string['requires_mustnot_withdetail'] = 'Arlo Order has NOT been paid for (Registration ID: {$a})';
