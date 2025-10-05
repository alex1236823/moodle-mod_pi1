<?php
require(__DIR__ . '/../../config.php'); // lädt Moodle-Framework-Bootstrap
require_once(__DIR__ . '/lib.php'); // lädt unsere Bibliothek

$id = optional_param('id', 0, PARAM_INT); // cmid = course moodle id = ID des Aktivitäts-Eintrags in der Tabelle course-modules
$n  = optional_param('n', 0, PARAM_INT);  // instance id = Primärschlüssel (Bsp. view.php?n=5) -> 
//optional_param($name, $default, $type) -> Sucht nach Parameter $name, wenn nicht vorhanden liefert $default


// Zwei Wege, die Aktivität zu identifizieren: über cmid (falls $id=/=0 oder Modul-Instanz-ID (z.B. ?n=5))
if ($id) {
    $cm     = get_coursemodule_from_id('pi1', $id, 0, false, MUST_EXIST);
    $course = get_course($cm->course);
    $pi1    = $DB->get_record('pi1', ['id' => $cm->instance], '*', MUST_EXIST);
} elseif ($n) {
    $pi1    = $DB->get_record('pi1', ['id' => $n], '*', MUST_EXIST);
    $course = get_course($pi1->course);
    $cm     = get_coursemodule_from_instance('pi1', $pi1->id, $course->id, false, MUST_EXIST);
} else {
    throw new moodle_exception('missingparameter');
}

// Sorge dafür, dass Nutzer angemeldet ist und Zugriff hat auf Aktivität
require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/pi1:view', $context); //Prüft, ob der/die Nutzer:in im Modul-Kontext die Capability mod/pi1:view besitzt.

// Seiteneinstellungen.
$PAGE->set_url('/mod/pi1/view.php', ['id' => $cm->id]); // Setzt ie kanonische URL der Seite
$PAGE->set_title(format_string($pi1->name)); // Setzt den HTML-Titel
$PAGE->set_heading(format_string($course->fullname)); // Setzt die Seitenüberschrift
$PAGE->set_context($context); // Ermittelt den Kontext auf Ebene dieses Kursmoduls und legt ihn als Kontext für diese Seite fest
$PAGE->set_pagelayout('incourse');

require_once(__DIR__.'/classes/form/search_form.php'); // Formular-Klasse laden

$formurl = new moodle_url('/mod/pi1/view.php', ['id' => $cm->id]); // URL für das Formular bauen

$mform = new \mod_pi1\form\search_form($formurl, ['defaultq' => $pi1->query ?? '']); // Instanziert Formular-Klasse. Wir übergeben hier den gespeicherten Wert aus der Aktivität ($pi1->query) als Voreinstellung;

// Wenn Nutzer gerade etwas gesendet hat (unde s valide ist), dann $city=trim($data->q)
if ($data = $mform->get_data()) {
    $city = trim($data->q);
} else {
    $city = $pi1->query ?: 'Berlin';
}

echo $OUTPUT->header(); // Rendert den Seitenkopf (<head>). Muss vor jeglicher Inhaltsausgabe kommen
echo $OUTPUT->heading(format_string($pi1->name)); // Inhaltsüberschrift für Aktivität

// Intro/Description (kommt aus standard_intro_elements()). Nutzt Moodles Formatierung/Filter und Theme styles
// trim($pi1->intro ?? ''): Nimm $pi1->intro, falls vorhanden und nicht null, sonst nimm '' (leerer String). (Null-Coalescing-Operator ??)
// trim entfernt Leerzeichen/Tab/Zeilenumbrüche
// offizielle Weg, ein Modul-Intro auszugeben laut doks
// $OUTPUT->box(..., 'generalbox mod_introbox') rendert einen div-Container mit den CSS Klassen generalbox und mod_introbox
if (trim($pi1->intro ?? '') !== '') {
    echo $OUTPUT->box(format_module_intro('pi1', $pi1, $cm->id), 'generalbox mod_introbox');
}

// --- REST: Geocoding + Wetter von Open-Meteo (kein API-Key) --- $CFG->libdir entspricht Pfad /var/www/moodle/lib
require_once($CFG->libdir . '/filelib.php'); // stellt curl() bereit

// $city = $pi1->query ?: 'Berlin'; // auskommentiert, da nun Formular zur Suche
// Wenn $pi1->query nicht leer/0/false/null ist → nimm ihn, sonst Fallback 'Berlin'
$curl = new curl(); // curl ist Moodles HTTP-Client (curl->get,post,head,setHeader,...) -> erstellt den HTTP-Client, den du für externe Requests verwenden kannst

// 1) Geodaten - Array in URL einbauen; moodle_url baut eine URL samt Query-String korrekt URL-kodiert
// $city kommt aus der Aktivität (definiert in mod_form.php)
$geourl = new moodle_url('https://geocoding-api.open-meteo.com/v1/search', [
    'name'     => $city,
    'count'    => 1,
    'language' => 'en',
    'format'   => 'json',
]);
// in $rawgeo wird der Rohtext der Antwort als JSON-String gespeichert
$rawgeo = $curl->get($geourl->out(false)); // out(false) -> rohe URL für HTTP (mit & im query string); out(true) würde &amp liefern zum einbinden in HTML
$geo    = json_decode($rawgeo, true); // JSON in PHP-Struktur umwandeln; geo besitzt array Struktur von Länge 1, da 'count' => 1
// -> Zurgriff mit: $lat = $geo['results'][0]['latitude']; 
// results ist der Schlüsselname aus der JSON-Antwort der API bzw. das Top-Level-Field
// true in decode sorgt für assoziative Arrays statt Objekte

// Fehlermeldung, wenn keine Stadt gefunden oder genauer wenn $geo kein array oder kein erster Treffer vorhanedn, also array nicht lesbar
//Rendert einen hinweis/alert-Block im Moodle-Theme. Mit 'notifyproblem' bekommt er i. d. R. rote/Fehler-Stilistik (je nach Theme).
if (!is_array($geo) || empty($geo['results'][0])) {
    echo $OUTPUT->notification(get_string('pi1_noplacefound', 'mod_pi1', s($city)), 'notifyproblem');
    echo $OUTPUT->footer();
    exit;
}

$lat = $geo['results'][0]['latitude'];
$lon = $geo['results'][0]['longitude'];

// 2) Weather-Daten - Array in URL einbauen; moodle_url baut eine URL samt Query-String korrekt URL-kodiert
$wurl = new moodle_url('https://api.open-meteo.com/v1/forecast', [
    'latitude'        => $lat,
    'longitude'       => $lon,
    'current_weather' => 'true'
]);
$rawweather = $curl->get($wurl->out(false));
$weather    = json_decode($rawweather, true);
// true in decode sorgt für assoziative Arrays statt Objekte

// --- Ausgabe: 1) Roh-JSON --- html-writer ist eine Moodle-Core-Hilfsklasse
// get_string($identifier, $component, $a=null) holt einen lokalisierten Text.
// $identifier = Schlüssel (z. B. 'pi1_rawjson')
// $component = Komponente/Plugin (hier dein Aktivitätsmodul mod_pi1)
// Moodle sucht den String im Sprachpaket der Komponente und gibt die Übersetzung für die aktuell eingestellte Sprache zurück.
echo html_writer::tag('h3', get_string('pi1_rawjson', 'mod_pi1')); //  Verpacke in <h3></h3>
echo html_writer::tag('pre', s($rawweather)); // s(..) wandelt gefährliche Zeichen in HTML-Zeichen um z.B. & -> &amp; um XSS zu umgehen Verpacke anschließend in <pre>..</pre>

// --- Ausgabe: 2) Formatiert ---
echo html_writer::tag('h3', get_string('pi1_pretty', 'mod_pi1')); //pi1_pretty in /lang/en

if (!empty($weather['current_weather'])) {
    $cw  = $weather['current_weather']; // Beachte hier ist wegen forecast?current_weather=true current weather Top-Level, d.h. es gibt kein results.
    //Insbesondere ist kein $weather['results'][0]['current_weather'] notwendig oder sogar falsch
    $rows = []; // Array separat initialisieren um Fehler zu vermeiden
    $rows[] = html_writer::tag('div', get_string('pi1_citylabel', 'mod_pi1', s($city))); // erste Zeile Stadt / Label pi1_citylabel etc. kommen aus den Ssprachdateien unter /lang
    $rows[] = html_writer::tag('div', get_string('pi1_temp', 'mod_pi1', format_float($cw['temperature'])) . ' °C');
    $rows[] = html_writer::tag('div', get_string('pi1_wind', 'mod_pi1', format_float($cw['windspeed'])) . ' km/h');

    $card = html_writer::div(implode('', $rows), 'pi1-card'); // implode('', $rows): Verklebt die HTML-Schnipsel ohne Trenner zu einem String.
    // Ergebnis: "<div>…</div><div>…</div><div>…</div>".
    // html_writer::div($content, 'pi1-card') erzeugt einen <div>-Container mit Klasse pi1-card um den gesamten Inhalt
    echo $card;

} else {
    echo $OUTPUT->notification(get_string('pi1_noweather', 'mod_pi1'), 'notifyproblem'); // Fehlermeldung wie oben
}

$mform->display(); // Formular ausgeben

echo $OUTPUT->footer(); // $OUTPUT->footer() liefert den Seiten-Footer als HTML-String
