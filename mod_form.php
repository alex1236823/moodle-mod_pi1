<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');

/**
 * Formular zum Anlegen/Bearbeiten einer pi1-Aktivität.
 */
class mod_pi1_mod_form extends moodleform_mod {

    public function definition() {
        $mform = $this->_form;

	// Section header title according to language file.
        $mform->addElement('header', 'general', get_string('general'));

        // --- Allgemein ----------------------------------------------------
        // Instanzname (landet in {pi1}.name).
        $mform->addElement('text', 'name', get_string('name'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        // Einleitungs-/Beschreibungstext (landet in {pi1}.intro / introformat).
        // Moodle fügt Editor + Format-Feld automatisch hinzu.
        $this->standard_intro_elements();

        // --- Unser Suchparameter ------------------------------------------
        // Freitext für die spätere API-Abfrage (z. B. Stadtname).
        $mform->addElement('text', 'query', get_string('query', 'mod_pi1'), ['size' => 48]);
        $mform->setType('query', PARAM_TEXT);
        $mform->addRule('query', null, 'required', null, 'client');
        $mform->addHelpButton('query', 'query', 'mod_pi1');

        // --- Kursmodul-Standarddinge (Sichtbarkeit, ID-Nummer, Verfügbarkeit, etc.)
        $this->standard_coursemodule_elements();

        // --- Speichern/Abbrechen-Buttons ----------------------------------
        $this->add_action_buttons();
    }

    /**
     * Serverseitige Validierung (ergänzt die Client-Regeln).
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (isset($data['query']) && trim($data['query']) === '') {
            $errors['query'] = get_string('err_required', 'form');
        }
        if (isset($data['name']) && trim($data['name']) === '') {
            $errors['name'] = get_string('err_required', 'form');
        }

        return $errors;
    }
}
