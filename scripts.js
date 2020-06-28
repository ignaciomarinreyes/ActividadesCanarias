function addActivity(requesturi) {
    var form_data = new FormData($('#addActivityForm')[0]);
    $.ajax({
        url: "doAddActivity.php",
        type: "POST",
        data: form_data,
        processData: false,
        contentType: false,
        dataType: "json",
        success: function(response) {
            if(response.saved) {
                window.location.replace(requesturi);
            } else {
                var inputs = {name:"name", type:"type",
                        description:"description", price:"price",
                        capacity:"capacity", startDate:"startDate",
                        duration:"duration", image:"image"};
                for (var j in inputs) {
                    $('#error_' + inputs[j]).text("");
                }
                for (var i in response.errors) {
                    if (response.errors[i].element == 'form') {
                        messageDialog('Error', response.errors[i].error);
                    } else {
                        $('#error_' + response.errors[i].element).text(response.errors[i].error);
                    }
                }
            }
        },
        error: function(response) {
            messageDialog('Error', response.error);
        }
    });
}

function buyTicket(requesturi) {
    var form_data = new FormData($('#buyTicketForm')[0]);
    $.ajax({
        url: "doBuyTicket.php",
        type: "POST",
        data: form_data,
        processData: false,
        contentType: false,
        dataType: "json",
        success: function(response) {
            if(response.saved) {
                window.location.replace(requesturi);
            } else {
                var inputs = {units:"units"};
                for (var j in inputs) {
                    $('#error_' + inputs[j]).text("");
                }
                for (var i in response.errors) {
                    if (response.errors[i].element == 'form') {
                        messageDialog('Error', response.errors[i].error);
                    } else {
                        $('#error_' + response.errors[i].element).text(response.errors[i].error);
                    }
                }
            }
        },
        error: function(response) {
            messageDialog('Error', response.error);
        }
    });
}

function editActivity(requesturi) {
    var form_data = new FormData($('#editActivityForm')[0]);
    $.ajax({
        url: "doEditActivity.php",
        type: "POST",
        data: form_data,
        processData: false,
        contentType: false,
        dataType: "json",
        success: function(response) {
            if(response.saved) {
                window.location.replace(requesturi);
            } else {
                var inputs = {name:"name", type:"type",
                        description:"description", price:"price",
                        capacity:"capacity", startDate:"startDate",
                        duration:"duration", image:"image"};
                for (var j in inputs) {
                    $('#error_' + inputs[j]).text("");
                }
                for (var i in response.errors) {
                    if (response.errors[i].element == 'form') {
                        messageDialog('Error', response.errors[i].error);
                    } else {
                        $('#error_' + response.errors[i].element).text(response.errors[i].error);
                    }
                }
            }
        },
        error: function(response) {
            messageDialog('Error', response.error);
        }
    });
}

function confirmDelete(name, action){
    $dialog = $('<div></div>');
    $dialog.text("¿Desea eliminar la actividad '" + name + "'?");
    $dialog.dialog({
        title: 'Confirmar eliminar',
        width: 400,
        modal: true,
        buttons: {
            'Sí': function() {$dialog.dialog('close'); action();},
            'No': function() {$dialog.dialog('close');}
        }
    });
}

function deleteActivity(id, name) {
    function deleteAjax() {
        $.ajax({
            url: "doDeleteActivity.php",
            type: "POST",
            data: "id=" + id,
            dataType: "json",
            success: function(response) {
                if(response.deleted) {
                    $('#activity' + id).remove();
                } else {
                    messageDialog('Error', response.error);
                }
            },
            error: function(response) {
                messageDialog('Error', response.error);
            }
        });
    }
    confirmDelete(name, deleteAjax);
}

function messageDialog(title, message) {
    $dialog=$('<div></div>');
    $dialog.text(message);
    $dialog.dialog({
        title: title,
        width: 400,
        modal: true,
        buttons: {
            'Aceptar': function() {$dialog.dialog('close');}
        }
    });
}

oldText = '';
id = null;
function checkPressed() {
    if(id !== null) {
        id = clearTimeout(id);
    }
    setTimeout(updateActivity, 100);
}

function updateActivity(){
    var text = document.querySelector('#searchActivity').value;
    if(text.length >= 3) {
        if(oldText !== text) {
            oldText = text;
            ajax(text);
        }
    }else{
        // Backspace control
        oldText = text;
        ajax("");
    }
}

function ajax(text) {
  // De esta forma se obtiene la instancia del objeto XMLHttpRequest
  if(window.XMLHttpRequest) {
    connection = new XMLHttpRequest();
  }
  else if(window.ActiveXObject) {
    connection = new ActiveXObject("Microsoft.XMLHTTP");
  }

  var param = text;

  // Preparando la función de respuesta
  connection.onreadystatechange = response;

  // Realizando la petición HTTP con método POST
  connection.open('POST', 'updateTable.php');
  connection.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  connection.send("valueTextArea=" + param);
}

function response() {
  if(connection.readyState == 4) {
    var response = connection.responseText;
    var tableNode = document.querySelector('.list_table');
    var tableNodeParent = tableNode.parentNode;
    tableNodeParent.removeChild(tableNode);

    var newNodeTable = document.createElement("table");
    var attribute = document.createAttribute("class");
    attribute.value = "list_table";
    newNodeTable.setAttributeNode(attribute);
    newNodeTable.innerHTML = response;
    tableNodeParent.appendChild(newNodeTable);

  }
}