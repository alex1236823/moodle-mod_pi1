<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Welche Core-Features unterstützt das Modul?
 */
function pi1_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:        return true;  // Intro/Description-Feld benutzen
        case FEATURE_SHOW_DESCRIPTION: return true;  // Beschreibung auf Kursseite anzeigen können
        default:                       return null;  // Rest: Standard
    }
}

/**
 * Beim Anlegen einer neuen Instanz (Kursbearbeitung → „Material/​Aktivität anlegen“).
 * $data kommt aus mod_form.php. $DB ist globales Objekt der Moodle-Datenbankansicht
 */
function pi1_add_instance(stdClass $data, $mform = null) {
    global $DB;
    $data->timecreated  = time();
    $data->timemodified = $data->timecreated; //Am Anfang ist letztes mal modified = erstellt (Zeit)
    // $data->course, $data->name, $data->intro, $data->introformat, $data->query kommen aus dem Formular.
    return $DB->insert_record('pi1', $data);
}

/**
 * Beim Bearbeiten einer bestehenden Instanz.
 */
function pi1_update_instance(stdClass $data, $mform = null) {
    global $DB;
    $data->id           = $data->instance; // Moodle übergibt 'instance' = Datensatz-ID in unserer Tabelle
    $data->timemodified = time();
    return $DB->update_record('pi1', $data);
}

/**
 * Beim Löschen einer Instanz aus dem Kurs.
 * Hier würden auch alle abhängigen Kind-Datensätze entfernt.
 */
function pi1_delete_instance($id) {
    global $DB;
    if (!$DB->record_exists('pi1', ['id' => $id])) {
        return false;
    }
    $DB->delete_records('pi1', ['id' => $id]);
    return true;
}
