<?php
#Comprobador de inicio de sesión
#Comprobamos nuevamente que el usuario tenga la sesión iniciada.
include('PDOconn.php');
include('essentials.php');
session_start();
$ban     = false;
$tp_user = 0;
if(!isset($_SESSION['username'])){
    header("Location: ../index.html");
    exit;
}
else{
    $user = $_SESSION['username'];
    if(ctype_digit($user)){
        $tp = "documento";
        $tp_user = 1;
    }
    else{
        $tp = "email";
        $tp_user = 2;
    }

    $query = "SELECT nombre, documento FROM tbl_usuario where $tp = :user";
    $stmt = $pdo->prepare($query);

    if($tp_user == 1){
        $stmt->bindParam(':user', $user, PDO::PARAM_INT);
    }
    else{
        $stmt->bindParam(':user', $user, PDO::PARAM_STR);
    }
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if(count($result) > 0){
        $row = $result[0];
        $nombre_usuario = $row['nombre'];
        $documento = $row['documento'];
        $ban = true;
        echo "Bienvenido, $nombre_usuario!";
        echo '<form action="logout.php" method="post">';
        echo '<input type="submit" value="Cerrar Sesión">';
        echo '</form>';
    }
    else{
        echo 'Error al obtener el nombre del usuario.';
    }
}
if($ban){

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $id_inmoviliario = trim($_POST['id_inmoviliario']);

        $query = "SELECT * FROM tbl_inmueble WHERE id_inmueble = :id_moviliario";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id_moviliario', $id_inmoviliario, PDO::PARAM_INT);

        if($stmt->execute()){
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(count($result) > 0){
                $row              = $result[0];
                $modo             = $row['arriendo_o_venta'] == 1 ? "Arriendo" : "Venta";
                $precio           = $row['precio'];
                $direccion        = $row['direccion'];
                $descripcion      = $row['descripcion'];
                $id_municipio     = $row['id_municipio_ubicacion'];
                $id_tipo_inmueble = $row['id_tipo_inmueble'];

                #---Obtener el nombre del tipo de inmueble---#
                $query = "SELECT * FROM tbl_tipo_inmueble WHERE id_tipo_inmueble = :id_tipo_inmueble";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':id_tipo_inmueble', $id_tipo_inmueble, PDO::PARAM_INT);

                if($stmt->execute()){
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if(count($result) > 0){
                        $row = $result[0];
                        $nombre_tipo_inmueble = $row['tipo_inmueble'];
                    }
                }

                #---Obtener nombres de municipios y ubicacion exacta---#

                $query = "SELECT * FROM tbl_municipio WHERE id_municipio = :id_municipio";
                $stmt  = $pdo->prepare($query);
                $stmt->bindParam(':id_municipio', $id_municipio, PDO::PARAM_INT);

                if($stmt->execute()){
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if(count($result) > 0){
                        $row = $result[0];
                        $nombre_municipio = $row['nombre_municipio'];
                        $id_estado        = $row['id_estado'];
                        $id_pais          = $row['id_pais'];

                        #obtener nombre del estado
                        $query = "SELECT * FROM tbl_estado WHERE id_estado = :id_estado";
                        $stmt = $pdo->prepare($query);
                        $stmt->bindParam(':id_estado', $id_estado, PDO::PARAM_INT);
                        
                        if($stmt->execute()){
                            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            if(count($result) > 0){
                                $row = $result[0];
                                $nombre_estado = $row['nombre_estado'];
                                $id_estado     = $row['id_estado'];

                                #Obtener el nombre del pais
                                $query = "SELECT * from tbl_pais WHERE id_pais = :id_pais";
                                $stmt = $pdo->prepare($query);
                                $stmt->bindParam(':id_pais', $id_pais, PDO::PARAM_INT);

                                if($stmt->execute()){
                                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                    if(count($result) > 0){
                                        $row = $result[0];
                                        $nombre_pais = $row['nombre_pais'];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../extralibs/ToastNotify/ToastNotify.css">
    <script src="../extralibs/ToastNotify/ToastNotify.js" defer></script>
    <script src="../javascript/toastNotifyTP1.js" defer></script>
    <script src="https://code.jquery.com/jquery-latest.min.js"></script>
    <script src="../javascript/essentials.js" defer></script>
    <script src="../javascript/edit_mov.js" defer></script>
    <title>Editando Moviliario | ArriendoFinca</title>
</head>
<body>
    <header>
        <div id="div_logo">
            <img src="../images/ArriendoFinca.png" alt="Logo" class="logo_largo">
        </div>
        <div id="div_ayuda">
            <input type="button" value="ayuda">
        </div>
        <!-- <a href="database/transfer_locations.php">transfer_locations</a> -->
    </header>

    <section>
        <h2>Editar Moviliario</h2>
        <p>ID del inmueble: <b id="id_inmoviliario"><?php echo $id_inmoviliario; ?></b> (no se puede editar el id del inmueble)</p>
        <p>Tipo de inmueble</p>
        <span>Tipo de inmueble actual: <b><?php echo $nombre_tipo_inmueble ?></b></span>
        <select name="edit_tipo_inmueble" id="edit_tipo_inmueble">
            <option value="default">Seleccione una opción...</option>
            <?php
                $result = get_tipos_inmueble();
                foreach ($result as $row) {
                    echo "<option value='" . $row['id_tipo_inmueble'] . "' " . ($row['tipo_inmueble'] == $nombre_tipo_inmueble ? 'selected' : '') . ">" . $row['tipo_inmueble'] . "</option>";
                }
            ?>
        </select>
        <p>Tipo de gestion</p>
        <select name="edit_arriendo_venta" id="edit_arriendo_venta">
            <option value="1" <?php echo ($modo == "Arriendo") ? 'selected' : ''; ?>>Arriendo</option>
            <option value="2" <?php echo ($modo == "Venta") ? 'selected' : ''; ?>>Venta</option>
        </select>
        <span>Actual estado: <?php echo $modo ?></span>
        <p>Precio</p>
        <input type="text" name="edit_precio" id="edit_precio" value="<?php echo $precio ?>"> 
        <p>Cambiar municipio de ubicación</p>
        <input type="button" value="Editar ubicación" id="btn_edit_municipio">
        <div id="div_edit_location">
            <p>Ubicacion actual: <?php echo $nombre_pais?> Departamento/Estado: <?php echo $nombre_estado ?> Ciudad: <?php echo $nombre_municipio ?></p>
            <select name="edit_pais" id="edit_pais" disabled>
                <option value="default">Seleccione un país...</option>
                <?php
                $result = get_paises();
                foreach ($result as $row) {
                    echo "<option value='" . $row['id_pais'] . "'>" . $row['nombre_pais'] . "</option>";
                }
                ?>
            </select>

            <select name="edit_estado" id="edit_estado" disabled>
                <option value='default'>Seleccione un estado...</option>
            </select>

            <select name="edit_municipio" id="edit_municipio" disabled>
                <option value="default">Seleccione un municipio...</option>
            </select>

            <p>Confirmo los cambios en la ubicacion del inmueble</p>
            <input type="checkbox" name="confirm_new_location" id="confirm_new_location">
        </div>
        <p>Dirección</p>
        <input type="text" name="edit_direccion" id="edit_direccion" value="<?php echo $direccion ?>">
        <p>Descripción del inmueble</p>
        <textarea name="edit_descripcion" id="edit_descripcion"><?php echo $descripcion ?></textarea>

        <p>Añadir fotos</p>
        <input type="file" name="Añadir fotos" id="Añadir fotos">
        <input type="button" value="Borrar fotos">

        <input type="button" value="Cancelar">
        <input type="button" value="Guardar cambios" id="edit_save">
    </section>
</body>
</html>