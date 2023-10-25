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
 * certprint handler event.
 *
 * @package     local_recompletion
 * @author      Dan Marsden
 * @copyright   Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_recompletion\activities;

use lang_string;

/**
 * certprint handler event.
 *
 * @package    local_recompletion
 * @author     Dan Marsden
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class certprint {
    /**
     * Add params to form.
     * @param moodleform $mform
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function editingform($mform) : void {
        $config = get_config('local_recompletion');

        $cba = array();
        $cba[] = $mform->createElement('radio', 'certprint', '',
            get_string('donothing', 'local_recompletion'), LOCAL_RECOMPLETION_NOTHING);
        $cba[] = $mform->createElement('radio', 'certprint', '',
            get_string('archive', 'local_recompletion'), LOCAL_RECOMPLETION_DELETE);

        $mform->addGroup($cba, 'certprint', get_string('pluginname', 'certprint'), array(' '), false);
        $mform->addHelpButton('certprint', 'certprint', 'local_recompletion');
        $mform->setDefault('certprint', $config->certprint);
    }

    /**
     * Add sitelevel settings for this plugin.
     *
     * @param admin_settingpage $settings
     */
    public static function settings($settings) {
        $choices = array(LOCAL_RECOMPLETION_NOTHING => get_string('donothing', 'local_recompletion'),
            LOCAL_RECOMPLETION_DELETE => get_string('archive', 'local_recompletion'));
        $settings->add(new \admin_setting_configselect('local_recompletion/certprint',
            new lang_string('pluginname', 'certprint'),
            new lang_string('pluginname_help', 'certprint'), LOCAL_RECOMPLETION_NOTHING, $choices));
    }

    /**
     * Reset and archive certprint records.
     * @param \stdclass $userid - user id
     * @param \stdClass $course - course record.
     * @param \stdClass $config - recompletion config.
     */
    public static function reset($userid, $course, $config) {
        global $DB;

        if (empty($config->certprint)) {
            return;
        } else if ($config->certprint == LOCAL_RECOMPLETION_DELETE) {
                $certificates = $DB->get_records('certprint_certificates', array('userid' => $userid, 'course' => $course->id,'archived'=>0));
                if ($certificates) {
                    foreach ($certificates as $certificate) {
                        $certificate->archived = 1;
                        $DB->update_record('certprint_certificates', $certificate);
                    }
                }
        }
    }
}
