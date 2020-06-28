<?php

class DB {
    private static $connection = null;

    public static function deleteActivity($id) {
        return self::execute('DELETE FROM actividades WHERE id = ?',
            array($id));
    }

    public static function execute($sql, $parms = null) {
        try {
            $db = self::get();
            $query = $db->prepare($sql);
            if ($query->execute($parms)) {
                return $query;
            }
        } catch (PDOException $ex) {
            return false;
        }
        return false;
    }

    public static function get() {
        if(self::$connection === null) {
            self::$connection = new PDO('sqlite:' . __DIR__ . '/datos.db');
            self::$connection->exec('PRAGMA foreign_keys = ON;');
            self::$connection->exec('PRAGMA encoding="UTF-8";');
            self::$connection->setAttribute(PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION);
        }
        return self::$connection;
    }

    public static function insertActivity($idPromoter, $name, $type,
        $description, $price, $capacity, $startDate, $duration, $image) {
        return self::execute('INSERT INTO actividades (idempresa, nombre, '
            . 'tipo, descripcion, precio, aforo, inicio, duracion, imagen) '
            . 'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
            array($idPromoter, $name, $type, $description, $price, $capacity,
            $startDate, $duration, $image));
    }

    public static function insertTicket($idCustomer, $idActivity, $price,
        $units) {
        return self::execute('INSERT INTO tickets (idcliente, idactividad, '
            . 'precio, unidades) VALUES (?, ?, ?, ?)',
            array($idCustomer, $idActivity, $price, $units));
    }

    public static function selectAllActivities() {
        $query = self::execute('SELECT * FROM actividades');
        $query->setFetchMode(PDO::FETCH_NAMED);
        return $query->fetchAll();
    }

    public static function selectAllActivitiesByPromoter($idPromoter) {
        $query = self::execute('SELECT * FROM actividades WHERE idempresa = ?',
            array($idPromoter));
        $query->setFetchMode(PDO::FETCH_NAMED);
        return $query->fetchAll();
    }

    public static function selectAllTicketsByActivity($idActivity) {
        $query = self::execute('SELECT * FROM tickets WHERE idactividad = ?',
            array($idActivity));
        $query->setFetchMode(PDO::FETCH_NAMED);
        return $query->fetchAll();
    }

    public static function selectAllTicketsByCustomer($idCustomer) {
        $query = self::execute('SELECT * FROM tickets WHERE idcliente = ?',
            array($idCustomer));
        $query->setFetchMode(PDO::FETCH_NAMED);
        return $query->fetchAll();
    }

    public static function selectActivity($id, &$result) {
        $query = self::execute('SELECT * FROM actividades WHERE id = ?',
            array($id));
        $query->setFetchMode(PDO::FETCH_NAMED);
        $result = $query->fetchAll();
        return count($result) == 1;
    }

    public static function selectCustomer($id, &$result) {
        $query = self::execute('SELECT * FROM usuarios WHERE tipo = 3 and id = ?',
            array($id));
        $query->setFetchMode(PDO::FETCH_NAMED);
        $result = $query->fetchAll();
        return count($result) == 1;
    }

    public static function selectBusyCapacitybyActivity($idActivity) {
        $query = self::execute('SELECT sum(unidades) as "total" FROM tickets WHERE idactividad = ?',
            array($idActivity));
        $query->setFetchMode(PDO::FETCH_NAMED);
        return $query->fetchAll()[0]['total'];
    }

    public static function selectPromoter($id, &$result) {
        $query = self::execute('SELECT * FROM empresas WHERE idempresa = ?',
            array($id));
        $query->setFetchMode(PDO::FETCH_NAMED);
        $result = $query->fetchAll();
        return count($result) == 1;
    }

    public static function selectUser($username, $password, &$result) {
        $query = self::execute('SELECT * FROM usuarios WHERE cuenta = ? and clave = ?',
            array($username, md5($password)));
        $query->setFetchMode(PDO::FETCH_NAMED);
        $result = $query->fetchAll();
        return count($result) == 1;
    }

    public static function updateActivity($id, $idPromoter, $name, $type,
        $description, $price, $capacity, $startDate, $duration, $image) {
        return self::execute('UPDATE actividades SET idempresa = ?, nombre = ?, '
            . 'tipo = ?, descripcion = ?, precio = ?, aforo = ?, inicio = ?, '
            . 'duracion = ?, imagen = ? where id = ?',
            array($idPromoter, $name, $type, $description, $price, $capacity,
            $startDate, $duration, $image, $id));
    }
}