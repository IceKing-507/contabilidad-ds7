<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Cuentas por Pagar</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .formulario-container {
            max-width: 600px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="date"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        input[type="submit"] {
            background-color: #1d75e9;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        input[type="submit"]:hover {
            background-color: #03316d;
        }

        /* Agregar estilo para desactivar ciertos campos */
        input[readonly] {
            background-color: #f1f1f1;
            cursor: not-allowed;
        }

    </style>
    <script>
        // Función para formatear el monto automáticamente
        function formatearMonto() {
            var monto = document.getElementById("monto_pagar").value;
            if (monto) {
                var montoFormateado = parseFloat(monto.replace(/,/g, '')).toFixed(2).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                document.getElementById("monto_pagar").value = montoFormateado;
            }
        }

        // Función para validar las fechas mínimo a partir de 2024
        function validarFechas() {
            var fechaEmision = document.getElementById("fecha_emision");
            var fechaVencimiento = document.getElementById("fecha_vencimiento");
            
            // Establecer mínimo para la fecha de emisión y vencimiento
            fechaEmision.setAttribute("min", "2024-01-01");
            fechaVencimiento.setAttribute("min", "2024-01-01");
        }

        window.onload = function() {
            validarFechas();
        };
    </script>
</head>
<body>

<div class="formulario-container">
    <h3 class="form-header p-3 border-bottom border-secondary-subtle custom-title">Cuentas por Pagar</h3>

    <?php
    // Conexión a la base de datos
    $conn = new mysqli("localhost", "root", "", "CONTA");

    // Verifica la conexión
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    // Generar un ID Factura aleatorio de 6 dígitos
    $idFacturaAleatorio = mt_rand(100000, 999999);

    // Obtener los proveedores para la lista desplegable
    $proveedores = $conn->query("SELECT ID_Proveedor, Nombre_Proveedor FROM proveedores");

    // Obtener los métodos de pago para la lista desplegable
    $metodosPago = $conn->query("SELECT ID_Metodo, Nombre FROM metodos_pago_cobro");

    // Obtener los números de cheque para la lista desplegable
    $cheques = $conn->query("SELECT ID_Cheque, Numero_Cheque FROM cheques");

    // Procesar el formulario al hacer clic en "Guardar"
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['guardar'])) {
        $idFactura = $_POST['id_factura'];
        $proveedorID = $_POST['id_proveedor'];
        $fechaEmision = $_POST['fecha_emision'];
        $fechaVencimiento = $_POST['fecha_vencimiento'];
        $montoPagar = str_replace(",", "", $_POST['monto_pagar']); // Eliminar comas antes de almacenar
        $estadoPago = $_POST['estado_pago'];
        $metodoPago = $_POST['metodo_pago'];
        $numeroCheque = $_POST['numero_cheque'];
        $comentarios = $_POST['comentarios'];

        // Validar campos vacíos
        if(empty($proveedorID) || empty($fechaEmision) || empty($fechaVencimiento) || empty($montoPagar) || empty($estadoPago) || empty($metodoPago) || empty($numeroCheque)) {
            echo "<p>Error: Todos los campos son obligatorios.</p>";
        } else {
            // Insertar los datos en la tabla cuentas_x_pagar
            $sql = "INSERT INTO cuentas_x_pagar (ID_Factura, Proveedor_ID, Fecha_Emision, Fecha_Vencimiento, Monto_Pagar, Estado_Pago, ID_Metodo, ID_Cheque, Comentarios)
                    VALUES ('$idFactura', '$proveedorID', '$fechaEmision', '$fechaVencimiento', '$montoPagar', '$estadoPago', '$metodoPago', '$numeroCheque', '$comentarios')";

            if ($conn->query($sql) === TRUE) {
                echo "<p>Datos guardados exitosamente.</p>";
            } else {
                echo "<p>Error: " . $sql . "<br>" . $conn->error . "</p>";
            }
        }
    }

    $conn->close();
    ?>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
        <label for="id_factura">ID Factura:</label>
        <input type="text" id="id_factura" name="id_factura" value="<?php echo $idFacturaAleatorio; ?>" readonly>

        <label for="id_proveedor">ID Proveedor:</label>
        <select id="id_proveedor" name="id_proveedor" required>
            <option value="">Seleccione un proveedor</option>
            <?php
            if ($proveedores->num_rows > 0) {
                while ($row = $proveedores->fetch_assoc()) {
                    echo "<option value='{$row['ID_Proveedor']}'>{$row['Nombre_Proveedor']}</option>";
                }
            }
            ?>
        </select>

        <label for="fecha_emision">Fecha de Emisión:</label>
        <input type="date" id="fecha_emision" name="fecha_emision" required>

        <label for="fecha_vencimiento">Fecha de Vencimiento:</label>
        <input type="date" id="fecha_vencimiento" name="fecha_vencimiento" required>

        <label for="monto_pagar">Monto a Pagar ($):</label>
        <input type="text" id="monto_pagar" name="monto_pagar" onblur="formatearMonto()" required>

        <label for="estado_pago">Estado de Pago:</label>
        <select id="estado_pago" name="estado_pago" required>
            <option value="">Seleccione un estado</option>
            <option value="pendiente">Pendiente</option>
            <option value="pagado">Pagado</option>
        </select>

        <label for="metodo_pago">Método de Pago:</label>
        <select id="metodo_pago" name="metodo_pago" required>
            <option value="">Seleccione un método</option>
            <?php
            if ($metodosPago->num_rows > 0) {
                while ($row = $metodosPago->fetch_assoc()) {
                    echo "<option value='{$row['ID_Metodo']}'>{$row['Nombre']}</option>";
                }
            }
            ?>
        </select>

        <label for="numero_cheque">Número de Cheque/Transacción:</label>
        <select id="numero_cheque" name="numero_cheque" required>
            <option value="">Seleccione un número de cheque</option>
            <?php
            if ($cheques->num_rows > 0) {
                while ($row = $cheques->fetch_assoc()) {
                    echo "<option value='{$row['ID_Cheque']}'>{$row['Numero_Cheque']}</option>";
                }
            }
            ?>
        </select>

        <label for="comentarios">Comentarios:</label>
        <textarea id="comentarios" name="comentarios" rows="4"></textarea>

        <input type="submit" name="guardar" value="Guardar">
    </form>
</div>

</body>
</html>
