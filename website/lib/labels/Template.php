<?php

abstract class Template {
    private $manual_strings = array(
'pl' => 'Instrukcja obsługi: 1. Zabierz geokreta. Zanotuj tracking code 2. Ukryj kreta w innym keszu. 3. Zarejestruj jego podróż na stronie https://geokrety.org',
'en' => "User's manual: 1. Take this GeoKret. Please note down his Tracking Code. 2. Hide in another cache. 3. Register the trip at https://geokrety.org",
'de' => 'Anleitung: 1. Nimm den GeoKret mit und notiere Dir den Tracking Code. 2. Verstecke ihn wieder in einem anderen Cache. 3. Logge seine Reise auf https://geokrety.org',
'cz' => 'Navod pro uživatele: 1. Vem Geokrtka. Poznamenej si jeho Tracking Code. 2. Schovej ho v jiné kešce. 3. Registruj cestu na https://geokrety.org',
'fr' => "Manuel de l'utilisateur: 1. Prenez ce GeoKret et notez son code de suivi. 2. Déposez le dans une autre cache. 3. Enregistrez son voyage sur https://geokrety.org",
'ru' => 'Руководство пользователя: 1. Возьмите ГеоКрота. Запишите его Tracking Code. 2. Переместите его в другой тайник. 3. Сообщите об этом на https://geokrety.org',
);

    /**
     * Returns name of the template. Use `::` as separator between different parts of the name (i.e. type and author).
     *
     * @return string
     */
    abstract public function getName();

    public function getId() {
        return get_class($this);
    }

    /**
     * Get list of Geokrety instructions in different languages.
     *
     * @param $languages string[] list of languages to get help for
     *
     * @return array string[] List of manual texts in needed languages
     */
    public function getManuals($languages) {
        $result = array();
        foreach ($languages as $lang) {
            if (array_key_exists($lang, $this->manual_strings)) {
                $result[] = $this->manual_strings[$lang];
            }
        }

        return $result;
    }

    /**
     * Get list of Geokrety instructions in specific language.
     *
     * @param $lang string language to get help for
     *
     * @return array string manual text
     */
    public function getManual($lang) {
        return $this->manual_strings[$lang];
    }

    abstract public function generate($gkId, $trackingCode, $gkName, $owner, $comment = '', $languages = ['en']);
}
