<?php
session_start();

include('conexion.php');

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: shop.html");
    exit();
}

$user_id = $_SESSION['user_id'];
// Obtener el nombre del usuario (puedes modificarlo según tu estructura de base de datos)
$sql_select_user = "SELECT nombre FROM usuarios WHERE id = $user_id";
$result_user = $conn->query($sql_select_user);
$user_name = ($result_user->num_rows > 0) ? $result_user->fetch_assoc()['nombre'] : "Usuario Desconocido";
// Manejar solicitudes de cambios en el inventario
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: shop.html");
    exit();
}
// Función para agregar un producto al inventario
function agregarProductoAlInventario($conn, $user_id, $product_name, $initial_quantity, $initial_comment) {
    $sql_insert_inventory = "INSERT INTO inventarios (id_usuario, nombre_producto, cantidad, fecha_movimiento, comentario) VALUES ($user_id, '$product_name', $initial_quantity, NOW(), '$initial_comment')";
    $conn->query($sql_insert_inventory);
}

// Función para agregar un movimiento al historial
function agregarMovimientoAlHistorial($conn, $user_id, $accion, $product_name, $quantity_change, $comment) {
    $sql_insert_history = "INSERT INTO historial_inventario (id_usuario, accion, nombre_producto, cantidad, fecha_movimiento, comentario) VALUES ($user_id, '$accion', '$product_name', $quantity_change, NOW(), '$comment')";
    $conn->query($sql_insert_history);
}

// Manejar solicitudes de cambios en el inventario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['insert'])) {
        $product_name = $_POST['product_name'];
        $quantity_change = $_POST['quantity'];
        $comment = $_POST['comment'];

        // Obtener la cantidad actual del producto en el inventario
        $sql_select_quantity = "SELECT cantidad FROM inventarios WHERE id_usuario = $user_id AND nombre_producto = '$product_name'";
        $result_quantity = $conn->query($sql_select_quantity);
        $current_quantity = ($result_quantity->num_rows > 0) ? $result_quantity->fetch_assoc()['cantidad'] : 0;

        // Calcular la nueva cantidad después del cambio
        $new_quantity = $current_quantity + $quantity_change;

        // Actualizar el inventario
        $sql_update_inventory = "UPDATE inventarios SET cantidad = $new_quantity WHERE id_usuario = $user_id AND nombre_producto = '$product_name'";
        $conn->query($sql_update_inventory);

        // Agregar un movimiento al historial
        $accion = ($quantity_change > 0) ? 'Inserción' : 'Eliminación';
        agregarMovimientoAlHistorial($conn, $user_id, $accion, $product_name, $quantity_change, $comment);
    }
    if (isset($_POST['insertProductSubmit'])) {
        // Obtener datos del nuevo producto
        $new_product_name = $_POST['product_name'];
        $initial_quantity = $_POST['initial_quantity'];
        $initial_comment = $_POST['initial_comment'];

        // Insertar nuevo producto en el inventario
        agregarProductoAlInventario($conn, $user_id, $new_product_name, $initial_quantity, $initial_comment);
        // Agregar un movimiento al historial
        $accion = 'Inserción';
        agregarMovimientoAlHistorial($conn, $user_id, $accion, $new_product_name, $initial_quantity, $initial_comment);
    }
    // Otros casos de modificaciones en el inventario pueden ir aquí
}

// Obtener el historial de cambios del usuario
$sql_select_history = "SELECT * FROM historial_inventario WHERE id_usuario = $user_id";
$result_history = $conn->query($sql_select_history);

// Obtener los elementos del inventario del usuario
$sql_select_inventory = "SELECT DISTINCT nombre_producto FROM inventarios WHERE id_usuario = $user_id";
$result_inventory = $conn->query($sql_select_inventory);

?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor</title>
    <!-- Agrega la referencia a Bootstrap -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"> <meta name="keywords" content="">
      <meta name="description" content="">
      <meta name="author" content="">
      <!-- bootstrap css -->
      <link rel="stylesheet" href="css/bootstrap.min.css">
      <!-- style css -->
      <link rel="stylesheet" href="css/style.css">
      <!-- Responsive-->
      <link rel="stylesheet" href="css/responsive.css">
      <!-- fevicon -->
      <link rel="icon" href="images/fevicon.png" type="image/gif" />
      <!-- Scrollbar Custom CSS -->
      <link rel="stylesheet" href="css/jquery.mCustomScrollbar.min.css">
      <!-- Tweaks for older IEs-->
      <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.min.css" media="screen">
 
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
</head>
<body>
<style>
        /* Estilos CSS para cambiar el color de fondo de las filas y la alerta parpadeante */
        .bg-danger {
            background-color: #ffdddd !important; /* Cambia este color según tus preferencias */
        }

        @keyframes blink {
            50% {
                opacity: 0;
            }
        }

        .blink {
            animation: blink 1s infinite;
        }


    </style>

</head>
<body class="main-layout position_head">
      <!-- loader  -->
      <div class="loader_bg">
         <div class="loader"><img src="images/loading.gif" alt="#" /></div>
      </div>

<!-- Encabezado -->
<!-- Encabezado -->
<header>
         <!-- header inner -->
         <div class="header">
            <div class="container-fluid">
               <div class="row">
                  <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col logo_section">
                     <div class="full">
                        <div class="center-desk">
                           <div class="logo">
                              <a href="index.html"><img src="Logom.PNG" alt="#" style="width: 80px; height: 80px;"  /></a>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="col-xl-9 col-lg-9 col-md-9 col-sm-9">
                     <nav class="navigation navbar navbar-expand-md navbar-dark ">
                        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExample04" aria-controls="navbarsExample04" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarsExample04">
                           <ul class="navbar-nav mr-auto">
                              <li class="nav-item active">
                                 <a class="nav-link" href="index.html">Inicio</a>
                              </li>
                              <li class="nav-item">
                                 <a class="nav-link" href="about.html">Acerca de</a>
                              </li>                
                              <li class="nav-item">
                                 <a class="nav-link" href="shop.html">Gestionar</a>
                              </li>
                              <li class="nav-item">
                                 <a class="nav-link" id="contactLink" href="#">Contacto</a>
                              </li>
                                                   
                           </ul>
                        </div>
                     </nav>
                  </div>
               </div>
            </div>
         </div>
      </header>

<div class="modal fade" id="contactModal" tabindex="-1" role="dialog" aria-labelledby="contactModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content" style="background: transparent; border: none;">
         <div class="modal-body p-0 d-flex justify-content-center align-items-center">
            <img src="Whatsapp.png" alt="Imagen de Contacto" class="img-fluid">
         </div>
      </div>
   </div>
</div>
<script>
   document.addEventListener("DOMContentLoaded", function () {
      var contactLink = document.getElementById("contactLink");
      contactLink.addEventListener("click", function () {
         $('#contactModal').modal('show');
      });
   });
</script>



<!-- JavaScript para activar el modal -->
<script>
   document.addEventListener("DOMContentLoaded", function () {
      var contactLink = document.getElementById("contactLink");
      contactLink.addEventListener("click", function () {
         $('#contactModal').modal('show');
      });
   });
</script>
<br>

<body class="main-layout">
 
    <div>
        <?php
        echo "<h1>Bienvenido, $user_name (ID: $user_id)</h1> ";
        ?>
           <form action="Table.php" method="post">
        <button type="submit" class="btn btn-danger" name="logout">Cerrar Sesión</button>
    </form>
    </div>
 <br>
 <br>

    <!-- Botón para abrir el modal de inserción -->
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#insertModal">
        Insertar Cambio
    </button>
<!-- Botón para abrir el modal de inserción de producto -->
<button type="button" class="btn btn-success" data-toggle="modal" data-target="#insertProductModal">
    Agregar Producto al Inventario
</button>
<!-- Botón para abrir el modal de historial -->
<button type="button" class="btn btn-info" data-toggle="modal" data-target="#historyModal">
    Ver Historial
</button>

<br>
<br>
<br>
 <!-- Modal de Cambio -->
<!-- Modal de Cambio -->
<div class="modal fade" id="insertModal" tabindex="-1" role="dialog" aria-labelledby="insertModalLabel" aria-hidden="true">
    <form action="Table.php" method="post">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="insertModalLabel">Insertar Cambio</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Campos del formulario de inserción -->
                    <div class="form-group">
                        <label for="product_name">Nombre del Producto:</label>
                        <select id="product_name" name="product_name" class="form-control">
                            <?php
                            while ($row_inventory = $result_inventory->fetch_assoc()) {
                                echo "<option value='" . $row_inventory['nombre_producto'] . "'>" . $row_inventory['nombre_producto'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="quantity">Cantidad:</label>
                        <input type="number" id="quantity" name="quantity" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="comment">Comentario:</label>
                        <input type="text" id="comment" name="comment" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary" name="insert">Insertar</button>
                </div>
            </div>
        </div>
    </form>
</div>


<!-- Modal de Inserción de Producto -->
<div class="modal fade" id="insertProductModal" tabindex="-1" role="dialog" aria-labelledby="insertProductModalLabel" aria-hidden="true">
    <form action="Table.php" method="post">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="insertProductModalLabel">Agregar Nuevo Producto al Inventario</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Campos del formulario para agregar nuevo producto al inventario -->
                    <div class="form-group">
                        <label for="product_name">Nombre del Nuevo Producto:</label>
                        <input type="text" id="product_name" name="product_name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="initial_quantity">Cantidad Inicial:</label>
                        <input type="number" id="initial_quantity" name="initial_quantity" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="initial_comment">Comentario Inicial:</label>
                        <input type="text" id="initial_comment" name="initial_comment" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-success" name="insertProductSubmit">Agregar Producto</button>
                </div>
            </div>
        </div>
    </form>
</div>
<style>
    h1 {
        font-size: 2em;
        color: #343a40;
        margin-bottom: 20px;
        text-align: center;
        text-transform: uppercase;
    }

    .table-container {
        max-height: 550px;
        overflow-y: auto;
        margin: 20px;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .table-container table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0;
    }

    .table-container th,
    .table-container td {
        padding: 12px;
        text-align: left;
        border-right: 2px solid #343a40; /* Bordes derechos con color del encabezado */
        border-left: 2px solid #343a40; /* Bordes izquierdos con color del encabezado */
        border-bottom: 2px solid #343a40; /* Bordes inferiores con color del encabezado */
    }

    .table-container th {
        background-color: #343a40;
        color: #ffffff;
    }

    .bg-danger {
        background-color: #ffdddd !important;
    }

    .bg-friendly {
        background-color: #f0f8ff;
    }
</style>

<!-- Mostrar el inventario del usuario en una tabla -->
<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th colspan="4">
                    <center><h1 style="color:#ffffff;">Inventario</h1></center>
                </th>
            </tr>
            <tr>
                <th>Nombre del Producto</th>
                <th>Cantidad</th>
                <th>Fecha de Movimiento</th>
                <th>Comentario</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Obtener el inventario del usuario
            $sql_select_inventory = "SELECT * FROM inventarios WHERE id_usuario = $user_id";
            $result_inventory = $conn->query($sql_select_inventory);

            while ($row_inventory = $result_inventory->fetch_assoc()) {
                // Aplica la clase 'bg-danger' solo si la cantidad es 10 o menos
                $rowClass = ($row_inventory['cantidad'] <= 10) ? 'bg-danger' : 'bg-friendly';

                echo "<tr class='$rowClass'>";
                echo "<td>" . $row_inventory['nombre_producto'] . "</td>";
                echo "<td>" . $row_inventory['cantidad'] . "</td>";
                echo "<td>" . $row_inventory['fecha_movimiento'] . "</td>";
                echo "<td>" . $row_inventory['comentario'] . "</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>
<br>



<!-- Modal de Historial -->
<style>
    #historyModalLabel {
        color: #343a40; /* Color del texto para el título del modal */
    }

    #historyModal .modal-content {
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    #historyModal .modal-body {
        max-height: 500px;
        overflow-y: auto;
    }

    #historyModal .table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0;
    }

    #historyModal th,
    #historyModal td {
        padding: 12px;
        text-align: left;
        border-right: 2px solid #343a40; /* Bordes derechos con color del encabezado */
        border-left: 2px solid #343a40; /* Bordes izquierdos con color del encabezado */
        border-bottom: 2px solid #343a40; /* Bordes inferiores con color del encabezado */
    }

    #historyModal th {
        background-color: #343a40; /* Color de fondo para las celdas del encabezado */
        color: #ffffff; /* Color del texto para las celdas del encabezado */
    }

    #historyModal .modal-footer {
        border-top: 2px solid #343a40; /* Borde superior con color del encabezado */
    }

    #historyModal .btn-secondary {
        background-color: #343a40; /* Color de fondo para el botón secundario */
        color: #ffffff; /* Color del texto para el botón secundario */
    }
</style>

<div class="modal fade" id="historyModal" tabindex="-1" role="dialog" aria-labelledby="historyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="historyModalLabel">Historial de Cambios</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Mostrar el historial de cambios del usuario en una tabla en el modal -->
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Acción</th>
                            <th>Nombre del Producto</th>
                            <th>Cantidad</th>
                            <th>Fecha de Movimiento</th>
                            <th>Comentario</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $rows = array_reverse($result_history->fetch_all(MYSQLI_ASSOC));
                        foreach ($rows as $row) {
                            echo "<tr>";
                            echo "<td>" . $row['id'] . "</td>";
                            echo "<td>" . $row['accion'] . "</td>";
                            echo "<td>" . $row['nombre_producto'] . "</td>";
                            echo "<td>" . $row['cantidad'] . "</td>";
                            echo "<td>" . $row['fecha_movimiento'] . "</td>";
                            echo "<td>" . $row['comentario'] . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<style>
        body {
            min-height: 100vh;
            margin-bottom: 60px; /* Ajusta el valor según la altura de tu footer */
        }

        footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            background-color: #343a40; /* Cambia este color según tus preferencias */
            color: #ffffff; /* Cambia este color según tus preferencias */
            padding: 10px;
        }
    </style>
<footer class="bg-dark text-white text-center py-3">
    <p>&copy; 2024 Maninventory - Gestor de inventarios-Derechos reservados.</p>
</footer>


    <!-- Formulario para permitir al usuario realizar cambios en el inventario -->
      <!-- <h2>Realizar Cambio</h2>-->
                <!-- Agrega la referencia a jQuery y Popper.js (necesarios para Bootstrap) y Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <script src="js/jquery.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/jquery-3.0.0.min.js"></script>
      <!-- sidebar -->
    <script src="js/jquery.mCustomScrollbar.concat.min.js"></script>
    <script src="js/custom.js"></script>

    </body>

</html>

<?php
$conn->close();
?>
