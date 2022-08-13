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
 * Custom Certificate handler event.
 *
 * @package     local_recompletion
 * @author      Lukas Celinak
 * @copyright   Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_recompletion\activities;

use lang_string;

/**
 * Custom Certificate handler event.
 *
 * @package    local_recompletion
 * @author     Lukas Celinak
 * @copyright  Catalyst IT
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class customcert {
    /**
     * Add params to form.
     * @param moodleform $mform
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function editingform($mform) : void {
        $config = get_config('local_recompletion');

        $cba = array();
        $cba[] = $mform->createElement('radio', 'customcert', '',
            get_string('donothing', 'local_recompletion'), LOCAL_RECOMPLETION_NOTHING);
        $cba[] = $mform->createElement('radio', 'customcert', '',
            get_string('deletecustomcertissues', 'local_recompletion'), LOCAL_RECOMPLETION_DELETE);

        $mform->addGroup($cba, 'customcert', get_string('customcertissues', 'local_recompletion'), array(' '), false);
        $mform->addHelpButton('customcert', 'customcertissues', 'local_recompletion');
        $mform->setDefault('customcert', $config->customcert);

        $mform->addElement('checkbox', 'archivecustomcertissues',
            get_string('archivecustomcertissues', 'local_recompletion'));
        $mform->setDefault('archivecustomcertissues', $config->archivecustomcertissues);

        $mform->disabledIf('customcert', 'enable', 'notchecked');
        $mform->disabledIf('archivecustomcert', 'enable', 'notchecked');
        $mform->hideIf('archivecustomcert', 'customcert', 'noteq', LOCAL_RECOMPLETION_DELETE);
    }

    /**
     * Add sitelevel settings for this plugin.
     *
     * @param admin_settingpage $settings
     */
    public static function settings($settings) {
        $choices = array(LOCAL_RECOMPLETION_NOTHING => new lang_string('donothing', 'local_recompletion'),
                         LOCAL_RECOMPLETION_DELETE => new lang_string('deletecustomcertissues', 'local_recompletion'));

        $settings->add(new \admin_setting_configselect('local_recompletion/customcert',
            new lang_string('customcertissues', 'local_recompletion'),
            new lang_string('customcertissues_help', 'local_recompletion'), LOCAL_RECOMPLETION_NOTHING, $choices));

        $settings->add(new \admin_setting_configcheckbox('local_recompletion/archivecustomcertissues',
            new lang_string('archivecustomcertissues', 'local_recompletion'), '', 1));
    }

    /**
     * Reset and archive quiz records.
     * @param \int $userid - userid
     * @param \stdclass $course - course record.
     * @param \stdClass $config - recompletion config.
     */
    public static function reset($userid, $course, $config) {
        global $DB;
        if (empty($config->customcert)) {
            return;
        } else if ($config->customcert == LOCAL_RECOMPLETION_DELETE) {
            $params = array('userid' => $userid, 'course' => $course->id);
            $selectsql = 'userid = ? AND customcert IN (SELECT id FROM {customcert} WHERE course = ?)';
            if ($config->archivecustomcert) {
                $customcertissues = $DB->get_records_select('customcert_issues', $selectsql, $params);
                foreach ($customcertissues as $isueid => $unused) {
                    // Add courseid to records to help with restore process.
                    $customcertissues[$isueid]->course = $course->id;
                }
                $DB->insert_records('local_recompletion_ccrt_i', $customcertissues);
            }
            $DB->delete_records_select('customcert_issues', $selectsql, $params);
        }
    }
}