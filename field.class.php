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
 * Class lockabletext field for database activity
 *
 * @package    datafield_lockabletext
 * @copyright  2024 onwards Lafayette College ITS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use core\notification;

require_once(__DIR__ . '/../text/field.class.php');

/**
 * Class lockabletext field for database activity
 *
 * @package    datafield_lockabletext
 * @copyright  2024 onwards Lafayette College ITS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class data_field_lockabletext extends data_field_text {
    /** @var string The internal datafield type */
    public $type = 'lockabletext';

    /**
     * Output control for editing content.
     *
     * @param int $recordid the id of the data record.
     * @param object $formdata the submitted form.
     *
     * @return string
     */
    public function display_add_field($recordid=0, $formdata=null) {
        global $DB, $OUTPUT;

        $readonly = '';
        $context = \context_module::instance($this->cm->id);

        if ($this->field->param1 === 'on' && !has_capability('datafield/lockabletext:manage', $context)) {
            $readonly = ' readonly';
        }

        if ($formdata) {
            $fieldname = 'field_' . $this->field->id;
            $content = $formdata->$fieldname;
        } else if ($recordid) {
            $content = $DB->get_field('data_content', 'content', ['fieldid' => $this->field->id, 'recordid' => $recordid]);
        } else {
            $content = '';
        }

        // Beware get_field returns false for new, empty records MDL-18567.
        if ($content === false) {
            $content = '';
        }

        $str = '<div title="' . s($this->field->description) . '">';
        $str .= '<label for="field_'.$this->field->id.'"><span class="accesshide">'.s($this->field->name).'</span>';
        if ($this->field->required) {
            $image = $OUTPUT->pix_icon('req', get_string('requiredelement', 'form'));
            $str .= html_writer::div($image, 'inline-req');
        }
        $str .= '</label><input class="basefieldinput form-control d-inline mod-data-input" ' .
                'type="text" name="field_' . $this->field->id . '" ' .
                'id="field_' . $this->field->id . '" value="' . s($content) . '"' . $readonly . '/>';
        $str .= '</div>';
        $str = '<input class="basefieldinput form-control d-inline mod-data-input" ' .
        'type="lockabletext" name="field_' . $this->field->id . '" ' .
        'id="field_' . $this->field->id . '" value="' . s($content) . '"' . $readonly . '/>';

        return $str;
    }

    /**
     * Update the content of one data field in the data_content table.
     *
     * Capability checks occur here because the field_validation
     * method does not have access to the old record.
     *
     * @param int $recordid the record id
     * @param string $value the the draft area id
     * @param string $name constructed name of the field, such as "field_10_lockabletext"
     */
    public function update_content($recordid, $value, $name='') {
        global $DB;

        $context = \context_module::instance($this->cm->id);
        if (!has_capability('datafield/lockabletext:manage', $context)) {
            return true;
        }

        return parent::update_content($recordid, $value, $name);
    }
}
