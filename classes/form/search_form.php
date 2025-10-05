<?php
namespace mod_pi1\form; // Passt zur Dateiposition mod/pi1/classes/form/search_form.php.
// Moodle autoloadet die Klasse später als \mod_pi1\form\search_form.

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php'); // Lädt die Form-API. Darin steckt u. a. die Basisklasse moodleform und HTML_QuickForm.

// Erben der Basisklasse moodleform
class search_form extends \moodleform {
    public function definition() {
        $mform = $this->_form;	// kommt aus Quickform; legt lokale Variable an; Danach $mform-> statt $this->_form->

        // Eingabefeld für die Stadt / den Suchbegriff.
        $mform->addElement('text', 'q', get_string('pi1_form_q', 'mod_pi1')); // Textfeld mit Name q hinzufügen
        $mform->setType('q', PARAM_TEXT); // Legt den Validierungstyp nach entfernen von Tags + XSS-entschärft
        $mform->setDefault('q', $this->_customdata['defaultq'] ?? ''); // customdata sind die beim Erzeugen übergebenen Daten z.B. $form = new \mod_pi1\form\search_form($url, ['defaultq' => 'Berlin']);
        $mform->addRule('q', null, 'required', null, 'client'); // Markiere q als Pflichtfeld (clientseitig nur im Browser)

        // Buttonleiste (nur "Suchen", kein Cancel).
        $this->add_action_buttons(false, get_string('pi1_search', 'mod_pi1')); // false bewirkt kein Cancel- nur Submit-Button
    }
}
