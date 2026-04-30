<?php

require_once __DIR__ . '/../Conexion.php';

class ReportesModel
{
    private $conn;

    public function __construct()
    {
        $conexion = new Conexion();
        $this->conn = $conexion->getConnection();
    }

    /**
     * Reporte 1: Promedio salarial por departamento
     */
    public function getPromedioSalarialPorDepto()
    {
        /*
         * EXPLICACIÓN DE COMANDOS SQL:
         * SELECT: Selecciona las columnas que queremos mostrar en el resultado.
         * ROUND(AVG(e.salario), 2): 'AVG' calcula el promedio matemático de todos los salarios. 'ROUND' redondea ese resultado a 2 decimales.
         * FROM: Indica la tabla principal de donde sacaremos los datos (tbldepartamentos).
         * JOIN: Une la tabla de departamentos con la de empleados. Funciona como un puente donde la condición 'ON' (d.id_departamento = e.id_departamento) asegura que solo se unan los empleados que pertenecen a ese departamento en específico.
         * GROUP BY: Agrupa las filas por departamento. Esto es OBLIGATORIO cuando usamos funciones matemáticas como AVG o SUM, porque le dice a la base de datos "calcula el promedio POR CADA departamento".
         * ORDER BY: Ordena los resultados finales. En este caso numéricamente por el ID del departamento.
         */
        $query = "
            SELECT 
                d.id_departamento AS \"ID DEPARTAMENTO\", 
                d.nombre_departamento AS \"DEPARTAMENTO\", 
                ROUND(AVG(e.salario), 2) AS \"SALARIO PROMEDIO\"
            FROM tbldepartamentos d
            JOIN tblempleados e ON d.id_departamento = e.id_departamento
            GROUP BY d.id_departamento, d.nombre_departamento
            ORDER BY d.id_departamento ASC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Reporte 2: Reporte de Antigüedad (Departamento 20)
     */
    public function getAntiguedadEmpleados()
    {
        /*
         * EXPLICACIÓN DE COMANDOS SQL:
         * || ' ' || : En PostgreSQL, la doble barra vertical (||) sirve para concatenar (unir) textos. Aquí unimos nombre y apellidos con un espacio en blanco.
         * COALESCE(): Devuelve el primer valor que no sea NULO. Si el empleado no tiene apellido materno (NULL), devolverá un espacio vacío para que no falle la concatenación.
         * CURRENT_DATE - fecha: En PostgreSQL, restar dos fechas devuelve automáticamente la cantidad de DÍAS de diferencia como un número entero.
         * TRUNC(... / 7): 'TRUNC' corta los decimales. Al dividir esos días entre 7, obtenemos las SEMANAS exactas que han pasado.
         * WHERE: Filtra los resultados ANTES de procesarlos, dejando pasar SOLO a los empleados cuyo id_departamento sea 20.
         * ORDER BY ... DESC, ... ASC: Primero ordena por antigüedad de mayor a menor (DESC) y en caso de empate, alfabéticamente por apellido paterno (ASC).
         */
        $query = "
            SELECT 
                e.nombre || ' ' || e.apaterno || ' ' || COALESCE(e.amaterno, '') AS \"NOMBRE COMPLETO\",
                e.fecha_contratacion AS \"FECHA DE CONTRATACIÓN\",
                e.id_empleado AS \"ID EMPLEADO\",
                d.nombre_departamento AS \"DEPARTAMENTO\",
                TRUNC((CURRENT_DATE - e.fecha_contratacion) / 7) AS \"SEMANAS DE ANTIGÜEDAD\"
            FROM tblempleados e
            JOIN tbldepartamentos d ON e.id_departamento = d.id_departamento
            WHERE e.id_departamento = 20
            ORDER BY \"SEMANAS DE ANTIGÜEDAD\" DESC, e.apaterno ASC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Reporte 3: Gasto total por departamento (> 15000)
     */
    public function getGastoTotalPorDepto()
    {
        /*
         * EXPLICACIÓN DE COMANDOS SQL:
         * SUM(e.salario): Función de agregación que suma todos los valores de la columna salario de los empleados agrupados.
         * HAVING: Es como un WHERE, pero EXCLUSIVO para agrupaciones. Mientras que WHERE filtra filas individuales antes de agrupar, HAVING filtra el RESULTADO de la agrupación. Aquí solo muestra los grupos cuya SUMA total es mayor a 15000.
         */
        $query = "
            SELECT 
                d.id_departamento AS \"ID DEPARTAMENTO\", 
                d.nombre_departamento AS \"DEPARTAMENTO\", 
                SUM(e.salario) AS \"GASTO TOTAL\"
            FROM tbldepartamentos d
            JOIN tblempleados e ON d.id_departamento = e.id_departamento
            GROUP BY d.id_departamento, d.nombre_departamento
            HAVING SUM(e.salario) > 15000
            ORDER BY d.id_departamento ASC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Reporte 4: Gasto salarial por puesto (Excluyendo 'TRAN' y > 2000)
     */
    public function getGastoSalarialPorPuesto()
    {
        /*
         * EXPLICACIÓN DE COMANDOS SQL:
         * NOT LIKE '%TRAN': 'LIKE' se usa para buscar patrones de texto. El '%' significa 'cualquier cosa antes'. Así que '%TRAN' busca todo lo que termine en 'TRAN'. Al poner 'NOT LIKE', excluimos estrictamente todos los puestos cuyo ID termina con esas letras.
         */
        $query = "
            SELECT 
                p.id_puesto AS \"ID PUESTO\", 
                p.nombre_puesto AS \"PUESTO\", 
                SUM(e.salario) AS \"GASTO TOTAL EN SUELDOS\"
            FROM tblpuestos p
            JOIN tblempleados e ON p.id_puesto = e.id_puesto
            WHERE p.id_puesto NOT LIKE '%TRAN'
            GROUP BY p.id_puesto, p.nombre_puesto
            HAVING SUM(e.salario) > 2000
            ORDER BY p.id_puesto ASC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Reporte 5: Empleados y sus Jefes Directos
     */
    public function getEmpleadosYJefes()
    {
        /*
         * EXPLICACIÓN DE COMANDOS SQL:
         * LEFT JOIN tblempleados j: Esto es un 'Self-Join' o unión consigo misma. Unimos la tabla de empleados (e) con la misma tabla de empleados pero usando un alias diferente (j de jefe).
         * La condición ON e.id_gerente = j.id_empleado significa: busca al empleado cuyo ID sea igual al ID de gerente del empleado actual.
         * Usamos LEFT JOIN en lugar de JOIN normal, porque el Director General NO tiene jefe (su id_gerente es nulo). Si usáramos JOIN normal, el Director no saldría en la lista. LEFT JOIN asegura que salgan todos, tengan jefe o no.
         */
        $query = "
            SELECT 
                p.nombre_puesto AS \"PUESTO\", 
                e.nombre || ' ' || e.apaterno || ' ' || COALESCE(e.amaterno, '') AS \"EMPLEADO\", 
                COALESCE(j.nombre || ' ' || j.apaterno || ' ' || COALESCE(j.amaterno, ''), 'Sin Jefe (Director)') AS \"JEFE DIRECTO\"
            FROM tblempleados e
            JOIN tblpuestos p ON e.id_puesto = p.id_puesto
            LEFT JOIN tblempleados j ON e.id_gerente = j.id_empleado
            ORDER BY \"JEFE DIRECTO\" ASC, \"EMPLEADO\" ASC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Reporte 6: Jefes y Número de Subordinados
     */
    public function getJefesYSubordinados()
    {
        /*
         * EXPLICACIÓN DE COMANDOS SQL:
         * COUNT(e.id_empleado): Cuenta cuántas filas (empleados) pertenecen a este grupo en específico (el jefe).
         * Aquí, la agrupación se hace por los datos del Jefe (j), y contamos los empleados (e) que dependen de él.
         */
        $query = "
            SELECT 
                j.nombre || ' ' || j.apaterno || ' ' || COALESCE(j.amaterno, '') AS \"GERENTE\", 
                COUNT(e.id_empleado) AS \"NÚMERO DE SUBORDINADOS\"
            FROM tblempleados e
            JOIN tblempleados j ON e.id_gerente = j.id_empleado
            GROUP BY \"GERENTE\"
            ORDER BY \"NÚMERO DE SUBORDINADOS\" DESC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Reporte 7: Reporte Geográfico de Empleados
     */
    public function getReporteGeografico()
    {
        /*
         * EXPLICACIÓN DE COMANDOS SQL:
         * Cadena de JOINs: Aquí encadenamos 4 tablas diferentes. 
         * Empleado -> Sucursal -> Ciudad -> Estado.
         * La base de datos sigue el rastro desde el ID de sucursal del empleado, hasta encontrar a qué ciudad y estado pertenece esa sucursal en específico.
         */
        $query = "
            SELECT 
                e.id_empleado AS \"ID EMPLEADO\", 
                e.nombre || ' ' || e.apaterno || ' ' || COALESCE(e.amaterno, '') AS \"NOMBRE COMPLETO\", 
                d.nombre_departamento AS \"DEPARTAMENTO\", 
                c.nombre_ciudad AS \"CIUDAD\", 
                es.nombre_estado AS \"ESTADO\"
            FROM tblempleados e
            JOIN tbldepartamentos d ON e.id_departamento = d.id_departamento
            JOIN tblsucursales s ON e.id_sucursal = s.id_sucursal
            JOIN tblciudades c ON s.id_ciudad = c.id_ciudad
            JOIN tblestados es ON c.id_estado = es.id_estado
            ORDER BY es.nombre_estado ASC, c.nombre_ciudad ASC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Reporte 8: Ficha de Empleado (Devuelve todos, el JS filtrará por nombre)
     */
    public function getFichasEmpleados()
    {
        /*
         * EXPLICACIÓN DE COMANDOS SQL:
         * Seleccionamos toda la información detallada requerida. Retornamos todos los empleados y delegamos la búsqueda por nombre y apellido paterno a la capa de frontend (JavaScript) para que el filtro sea instantáneo en la tabla sin recargar la página.
         */
        $query = "
            SELECT 
                e.nombre || ' ' || e.apaterno || ' ' || COALESCE(e.amaterno, '') AS \"NOMBRE COMPLETO\",
                p.nombre_puesto AS \"PUESTO\", 
                d.nombre_departamento AS \"DEPARTAMENTO\", 
                c.nombre_ciudad AS \"CIUDAD\", 
                es.nombre_estado AS \"ESTADO\"
            FROM tblempleados e
            JOIN tblpuestos p ON e.id_puesto = p.id_puesto
            JOIN tbldepartamentos d ON e.id_departamento = d.id_departamento
            JOIN tblsucursales s ON e.id_sucursal = s.id_sucursal
            JOIN tblciudades c ON s.id_ciudad = c.id_ciudad
            JOIN tblestados es ON c.id_estado = es.id_estado
            ORDER BY e.nombre ASC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Reporte 9: Empleados con salario menor al mínimo de IT_PROG
     */
    public function getSalariosMenoresIT()
    {
        /*
         * EXPLICACIÓN DE COMANDOS SQL:
         * Subconsulta: (SELECT MIN(salario) FROM tblempleados WHERE id_puesto = 'IT_PROG')
         * Primero, la base de datos resuelve el código entre paréntesis, encontrando cuál es el salario más bajo de todos los programadores.
         * Después de obtener ese número (ejemplo: 10,000), la consulta principal (el WHERE de afuera) compara a todos los empleados contra ese número y filtra solo a los que ganan menos.
         */
        $query = "
            SELECT 
                e.id_empleado AS \"ID EMPLEADO\", 
                e.nombre || ' ' || e.apaterno || ' ' || COALESCE(e.amaterno, '') AS \"EMPLEADO\", 
                e.salario AS \"SALARIO\",
                p.nombre_puesto AS \"PUESTO\"
            FROM tblempleados e
            JOIN tblpuestos p ON e.id_puesto = p.id_puesto
            WHERE e.salario < (
                SELECT MIN(salario) 
                FROM tblempleados 
                WHERE id_puesto = 'IT_PROG'
            )
            ORDER BY e.salario DESC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Reporte 10: Empleados sin subordinados
     */
    public function getEmpleadosSinSubordinados()
    {
        /*
         * EXPLICACIÓN DE COMANDOS SQL:
         * NOT IN (...): Evalúa si el ID del empleado NO EXISTE dentro de la lista que genera la subconsulta.
         * Subconsulta: (SELECT id_gerente FROM tblempleados WHERE id_gerente IS NOT NULL).
         * La subconsulta crea una lista de todos los IDs que pertenecen a alguien que es jefe. Si el ID de un empleado no está en esa lista, significa que no es jefe de nadie (no tiene subordinados).
         */
        $query = "
            SELECT 
                e.nombre || ' ' || e.apaterno || ' ' || COALESCE(e.amaterno, '') AS \"EMPLEADO\", 
                p.nombre_puesto AS \"PUESTO\", 
                COALESCE(j.nombre || ' ' || j.apaterno || ' ' || COALESCE(j.amaterno, ''), 'Sin Jefe') AS \"JEFE DIRECTO\"
            FROM tblempleados e
            JOIN tblpuestos p ON e.id_puesto = p.id_puesto
            LEFT JOIN tblempleados j ON e.id_gerente = j.id_empleado
            WHERE e.id_empleado NOT IN (
                SELECT DISTINCT id_gerente 
                FROM tblempleados 
                WHERE id_gerente IS NOT NULL
            )
            ORDER BY p.nombre_puesto ASC, \"EMPLEADO\" ASC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // =============== MÉTODOS PARA TARJETAS GENERALES DEL DASHBOARD ===============
    
    public function getTotalEmpleados() {
        $query = "SELECT COUNT(*) AS total FROM tblempleados";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
    
    public function getTotalDepartamentos() {
        $query = "SELECT COUNT(*) AS total FROM tbldepartamentos";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
    
    public function getTotalSucursales() {
        $query = "SELECT COUNT(*) AS total FROM tblsucursales";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
    
    public function getGastoMensualTotal() {
        $query = "SELECT SUM(salario) AS total FROM tblempleados";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
}
?>
