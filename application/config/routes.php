
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['API'] = 'Rest_server';

/** routes for recaudaciones */
//$route['product/(:num)'] = 'catalog/product_lookup_by_id/$1';
$route['api/recaudaciones/persona-by-ci/(:any)'] = 'api/recaudaciones/getPersonaByCi/$1';
$route['api/recaudaciones/persona-by-nit/(:any)'] = 'api/recaudaciones/getPersonaByNit/$1';

/** routes for declaraciones_juradas */
$route['api/declaraciones-juradas/per-page/(:num)/(:num)/(:num)'] = 'api/DeclaracionJurada/getByPage/$1/$2/$3';
$route['api/declaraciones-juradas/by-token/(:any)'] = 'api/DeclaracionJurada/getByToken/$1';
$route['api/declaraciones-juradas/create'] = 'api/DeclaracionJurada/create';
$route['api/declaraciones-juradas/update'] = 'api/DeclaracionJurada/update';
$route['api/declaraciones-juradas/complete'] = 'api/DeclaracionJurada/complete';
$route['api/declaracione-jurada/check/(:any)'] = 'api/DeclaracionJurada/check/$1';

/** routes for user */
$route['api/usuario/create'] = 'api/Usuario/create';
$route['api/usuario/login'] = 'api/Usuario/login';

/** routes for generic */
$route['api/list/estado-civil'] = 'api/Genericos/getAllEstadoCivil';
$route['api/list/nacionalidad'] = 'api/Genericos/getAllNacionalidad';
$route['api/list/genero'] = 'api/Genericos/getAllGenero';
$route['api/list/ci-expedido'] = 'api/Genericos/getAllCiExpedido';

/** routes for actividad economica */
//$route['api/actividad-economica/search-by-name/(:any)'] = 'api/ActividadEconomica/getSearchByName/$1';

/** routes for tipo actividad economica */
$route['api/tipo-actividad-economica/search-by-name/(:any)/(:any)'] = 'api/TipoActividadEconomica/getSearchByName/$1/$2';

/** routes for domicilio actividad economica */
$route['api/domicilio-actividad-economica/create'] = 'api/DomicilioActividadEconomica/create';
$route['api/domicilio-actividad-economica/update'] = 'api/DomicilioActividadEconomica/update';
$route['api/domicilio-actividad-economica/get-by-token-lic/(:any)'] = 'api/DomicilioActividadEconomica/getByTokenDJ/$1';

/** routes for domicilio */
$route['api/domicilio/token-lic/(:any)'] = 'api/Domicilio/getDomicilioByTokenDJ/$1';

/** routes for cobros*/
$route['api/cobros/fur/(:num)'] = 'api/Cobros/getFur/$1';
// prescripcion
$route['api/cobros/publicidad/(:num)'] = 'api/Cobros/licenciaPublicidad/$1';
$route['api/cobros/sitios-municipales/(:num)'] = 'api/Cobros/licenciaSitiosMunicipales/$1';
//$route['api/cobros/publicidad/(:num)'] = 'api/Cobros/licenciaPublicidad/$1';

//$route['api/cobros/furN/(:num)'] = 'api/Cobros/getFurN/$1';
$route['api/objectoTributario/getObjeto'] = 'api/ObjectoTributario/getObjeto';

/** routes for tipo_sociedad*/
$route['api/tipo-sociedad/all'] = 'api/TipoSociedad/getAll';

/** routes for persona*/
$route['api/persona/create'] = 'api/Persona/create';
$route['api/persona/update'] = 'api/Persona/update';

/** routes for representante-legal*/
$route['api/representante-legal/create'] = 'api/RepresentanteLegal/create';

/** routes for report */
$route['api/report/declaracion-jurada/(:any)'] = 'api/Report/declaracionJurada/$1';
$route['api/report/prescripcionPdf/(:any)'] = 'api/Report/prescripcionPdf/$1';

/** routes for report */
$route['api/solicitante/token-lic/(:any)'] = 'api/Solicitante/getSolicitanteByTokenDJ/$1';

/** search de prescripcion  por */
$route['api/licencia-funcionamiento/search'] = 'api/Prescripcion/search';

//** routes for prescripcion */
$route['api/prescripcion/objetos-tributarios'] = 'api/Prescripcion/obtenerObjetoTributario';

$route['api/prescripcion/create'] = 'api/Prescripcion/create';
$route['api/prescripcion/update'] = 'api/Prescripcion/update'; 

$route['api/domicilioPres/createDireccion'] = 'api/DomicilioPres/createDireccion';
$route['api/domicilioPres/updatePrescripcion'] = 'api/DomicilioPres/updatePrescripcion';

$route['api/ObjectoTributario/createObjTributario'] = 'api/ObjectoTributario/createObjTributario';
$route['api/objectoTributario/updateObjTributario'] = 'api/ObjectoTributario/updateObjTributario';

$route['api/prescripcion/detalleJuri/(:num)']='api/prescripcion/detalleJuri/$1';
$route['api/prescripcion/per-page/(:num)/(:num)/(:num)'] = 'api/Prescripcion/getByPage/$1/$2/$3';

//$route['api/prescripcion/edit/(:num)/(:num)'] = 'api/Prescripcion/getPrescripcionById/$1/$2';
$route['api/prescripcion/edit/(:any)'] = 'api/Prescripcion/getPrescripcionById/$1';
//$route['api/prescripcion/editDireccion/(:any)'] = 'api/Prescripcion/getPrescripcionByDireccion/$1';
$route['api/prescripcion/editJ/(:num)/(:num)'] = 'api/Prescripcion/getPrescripcionByNit/$1/$2';



$route['api/contribuyentePres/token-pres/(:any)'] = 'api/ContribuyentePres/getContribuyenteByToken/$1';


$route['api/prescripcion/estadoPrescripcion/(:num)'] = 'api/Prescripcion/estadoPrescripcion/$1';
//$route['api/prescripcion/gestionJuridico/(:num)/(:num)'] = 'api/Prescripcion/getGestionJuridico/$1/$2';
