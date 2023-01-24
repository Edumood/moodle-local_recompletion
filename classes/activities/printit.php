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
 * printit handler event.
 *
 * @package     local_recompletion
 * @author      Dan Marsden
 * @copyright   Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_recompletion\activities;

use lang_string;

/**
 * printit handler event.
 *
 * @package    local_recompletion
 * @author     Dan Marsden
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class printit {
    /**
     * Add params to form.
     * @param moodleform $mform
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function editingform($mform) : void {
        $config = get_config('local_recompletion');

        $cba = array();
        $cba[] = $mform->createElement('radio', 'printit', '',
            get_string('donothing', 'local_recompletion'), LOCAL_RECOMPLETION_NOTHING);
        $cba[] = $mform->createElement('radio', 'printit', '',
            get_string('archive', 'local_recompletion'), LOCAL_RECOMPLETION_DELETE);

        $mform->addGroup($cba, 'printit', get_string('pluginname', 'printit'), array(' '), false);
        $mform->addHelpButton('printit', 'printit', 'local_recompletion');
        $mform->setDefault('printit', $config->printit);
    }

    /**
     * Add sitelevel settings for this plugin.
     *
     * @param admin_settingpage $settings
     */
    public static function settings($settings) {
        $choices = array(LOCAL_RECOMPLETION_NOTHING => get_string('donothing', 'local_recompletion'),
            LOCAL_RECOMPLETION_DELETE => get_string('archive', 'local_recompletion'));
        $settings->add(new \admin_setting_configselect('local_recompletion/printit',
            new lang_string('pluginname', 'printit'),
            new lang_string('pluginname_help', 'printit'), LOCAL_RECOMPLETION_NOTHING, $choices));
    }

    /**
     * Reset and archive printit records.
     * @param \stdclass $userid - user id
     * @param \stdClass $course - course record.
     * @param \stdClass $config - recompletion config.
     */
    public static function reset($userid, $course, $config) {
        global $DB;

        if (empty($config->printit)) {
            return;
        } else if ($config->printit == LOCAL_RECOMPLETION_DELETE) {
                $certificates = $DB->get_records('printit_documents', array('userid' => $userid, 'course' => $course->id,'archived'=>0));
                if ($certificates) {
                    foreach ($certificates as $certificate) {
                        $certificate->archived = 1;
                        $DB->update_record('printit_documents', $certificate);
                    }
                }
        }
    }
}
