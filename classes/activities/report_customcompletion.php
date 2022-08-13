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
 * Assign handler event.
 *
 * @package     local_recompletion
 * @author      Dan Marsden
 * @copyright   Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_recompletion\activities;

use lang_string;

/**
 * Quiz handler event.
 *
 * @package    local_recompletion
 * @author     Dan Marsden
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class report_customcompletion {
    /**
     * Add params to form.
     * @param moodleform $mform
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function editingform($mform): void {
        $config = get_config('local_recompletion');

        $cba = array();
        $cba[] = $mform->createElement('radio', 'report_customcompletion', '',
            get_string('donothing', 'local_recompletion'), LOCAL_RECOMPLETION_NOTHING);
        $cba[] = $mform->createElement('radio', 'report_customcompletion', '',
            get_string('delete', 'local_recompletion'), LOCAL_RECOMPLETION_DELETE);
        $mform->addGroup($cba, 'report_customcompletion', get_string('pluginname', 'report_customcompletion'), array(' '), false);
        $mform->addHelpButton('report_customcompletion', 'pluginname', 'report_customcompletion');

        $mform->setDefault('report_customcompletion', $config->report_customcompletion);

        $mform->disabledIf('report_customcompletion', 'enable', 'notchecked');
    }

    /**
     * Add sitelevel settings for this plugin.
     *
     * @param admin_settingpage $settings
     */
    public static function settings($settings) {
        $choices = array(LOCAL_RECOMPLETION_NOTHING => new lang_string('donothing', 'local_recompletion'),
            LOCAL_RECOMPLETION_DELETE => new lang_string('delete', 'local_recompletion'));

        $settings->add(new \admin_setting_configselect('local_recompletion/report_customcompletion',
            new lang_string('pluginname', 'report_customcompletion'),
            new lang_string('pluginname', 'report_customcompletion'), LOCAL_RECOMPLETION_NOTHING, $choices));
    }

    /**
     * Reset assign records.
     * @param \int $userid - record with user information for recompletion
     * @param \stdClass $course - course record.
     * @param \stdClass $config - recompletion config.
     */
    public static function reset($userid, $course, $config) {
        global $DB;
        if (empty($config->customcompletion)) {
            return '';
        } else if ($config->customcompletion == LOCAL_RECOMPLETION_DELETE) {
            // Prepare functions for log querries
            $logmang = \get_log_manager();
            $readers = $logmang->get_readers('\core\log\sql_internal_table_reader');
            $reader = reset($readers);
            $readername = key($readers);
            if (empty($reader) || empty($readername)) {
                // No readers, no processing.
                return true;
            }
            $logtable = $reader->get_internal_log_table_name();
            $sql = "UPDATE {" . $logtable . "} SET other='deleted'
                        WHERE eventname = :eventname
                        AND relateduserid = :userid 
                        AND course = :courseid ";

            $params = ['eventname'=>'report_customcompletion\event\notification_sent',
                    'userid'=>$userid,
                    'courseid'=>$course->id];
            $DB->execute($sql,$params);

        }
        return '';
    }
}
