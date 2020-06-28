<?php
include_once 'business.class.php';

class View {
    public static function head() {
        Session::start();
        echo "<!DOCTYPE html>";
        echo "<html>";
        echo "<head>";
        echo "<meta charset=\"utf-8\">";
        echo "<link rel=\"stylesheet\" href=\"//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css\">";
        echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"estilos.css\">";
        echo "<script src=\"http://code.jquery.com/jquery-1.11.2.js\"></script>";
        echo "<script src=\"http://code.jquery.com/ui/1.11.4/jquery-ui.js\"></script>";
        echo "<script src=\"scripts.js\"></script>";
        echo "<title>" . Script::getTitle() . "</title>";
        echo "</head>";
        echo "<body>";
    }

    public static function header() {
        echo "<header>";
        echo "<img src='logo.png'>";
        if (UserLogged::exists()) {
            self::showUserLogged();
        } else {
            self::showUserLoginForm();
        }
        echo "<p class=\"subtitle\">Las mejores actividades en Gran Canaria</p>";
        echo "</header>";
    }

    public static function nav() {
        echo "<nav>";
        echo "<a href=\"index.php\">Actividades</a>";
        if (UserLogged::isCustomer()) {
            echo "<a href=\"customer.php?id="
                . UserLogged::getId() . "\">Mi cuenta</a>";
        } else if (UserLogged::isPromoter()) {
            echo "<a href=\"promoter.php?id="
                . UserLogged::getId() . "\">Mi cuenta</a>";
        }
        if (Script::showsActivitySearch())
            self::showActivitySearchForm();
        echo "</nav>";
    }

    public static function mainActivities() {
        Script::saveRequestUri();
        echo "<main>";
        echo "<section>";
        if (SearchFromQueryString::check()) {
            echo "<h2>Actividades resultado de la búsqueda</h2>";
            self::showActivityTable(Activity::getSearchedActivities(
                SearchFromQueryString::getSearch()));
        } else {
            echo "<h2>Próximas actividades</h2>";
            echo "<p>Descubre los mejores espectáculos, fiestas, encuentros"
                . " deportivos, culturales y de ocio en la isla</p>";
            self::showActivityTable(Activity::getAll());
        }
        echo "</section>";
        echo "</main>";
    }

    public static function mainActivity() {
        Script::saveRequestUri();
        echo "<main>";
        echo "<section>";
        if (!IdFromQueryString::check() ||
            !Activity::checkActivity(IdFromQueryString::getId())) {
            echo "<h2>Actividad no encontrada</h2>";
        } else {
            self::showActivityDetails();
            if (UserLogged::isPromoter() &&
                UserLogged::getId() == Activity::getIdPromoter()) {
                echo "<h2>Tickets vendidos</h2>";
                self::showActivityTicketTable(
                    Ticket::getAllByActivity(Activity::getId()));
            }
        }
        echo "</section>";
        echo "</main>";
    }

    public static function mainAddActivity() {
        echo "<main>";
        echo "<section>";
        if (!UserLogged::isPromoter() ||
            UserLogged::getId() != Activity::getIdPromoter()) {
            echo "<h2>Error: No puede crear actividad</h2>";
        } else {
            echo "<h2>Nueva actividad</h2>";
            self::showAddActivityForm();
        }
        echo "</section>";
        echo "</main>";
    }

    public static function mainBuyTicket() {
        echo "<main>";
        echo "<section>";
        if (!UserLogged::isCustomer() ||
            !IdFromQueryString::check() ||
            !Activity::checkActivity(IdFromQueryString::getId()) ||
            !Promoter::checkPromoter(Activity::getIdPromoter())) {
            echo "<h2>Error: Faltan datos para poder realizar la compra</h2>";
        } else {
            echo "<h2>Comprar ticket</h2>";
            self::showBuyTicketForm();
        }
        echo "</section>";
        echo "</main>";
    }

    public static function mainCustomer() {
        echo "<main>";
        echo "<section>";
        if (!IdFromQueryString::check() ||
            !Customer::checkCustomer(IdFromQueryString::getId())) {
            echo "<h2>Usuario no encontrado</h2>";
        } else {
            if ((!UserLogged::exists()) ||
                (UserLogged::isCustomer() &&
                UserLogged::getId() != Customer::getId())) {
                echo "<h2>Información no disponible</h2>";
            } else {
                self::showCustomerDetails();
                if (UserLogged::getId() == Customer::getId()) {
                    echo "<h2>Tickets comprados</h2>";
                    self::showCustomerTicketTable(
                        Ticket::getAllByCustomer(Customer::getId()));
                }
            }
        }
        echo "</section>";
        echo "</main>";
    }

    public static function mainEditActivity() {
        echo "<main>";
        echo "<section>";
        if (!UserLogged::isPromoter() ||
            !IdFromQueryString::check() ||
            !Activity::checkActivity(IdFromQueryString::getId()) ||
            Activity::getIdPromoter() != UserLogged::getId()) {
            echo "<h2>Error: No puede modificar la actividad</h2>";
            echo "<p>" . Activity::getIdPromoter() . " " . UserLogged::getId() . "</p>";
        } else {
            echo "<h2>Modificar actividad</h2>";
            self::showEditActivityForm();
        }
        echo "</section>";
        echo "</main>";
    }

    public static function mainPromoter() {
        Script::saveRequestUri();
        echo "<main>";
        echo "<section>";
        if (!IdFromQueryString::check() ||
            !Promoter::checkPromoter(IdFromQueryString::getId())) {
            echo "<h2>Promotor no encontrado</h2>";
        } else {
            self::showPromoterDetails();
            echo "<h2>Actividades</h2>";
            self::showActivityTable(
                Activity::getAllByPromoter(Promoter::getId()));
            if (UserLogged::isPromoter() &&
                UserLogged::getId() == Activity::getIdPromoter()) {
                self::showAddActivityButton();
            }
        }
        echo "</section>";
        echo "</main>";
    }

    public static function footer() {
        echo "<footer>";
        echo "<p>Práctica 2 de Programación IV. Curso 2019-2020</p>";
        echo "</footer>";
        echo "</body>";
        echo "</html>";
    }

    private static function getActivityLink() {
        return "<a href='activity.php?id=" . Activity::getId() . "'>"
            . Activity::getName() . "</a>";
    }

    private static function getBuyActivityButton() {
        return "<a href=\"buyTicket.php?id=" . Activity::getId()
            . "\">Comprar</a>";
    }

    private static function getCustomerLink() {
        return "<a href='customer.php?id=" . Customer::getId() . "'>"
            . Customer::getName() . "</a>";
    }


    private static function getDatetimePlaceholderPattern() {
        return "placeholder=\"__/__/____ --:--\" "
            . "pattern=\"(0[1-9]|[12][0-9]|3[0-1])/(0[1-9]|1[0-2])/([0-9]{4}) "
            . "([0-1][0-9]|2[0-3]):([0-5][0-9])\"";
    }

    private static function getDeleteActivityButton() {
        return "<a href=\"javascript:deleteActivity(" . Activity::getId()
            . ", '" . Activity::getName() . "')\">&nbsp;Eliminar&nbsp;</a>";
    }

    private static function getEditActivityButton() {
        return "<a href=\"editActivity.php?id=" . Activity::getId()
            . "\">Modificar</a>";
    }

    private static function getPromoterLink() {
        return "<a href='promoter.php?id=" . Promoter::getId() . "'>"
            . Promoter::getName() . "</a>";
    }

    private static function getTimePlaceholderPattern() {
        return "placeholder=\"--:--\" "
            . "pattern=\"([0-1][0-9]|2[0-3]):([0-5][0-9])\"";
    }

    public static function showActivityTable($activities) {
        echo "<table class=\"list_table\">";
        echo "<tr>";
        echo "<th>Nombre</th>";
        echo "<th>Tipo</th>";
        echo "<th>Fecha</th>";
        echo "<th>Precio</th>";
        echo "<th>Imagen</th>";
        if ((UserLogged::isCustomer()) ||
            (UserLogged::isPromoter() &&
            Script::showsPromoterOpctions()))
            echo "<th>Opciones</th>";
        echo "</tr>";
        foreach($activities as $activity) {
            Activity::setActivity($activity);
            echo "<tr id=\"activity" . Activity::getId() . "\">";
            echo "<td>" . self::getActivityLink() . "</td>";
            echo "<td>" . Activity::getType() . "</td>";
            echo "<td>" . date('d/m/Y', Activity::getStartDate()) . "</td>";
            echo "<td>" . Euro::valueOf(Activity::getPrice()) . "</td>";
            echo "<td><img src='" . Image::toBase64(Activity::getImage())
                . "'></td>";
            if (UserLogged::isCustomer())
                echo "<td><div class=\"small_button\">"
                    . self::getBuyActivityButton() . "</div></td>";
            if (UserLogged::isPromoter() && Script::showsPromoterOpctions())
                echo "<td><div class=\"small_button\">"
                    . self::getEditActivityButton() . "<br>"
                    . self::getDeleteActivityButton() . "</div></td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    private static function showActivityDetails() {
        echo "<h2>" . Activity::getName() . "</h2>";
        echo "<img src='" . Image::toBase64(Activity::getImage()) . "'>";
        echo "<table class=\"details_table\">";
        echo "<tr><th>Promotor</th>";
        echo "<td>";
        if (Promoter::checkPromoter(Activity::getIdPromoter()))
            echo self::getPromoterLink();
        echo "</td></tr>";
        echo "<tr><th>Tipo</th>";
        echo "<td>" . Activity::getType() . "</td></tr>";
        echo "<tr><th>Descripción</th>";
        echo "<td>" . Activity::getDescription() . "</td></tr>";
        echo "<tr><th>Precio</th>";
        echo "<td>" . Euro::valueOf(Activity::getPrice(), true) . "</td></tr>";
        echo "<tr><th>Aforo</th>";
        echo "<td>" . Activity::getCapacity()
            . (Activity::getBusyCapacity() > 0 ? " (disponible: "
            . Activity::getFreeCapacity() . ")" : "")
            . "</td></tr>";
        echo "<tr><th>Inicio</th>";
        echo "<td>" . date('d/m/Y H:i', Activity::getStartDate())
            . "</td></tr>";
        echo "<tr><th>Duración</th>";
        echo "<td>" . Seconds::toHoursAndMinutes(Activity::getDuration())
            . "</td></tr>";
        echo "</table>";
    }

    private static function showActivitySearchForm() {
        echo "<form class=\"search\" action=\"/index.php\" method=\"get\">";
        echo "<input id='searchActivity' onkeydown='checkPressed()' type=\"text\""
            . " placeholder=\"buscar...\" name=\"search\""
            . (SearchFromQueryString::check() ? " value=\""
            . SearchFromQueryString::getSearch() . "\"" : "") .
            ">";
        echo "<input type=\"submit\" value=\"Buscar\">";
        echo "</form>";
    }

    private static function showActivityTicketTable($tickets) {
        echo "<table class=\"list_table\">";
        echo "<tr>";
        echo "<th>Comprador</th>";
        echo "<th>Precio</th>";
        echo "<th>Unidades</th>";
        echo "</tr>";
        foreach($tickets as $ticket) {
            Ticket::setTicket($ticket);
            echo "<tr>";
            echo "<td>";
            if (Customer::checkCustomer(Ticket::getCustomer()))
                echo self::getCustomerLink();
            echo "</td>";
            echo "<td>" . Euro::valueOf(Ticket::getPrice()) . "</td>";
            echo "<td>" . Ticket::getUnits() . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    private static function showAddActivityButton() {
        echo "<div class=\"big_button\">";
        echo "<a href=\"addActivity.php\">Crear actividad</a>";
        echo "</div>";
    }

    private static function showAddActivityForm() {
        echo "<form id=\"addActivityForm\" class=\"details_table\""
            . " action=\"javascript:addActivity('" . Script::getRequestUri()
            . "')\" method=\"post\" enctype=\"multipart/form-data\">";
        echo "<table>";
        echo "<tr><th><label for=\"name\">Nombre</label></th>";
        echo "<td><input type=\"text\" id=\"name\" name=\"name\">"
            . "<span class=\"error\" id=\"error_name\"></span></td></tr>";
        echo "<tr><th><label for=\"type\">Tipo</label></th>";
        echo "<td><input type=\"text\" id=\"type\" name=\"type\">"
            . "<span id=\"error_type\"></span></td></tr>";
        echo "<tr><th><label for=\"description\">Descripción</label></th>";
        echo "<td><input type=\"text\" id=\"description\" name=\"description\">"
            . "<span id=\"error_description\"></span></td></tr>";
        echo "<tr><th><label for=\"price\">Precio</label></th>";
        echo "<td><input type=\"number\" id=\"price\" name=\"price\">"
            . "<span id=\"error_price\"></span></td></tr>";
        echo "<tr><th><label for=\"capacity\">Aforo</label></th>";
        echo "<td><input type=\"number\" id=\"capacity\" name=\"capacity\">"
            . "<span id=\"error_capacity\"></span></td></tr>";
        echo "<tr><th><label for=\"startDate\">Inicio</label></th>";
        echo "<td><input type=\"text\" id=\"startDate\" name=\"startDate\""
            . " " . self::getDatetimePlaceholderPattern() . ">"
            . "<span id=\"error_startDate\"></span>"
            . "<br><span>Día y hora en formato 99/99/9999 00:00</span>"
            . "</td></tr>";
        echo "<tr><th><label for=\"duration\">Duración</label></th>";
        echo "<td><input type=\"text\" id=\"duration\" name=\"duration\""
            . " " . self::getTimePlaceholderPattern() . ">"
            . "<span id=\"error_duration\"></span>"
            . "<br><span>Horas y minutos en formato 00:00</span>"
            . "</td></tr>";
        echo "<tr><th><label for=\"image\">Imagen</label></th>";
        echo "<td><input type=\"file\" id=\"image\" name=\"image\""
            . " accept=\"image/jpeg, image/png\">"
            . "<span id=\"error_image\"></span>"
            . "<br><span>Formato JPEG o PNG y tamaño máximo 60 KB</span>"
            . "</td></tr>";
        echo "</table>";
        echo "<input type=\"submit\" value=\"Crear\">";
        echo "</form>";
    }

    private static function showBuyTicketForm() {
        echo "<form id=\"buyTicketForm\" class=\"details_table\""
            . " action=\"javascript:buyTicket('" . Script::getRequestUri()
            . "')\" method=\"post\">";
        echo "<table>";
        echo "<tr><th>Actividad</th>";
        echo "<td>" . self::getActivityLink() . "</td></tr>";
        echo "<tr><th>Promotor</th>";
        echo "<td>" . self::getPromoterLink() . "</td></tr>";
        echo "<tr><th>Precio</th>";
        echo "<td>" . Euro::valueOf(Activity::getPrice(), true) . "</td></tr>";
        echo "<tr><th><label for=\"units\">Unidades</label></th>";
        echo "<td><input type=\"number\" id=\"units\" name=\"units\""
            . " value=\"1\"><span id=\"error_units\"></span></td></tr>";
        echo "</table>";
        echo "<input type=\"submit\" value=\"Comprar\">";
        echo "</form>";
    }

    private static function showCustomerDetails() {
        echo "<h2>" . Customer::getName() . "</h2>";
        echo "<table class=\"details_table\">";
        echo "<tr><th>Correo electrónico</th>";
        echo "<td>" . Customer::getEmail() . "</td></tr>";
        echo "<tr><th>Dirección</th>";
        echo "<td>" . Customer::getAddress() . "</td></tr>";
        echo "<tr><th>Población</th>";
        echo "<td>" . Customer::getTown() . "</td></tr>";
        echo "<tr><th>Teléfono</th>";
        echo "<td>" . Customer::getPhone() . "</td></tr>";
        echo "</table>";
    }

    private static function showCustomerTicketTable($tickets) {
        echo "<table class=\"list_table\">";
        echo "<tr>";
        echo "<th>Actividad</th>";
        echo "<th>Precio</th>";
        echo "<th>Unidades</th>";
        echo "</tr>";
        foreach($tickets as $ticket) {
            Ticket::setTicket($ticket);
            echo "<tr>";
            echo "<td>";
            if (Activity::checkActivity(Ticket::getActivity()))
                echo self::getActivityLink();
            echo "</td>";
            echo "<td>" . Euro::valueOf(Ticket::getPrice()) . "</td>";
            echo "<td>" . Ticket::getUnits() . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    private static function showEditActivityForm() {
        echo "<form id=\"editActivityForm\" class=\"details_table\""
            . " action=\"javascript:editActivity('" . Script::getRequestUri()
            . "')\" method=\"post\" enctype=\"multipart/form-data\">";
        echo "<table>";
        echo "<tr><th><label for=\"name\">Nombre</label></th>";
        echo "<td><input type=\"text\" id=\"name\" name=\"name\" value=\""
            . Activity::getName() . "\">"
            . "<span id=\"error_name\"></span></td></tr>";
        echo "<tr><th><label for=\"type\">Tipo</label></th>";
        echo "<td><input type=\"text\" id=\"type\" name=\"type\" value=\""
            . Activity::getType() . "\">"
            . "<span id=\"error_type\"></span></td></tr>";
        echo "<tr><th><label for=\"description\">Descripción</label></th>";
        echo "<td><input type=\"text\" id=\"description\" name=\"description\""
            . " value=\"" . Activity::getDescription() . "\">"
            . "<span id=\"error_description\"></span></td></tr>";
        echo "<tr><th><label for=\"price\">Precio</label></th>";
        echo "<td><input type=\"number\" id=\"price\" name=\"price\""
            . " step=\"0.01\" value=\"" . Activity::getPrice()
            . "\"><span id=\"error_price\"></span></td></tr>";
        echo "<tr><th><label for=\"capacity\">Aforo</label></th>";
        echo "<td><input type=\"number\" id=\"capacity\" name=\"capacity\""
            . " value=\"" . Activity::getCapacity()
            . "\"><span id=\"error_capacity\"></span></td></tr>";
        echo "<tr><th><label for=\"startDate\">Inicio</label></th>";
        echo "<td><input type=\"text\" id=\"startDate\" name=\"startDate\""
            . " " . self::getDatetimePlaceholderPattern() . " value=\""
            . date('d/m/Y H:i', Activity::getStartDate()) . "\">"
            . "<br><span>Día y hora en formato 99/99/9999 00:00</span>"
            . "</td></tr>";
        echo "<tr><th><label for=\"duration\">Duración</label></th>";
        echo "<td><input type=\"text\" id=\"duration\" name=\"duration\""
            . " " . self::getTimePlaceholderPattern() . " value=\""
            . Seconds::toHHMM(Activity::getDuration()) . "\">"
            . "<span id=\"error_duration\"></span>"
            . "<br><span>Horas y minutos en formato 00:00</span>"
            . "</td></tr>";
        echo "<tr><th><label for=\"image\">Imagen</label></th>";
        echo "<td><input type=\"file\" id=\"image\" name=\"image\""
            . " accept=\"image/jpeg, image/png\">"
            . "<span id=\"error_image\"></span>"
            . "<br><span>Formato JPEG o PNG y tamaño máximo 60 KB</span>"
            . "</td></tr>";
        echo "</table>";
        echo "<input type=\"submit\" value=\"Guardar\">";
        echo "</form>";
    }

    private static function showPromoterDetails() {
        echo "<h2>" . Promoter::getName() . "</h2>";
        echo "<img src='" . Image::toBase64(Promoter::getImage()) . "'>";
        echo "<table class=\"details_table\">";
        echo "<tr><th>Descripción</th>";
        echo "<td>" . Promoter::getDescription() . "</td></tr>";
        echo "<tr><th>Contacto</th>";
        echo "<td>" . Promoter::getContact() . "</td></tr>";
        echo "</table>";
    }

    private static function showUserLogged() {
        echo "<div class=\"user\">";
        echo "<span>" . UserLogged::getName();
        if (UserLogged::isAdmin()) {
            echo " (Administrador)</span>";
        } else if (UserLogged::isPromoter()) {
            echo " (Promotor)</span>";
        } else { //isCustomer
            echo "</span>";
        }
		echo "<a class=\"botton_logout\" href=\"doLogout.php\">Cerrar sesión</a>";
        echo "</div>";
    }

    private static function showUserLoginForm() {
        echo "<div class=\"user\">";
        echo "<form action=\"doLogin.php\" method=\"post\">";
        echo "<input type=\"text\""
            . " placeholder=\"Usuario\" name=\"username\""
            . (UserDataFromPost::check() ? " value=\""
            . UserDataFromPost::getUsername() . "\"" : "")
            . " required>";
        echo "<input type=\"password\""
            . " placeholder=\"Contraseña\" name=\"password\" required>";
        echo "<input type=\"submit\" value=\"Iniciar sesión\">";
        echo "</form>";
        echo (UserDataFromPost::check() ? "<p>Usuario o contraseña"
            . " incorrecta</p>" : "");
        echo "</div>";
        if (UserDataFromPost::check())
            UserDataFromPost::clean();
    }
}