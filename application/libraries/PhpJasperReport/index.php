<?php include("PhpJasperReport.php"); ?>

<?php 

$dbConnection = array('driver' => 'postgres', 
                'username' => 'postgres',
                'password' => 'postgres',
                'host' => 'localhost',
                'database' => 'phpjasperreport',
                'port' => '5432');

$jasper = new PhpJasperReport($dbConnection, '/jrxml_store/', '/reports');

$file = $jasper->run('prueba1.jrxml', ['id'=>221],['pdf']);
// OR $file = $jasper->runPdf('prueba1.jrxml', ['id'=>221]); // otra forma de compilar para crear solo un archivo pdf
$jasper->forceDowload($file, 'nuevoOtro.pdf'); // Fuerza la descarga del archivo y le pone el nombre


// $file = $jasper->runExcel('prueba1.jrxml', ['id'=>221]); // otra forma de compilar para crear solo un archivo Excel
// $jasper->forceDowload($file, 'nuevoOtro.xls'); // Fuerza la descarga del archivo y le pone el nombre


echo $file;
$jasper->removeFile($file); // metodo para eliminar de la carpeta, Esto es util si el almacenamiento seria temporal

// otra forma de eliminar el archivo del servidor 
// $jasper->removeFile(); // la ruta optiene de la creacion del objeto
 ?>