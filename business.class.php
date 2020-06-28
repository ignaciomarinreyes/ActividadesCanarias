<?php
include_once 'data_access.class.php';

class Activity {
    private static function addError(&$errors, $element, $message) {
        $error = new stdClass();
        $error->element = $element;
        $error->error = $message;
        $errors->append($error);
    }

    public static function checkActivity($idActivity) {
        Session::start();
        if (DB::selectActivity($idActivity, $result)) {
            $_SESSION['activity'] = $result[0];
            return true;
        }
        return false;
    }

    public static function checkDataFromPost($requiredImage, &$errors) {
        $errors = new ArrayObject();

        if (count($_POST) == 0 ||
            !isset($_POST['name']) ||
            !isset($_POST['type']) ||
            !isset($_POST['description']) ||
            !isset($_POST['price']) ||
            !isset($_POST['capacity']) ||
            !isset($_POST['startDate']) ||
            !isset($_POST['duration']) ||
            !isset($_FILES['image'])) {
            self::addError($errors, 'form', 'Error inesperado');
            return false;
        }

        if (strlen($_POST['name']) == 0) {
            self::addError($errors, 'name', 'Campo obligatorio');
        } else {
            if (strlen($_POST['name']) < 2 ||
                strlen($_POST['name']) > 32) {
                self::addError($errors, 'name', 'Entre 2 y 32 caracteres');
            }
        }

        if (strlen($_POST['type']) == 0) {
            self::addError($errors, 'type', 'Campo obligatorio');
        } else {
            if (strlen($_POST['type']) > 16) {
                self::addError($errors, 'type', 'Entre 1 y 16 caracteres');
            }
        }

        if (strlen($_POST['description']) == 0) {
            self::addError($errors, 'description', 'Campo obligatorio');
        } else {
            if (strlen($_POST['description']) < 12 ||
                strlen($_POST['description']) > 1024) {
                self::addError($errors, 'description',
                    'Entre 12 y 1024 caracteres');
            }
        }

        if (strlen($_POST['price']) == 0) {
            self::addError($errors, 'price', 'Campo obligatorio');
        } else {
            if ((float)$_POST['price'] < 0) {
                self::addError($errors, 'price',
                    'Debe ser mayor o igual a 0');
            }
        }

        if (strlen($_POST['capacity']) == 0) {
            self::addError($errors, 'capacity', 'Campo obligatorio');
        } else {
            if ((int)$_POST['capacity'] <= 0) {
                self::addError($errors, 'capacity', 'Debe ser mayor que 0');
            }
        }

        if (strlen($_POST['startDate']) == 0) {
            self::addError($errors, 'startDate', 'Campo obligatorio');
        } else {
            if (strtotime(str_replace('/', '-', $_POST['startDate'])) == null) {
                self::addError($errors, 'startDate', 'Fecha incorrecta');
            }
        }

        if (strlen($_POST['duration']) == 0) {
            self::addError($errors, 'duration', 'Campo obligatorio');
        } else {
            if ((int) $_POST['duration'] < 0) {
                self::addError($errors, 'duration', 'Debe ser mayor que 0');
            }
        }

        if ($requiredImage && $_FILES['image']['error'] == UPLOAD_ERR_NO_FILE) {
            self::addError($errors, 'image', 'Campo obligatorio');
        }
        if ($_FILES['image']['error'] != UPLOAD_ERR_NO_FILE) {
            if (!Image::isValidType($_FILES['image']['type'])) {
                self::addError($errors, 'image', 'Tipo incorrecto');
            }
            if (!$_FILES['image']['error'] == UPLOAD_ERR_OK) {
                self::addError($errors, 'image', 'Error del archivo adjunto');
            }
            if (!Image::isValidSize($_FILES['image']['size'])) {
                self::addError($errors, 'image', 'Supera el tamaño permitido');
            }
        }

        if (isset($errors[0])) {
            $error = new stdClass();
            $error->element = 'form';
            $error->error = 'Hay campos que no cumplen las validaciones';
            $errors->append($error);
            return false;
        }

        return true;
    }

    public static function delete($id) {
        return DB::deleteActivity($id);
    }

    public static function getAll($idPromoter = null) {
        return DB::selectAllActivities();
    }

    public static function getAllByPromoter($idPromoter) {
        return DB::selectAllActivitiesByPromoter($idPromoter);
    }

    public static function getCapacity() {
        return self::getCheckedActivity()['aforo'];
    }

    public static function getDescription() {
        return self::getCheckedActivity()['descripcion'];
    }

    public static function getDuration() {
        return self::getCheckedActivity()['duracion'];
    }

    public static function getFreeCapacity() {
        return (int) self::getCapacity() - self::getBusyCapacity();
    }

    public static function getId() {
        return self::getCheckedActivity()['id'];
    }

    public static function getIdPromoter() {
        return self::getCheckedActivity()['idempresa'];
    }

    public static function getImage() {
        return self::getCheckedActivity()['imagen'];
    }

    public static function getName() {
        return self::getCheckedActivity()['nombre'];
    }

    public static function getPrice() {
        return self::getCheckedActivity()['precio'];
    }

    public static function getBusyCapacity() {
        if (isset(self::getCheckedActivity()['id'])) {
            return number_format(DB::selectBusyCapacitybyActivity(
                self::getId()), 0);
        }
        return false;
    }

    public static function getSearchedActivities($search) {
        return self::search(self::getAll(), $search);
    }

    public static function getSearchedActivitiesByPromoter($idPromoter,
        $search) {
        return self::search(self::getAllByPromoter($idPromoter), $search);
    }

    public static function getStartDate() {
        return self::getCheckedActivity()['inicio'];
    }

    public static function getType() {
        return self::getCheckedActivity()['tipo'];
    }

    public static function saveChanges($name, $type, $description, $price,
        $capacity, $startDate, $duration, $imageFile) {
        if ($imageFile['error'] == UPLOAD_ERR_NO_FILE) {
            return DB::updateActivity(Activity::getId(), UserLogged::getId(),
                $name, $type, $description, $price, $capacity,
                strtotime(str_replace('/', '-', $startDate)),
                Seconds::valueOf($duration), Activity::getImage());
        } else {
            return DB::updateActivity(Activity::getId(), UserLogged::getId(),
                $name, $type, $description, $price, $capacity,
                strtotime(str_replace('/', '-', $startDate)),
                Seconds::valueOf($duration),
                file_get_contents($imageFile['tmp_name']));
        }
    }

    public static function saveNew($name, $type, $description, $price,
        $capacity, $startDate, $duration, $imageFile) {
        return DB::insertActivity(UserLogged::getId(), $name, $type,
            $description, $price, $capacity,
            strtotime(str_replace('/', '-', $startDate)),
            Seconds::valueOf($duration),
            file_get_contents($imageFile['tmp_name']));
    }

    private static function getCheckedActivity() {
        Session::start();
        return (!isset($_SESSION['activity']) ? false : $_SESSION['activity']);
    }

    private static function search($activities, $search) {
        $results = array();
        foreach($activities as $activity) {
            if (!$search == null &&
                stripos($activity['nombre'], $search) === false &&
                stripos($activity['tipo'], $search) === false &&
                stripos(date('d/m/Y', $activity['inicio']), $search) === false)
                continue;
            array_push($results, $activity);
        }
        return $results;
    }

    public static function setActivity($activity) {
        Session::start();
        $_SESSION['activity'] = $activity;
    }
}

class Customer {
    public static function checkCustomer($id) {
        Session::start();
        if (DB::selectCustomer($id, $result)) {
            $_SESSION['customer'] = $result[0];
            return true;
        }
        return false;
    }

    public static function getAddress() {
        return self::getCheckedCustomer()['direccion'];
    }

    public static function getId() {
        return self::getCheckedCustomer()['id'];
    }

    public static function getEmail() {
        return self::getCheckedCustomer()['email'];
    }

    public static function getName() {
        return self::getCheckedCustomer()['nombre'];
    }

    public static function getPhone() {
        return self::getCheckedCustomer()['telefono'];
    }

    public static function getTown() {
        return self::getCheckedCustomer()['poblacion'];
    }

    private static function getCheckedCustomer() {
        Session::start();
        return (!isset($_SESSION['customer']) ? false : $_SESSION['customer']);
    }
}

class Euro {
    public static function valueOf($number, $symbol = null) {
        if ($number < 0)
            return '';
        $result = number_format($number, 2, ",", ".");
        if ($symbol == true)
            $result .= " €";
        return $result;
    }
}

class IdFromQueryString {
    public static function check() {
        return count($_GET) > 0 &&
            isset($_GET['id']) &&
            $_GET['id'] != null;
    }

    public static function getId() {
        return (!self::check() ? null : $_GET['id']);
    }
}

class Image {
    public static function isValidType($type) {
        return ($type == 'image/jpeg' || $type == 'image/png');
    }

    public static function isValidSize($size) {
        return $size <= 60000;
    }

    public static function toBase64($image) {
        $b64 = base64_encode($image);
        $signature = substr($b64, 0, 3);
        if ( $signature == '/9j') {
            $mime = 'data:image/jpeg;base64,';
        } else if ( $signature == 'iVB') {
            $mime = 'data:image/png;base64,';
        }
        return $mime . $b64;
    }
}

class Promoter {
    public static function checkPromoter($id) {
        Session::start();
        if (DB::selectPromoter($id, $result)) {
            $_SESSION['promoter'] = $result[0];
            return true;
        }
        return false;
    }

    public static function getContact() {
        return self::getCheckedPromoter()['contacto'];
    }

    public static function getDescription() {
        return self::getCheckedPromoter()['descripcion'];
    }

    public static function getId() {
        return self::getCheckedPromoter()['idempresa'];
    }

    public static function getImage() {
        return self::getCheckedPromoter()['logo'];
    }

    public static function getName() {
        return self::getCheckedPromoter()['nombre'];
    }

    private static function getCheckedPromoter() {
        Session::start();
        return (!isset($_SESSION['promoter']) ? false : $_SESSION['promoter']);
    }
}

class SearchFromQueryString {
    public static function check() {
        return count($_GET) > 0 &&
            isset($_GET['search']) &&
            $_GET['search'] != null;
    }

    public static function getSearch() {
        return (!self::check() ? null : $_GET['search']);
    }
}

class Seconds {
    public static function toHHMM($seconds) {
        if ($seconds < 0)
            return null;
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds / 60) % 60);
        return sprintf("%02d:%02d", $hours, $minutes);
    }

    public static function toHoursAndMinutes($seconds) {
        if ($seconds < 0)
            return null;
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds / 60) % 60);
        if ($minutes > 0) {
            if ($hours > 0) {
                return $hours . " horas" . " y " . $minutes . " minutos";
            } else {
                return $minutes . " minutos";
            }
        }
        return $hours . " horas";
    }

    public static function valueOf($time) {
        $parsed = date_parse($time);
        return $parsed['hour'] * 3600 +
            $parsed['minute'] * 60 +
            $parsed['second'];
    }
}

class Session {
    public static function start() {
        if(session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}

class Script {
    public static function getAddActivityFailedUrl() {
        return 'addActivityFailed.php';
    }

    public static function getBuyTicketFailedUrl() {
        return 'buyTicketFailed.php';
    }

    public static function getEditActivityFailedUrl() {
        return 'editActivityFailed.php';
    }

    public static function getTitle() {
        if (isset($_SERVER['SCRIPT_NAME']) &&
            $_SERVER['SCRIPT_NAME'] == '/activity.php' &&
            count($_GET) > 0 &&
            isset($_GET['id']) &&
            Activity::checkActivity($_GET['id']))
            return 'GCActiva: ' . Activity::getName();
        else
            return 'GCActiva';
    }

    public static function getRequestUri() {
        Session::start();
        if (!isset($_SESSION['uri']))
            return '/index.php';
        return $_SESSION['uri'];
    }

    public static function saveRequestUri() {
        Session::start();
        if (isset($_SERVER['REQUEST_URI']))
            $_SESSION['uri'] = $_SERVER['REQUEST_URI'];
    }

    public static function showsActivitySearch() {
        return (self::getScriptName() == '/index.php');
    }

    public static function showsPromoterOpctions() {
        return (self::getScriptName() == '/promoter.php');
    }

    private static function getScriptName() {
        if (!isset($_SERVER['SCRIPT_NAME']))
            return false;
        return $_SERVER['SCRIPT_NAME'];
    }
}

class Ticket {
    private static function addError(&$errors, $element, $message) {
        $error = new stdClass();
        $error->element = $element;
        $error->error = $message;
        $errors->append($error);
    }

    public static function checkDataFromPost(&$errors) {
        $errors = new ArrayObject();

        if (count($_POST) == 0 ||
            !isset($_POST['units'])) {
            self::addError($errors, 'form', 'Error inesperado');
            return false;
        }

        if (strlen($_POST['units']) == 0) {
            self::addError($errors, 'name', 'Campo obligatorio');
        } else {
            if ((int) $_POST['units'] < 1) {
                self::addError($errors, 'units', 'Debe ser mayor que 0');
            }
            if ((int) $_POST['units'] > Activity::getFreeCapacity()) {
                self::addError($errors, 'units', 'Supera el aforo disponible');
            }
        }

        if (isset($errors[0])) {
            $error = new stdClass();
            $error->element = 'form';
            $error->error = 'Hay campos que no cumplen las validaciones';
            $errors->append($error);
            return false;
        }

        return true;
    }

    public static function getAllByActivity($idActivity) {
        return DB::selectAllTicketsByActivity($idActivity);
    }

    public static function getAllByCustomer($idCustomer) {
        return DB::selectAllTicketsByCustomer($idCustomer);
    }

    public static function getActivity() {
        return self::getCheckedTicket()['idactividad'];
    }

    public static function getCustomer() {
        return self::getCheckedTicket()['idcliente'];
    }

    public static function getPrice() {
        return self::getCheckedTicket()['precio'];
    }

    public static function getUnits() {
        return number_format(self::getCheckedTicket()['unidades'], 0);
    }

    public static function isValidUnits($units) {
        return ((int) $units) > 0;
    }

    public static function saveNew($units) {
        return DB::insertTicket(UserLogged::getId(), Activity::getId(),
            Activity::getPrice(), $units);
    }

    public static function setTicket($ticket) {
        Session::start();
        $_SESSION['ticket'] = $ticket;
    }

    private static function getCheckedTicket() {
        Session::start();
        return (!isset($_SESSION['ticket']) ? false : $_SESSION['ticket']);
    }
}

class UserDataFromPost {
    public static function check() {
        return isset($_SESSION['user_data']['username']) &&
            $_SESSION['user_data']['username'] != null;
    }

    public static function getUsername() {
        return (!self::check() ? null : $_SESSION['user_data']['username']);
    }

    public static function clean() {
        Session::start();
        unset($_SESSION['user_data']);
    }

    public static function save() {
        Session::start();
        if (count($_POST) > 0 &&
            isset($_POST['username']) &&
            $_POST['username'] != null &&
            isset($_POST['password']))
            $_SESSION['user_data']['username'] = $_POST['username'];
    }
}

class UserLogged {
    public static function getId() {
        return (!isset(self::getUser()['id']) ? null :
            self::getUser()['id']);
    }

    public static function getName() {
        return (!isset(self::getUser()['nombre']) ? null :
            self::getUser()['nombre']);
    }

    private static function getUser() {
        Session::start();
        return (!isset($_SESSION['user_logged']) ? null :
            $_SESSION['user_logged']);
    }

    public static function login($username, $password) {
        Session::start();
        if (DB::selectUser($username, $password, $result)) {
            $_SESSION['user_logged'] = $result[0];
            return true;
        }
        return false;
    }

    public static function logout() {
        Session::start();
        unset($_SESSION['user_logged']);
    }

    public static function exists() {
        return self::getUser() != null;
    }

    public static function isAdmin() {
        return (!isset(self::getUser()['tipo']) ? false :
            self::getUser()['tipo'] == 1);
    }

    public static function isCustomer() {
        return (!isset(self::getUser()['tipo']) ? false :
            self::getUser()['tipo'] == 3);
    }

    public static function isPromoter() {
        return (!isset(self::getUser()['tipo']) ? false :
            self::getUser()['tipo'] == 2);
    }
}