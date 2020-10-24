<?php

namespace Core\Input;

class UserInput {

    public const RAW = 0;
    public const FILTER_TXT = 101;
    public const FILTER_MAIL = 102;
    public const FILTER_URL = 103;
    public const FILTER_INT = 104;
    public const FILTER_FLOAT = 105;
    public const FILTER_HTML = 1003;
    public const FILTER_SHELL = 1004;

    private static $getInstance = null;
    private static $postInstance = null;

    /**
     * Get var
     * Syntaxe : UserInput::GET(filter)->variableName
     * UserInput::GET(UserInput::FILTER_MAIL)->email permet de lire $_GET['email'] et d'y appliquer un filtrage de type email
     * 
     * UserInput::GET()->email est eqUserInputvalent à UserInput::GET(UserInput::FILTER_TXT)->email
     * 
     * - Lire avec filtre par defaut                : $id = UserInput::GET()->order_id
     * - Lire avec une filtre spécifique            : $id = UserInput::GET(UserInput::FILTER_INT)->order_id
     * - Lire sans aucune filtre                    : $id = UserInput::GET(UserInput::RAW)->order_id
     * - Ecrire                                     : UserInput::GET()->action = 150
     * - Tester                                     : isset(UserInput::GET()->action)
     * - Parcourir avec filtre par defaut           : foreach(UserInput::GET() as $k => $v)
     * - Parcourir sans aucun filtre                : foreach(UserInput::GET(UserInput::raw) as $k => $v)
     *
     *
     * Pour les noms de variables qUserInput utilisent des caractères spéciaux ou qUserInput commencent par un nombre,
     * il est possible d'utiliser la notation "curly braces", par exemple : 
     * - $_GET[$prefix.'label']     deviens     UserInput::GET()->{$prefix.'label'}
     * - $_GET['arc-en-ciel']       deviens     UserInput::GET()->{'arc-en-ciel'}
     * - $_GET['3dsecure']          deviens     UserInput::GET()->{'3dsecure'}
     * 
     *
     * @param int $filter
     * @return Sanitizer
     */
    public static function GET($filter = UserInput::FILTER_TXT) :Sanitizer {
        if (self::$getInstance === null) {
            self::$getInstance = new Sanitizer(INPUT_GET);
        }
        self::$getInstance->setFilter($filter);
        return self::$getInstance;
    }

    /**
     * post var
     * Syntaxe : UserInput::POST(filter)->variableName
     * UserInput::POST(UserInput::FILTER_MAIL)->email permet de lire $_POST['email'] et d'y appliquer un filtrage de type email
     * 
     * UserInput::POST()->email est eqUserInputvalent à UserInput::POST(UserInput::FILTER_TXT)->email
     * 
     * - Lire avec filtre par defaut                : $id = UserInput::POST()->order_id
     * - Lire avec une filtre spécifique            : $id = UserInput::POST(UserInput::FILTER_INT)->order_id
     * - Lire sans aucune filtre                    : $id = UserInput::POST(UserInput::RAW)->order_id
     * - Ecrire                                     : UserInput::POST()->action = 150
     * - Tester                                     : isset(UserInput::POST()->action)
     * - Parcourir avec filtre par defaut           : foreach(UserInput::POST() as $k => $v)
     * - Parcourir sans aucun filtre                : foreach(UserInput::POST(UserInput::raw) as $k => $v)
     *
     *
     * Pour les noms de variables qUserInput utilisent des caractères spéciaux ou qUserInput commencent par un nombre,
     * il est possible d'utiliser la notation "curly braces", par exemple : 
     * - $_POST[$prefix.'label']     deviens     UserInput::POST()->{$prefix.'label'}
     * - $_POST['arc-en-ciel']       deviens     UserInput::POST()->{'arc-en-ciel'}
     * - $_POST['3dsecure']          deviens     UserInput::POST()->{'3dsecure'}
     * 
     *
     * @param int $filter
     * @return Sanitizer
     */
    public static function POST($filter = UserInput::FILTER_TXT) :Sanitizer {
        if (self::$postInstance === null) {
            self::$postInstance = new Sanitizer(INPUT_POST);
        }
        self::$postInstance->setFilter($filter);
        return self::$postInstance;
    }


}

