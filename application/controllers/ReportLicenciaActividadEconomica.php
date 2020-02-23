<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ReportLicenciaActividadEconomica extends CI_Controller {

	public function __construct()
	{
		parent::__construct();

        $this->load->model('DeclaracionJurada_model');
        $this->load->model('ActividadEconomica_model');
        $this->load->model('Estado_model');
        $this->load->model('Solicitante_model');
        $this->load->model('RepresentanteLegal_model');
        $this->load->model('Persona_model');

        $this->load->library('Authorization_Token');

        $this->config->load('configCiJasper'); //Carga el configurador. Tambien puedes cargarlo automaticamente en el archivo autoload.php con $autoload['config'] = array('configCiJasper');
        $this->load->library('PhpJasperReport/CiJasper');

        $this->load->library('Ciqrcode');

        $this->load->helper('download');
    }

	public function index()
	{
        echo "***************** respuesta de reportLicenciaActividadEconomica";
    }

    /**
    * Create a new report for declaracion jurada
    * --------------------------
    * @param: token of declaracion_jurada
    * --------------------------
    * @author: RAR
    * @link: report/licencia-actividad-economica/(:any)
    */
    public function licenciaActividadEconomicaDownload($token){

        $token_dj = $this->security->xss_clean($token);
        $this->security->xss_clean($this->input->get());

        $_token_auth = $this->input->get('auth', TRUE);
        $is_valid_token = $this->authorization_token->validateToken($_token_auth);

        if(null !== $is_valid_token && $is_valid_token['status'] === TRUE){

            $user = $is_valid_token['data'];
            if(!is_null($token_dj) && !empty($token_dj) ){
                $declaracion_jurada = $this->DeclaracionJurada_model->getByToken($token_dj);
    
                if(null !== $declaracion_jurada){

                    $estado = $this->Estado_model->getById($declaracion_jurada->id_estado);
                    $actividad_economica = $this->ActividadEconomica_model->getById( $declaracion_jurada->id_actividad_economica );

                    if($estado->code === "APROBADO"){
                        $solicitante = $this->Solicitante_model->getById( $declaracion_jurada->id_solicitante );
                        $representante_legal = $this->RepresentanteLegal_model->getByIdSolicitud( $solicitante->id );
                        $persona = $this->Persona_model->getById($representante_legal->id_persona);

                        #contruimos el codigo QR
                        $params['data'] = "N-".$declaracion_jurada->numero."--F-".$declaracion_jurada->fur."--RC-".$actividad_economica->rotulo_comercial."--CI-".$persona->numero_documento;
                        $params['level'] = 'H';
                        $params['size'] = 150;
                        $params['savename'] = FCPATH.'public/'. $declaracion_jurada->token.".png";
                        $this->ciqrcode->generate($params);

                        $img_qr = file_get_contents( FCPATH.'public/'. $declaracion_jurada->token.".png");
                        $base64_qr = base64_encode($img_qr);

                        $param_pdf['id_dj'] = (int)$declaracion_jurada->id;
                        $param_pdf['img_qr'] = strval($base64_qr);
                        $param_pdf['username'] = strval($user->username.substr($user->username, 0, strpos($user->username, '@')));

                        //$solicitante = $this->Solicitante_model->getById($declaracion_jurada->id_solicitante);
                        if((int)$solicitante->contribuyente === (int)NATURAL)
                            $file = $this->cijasper->run('licencia_natural.jrxml', $param_pdf,['pdf']);
                        else
                            $file = $this->cijasper->run('licencia_juridica.jrxml', $param_pdf,['pdf']);
    
                        force_download($declaracion_jurada->numero.".pdf",file_get_contents($file)); 
                    }else{
                        if($estado->code === "COMPLETADO"){
                            $solicitante = $this->Solicitante_model->getById($declaracion_jurada->id_solicitante);

                            if((int)$solicitante->contribuyente === (int)NATURAL)
                                $file = $this->cijasper->run('licencia_natural_invalid.jrxml', ['id_dj'=>(int)$declaracion_jurada->id],['pdf']);
                            else
                                $file = $this->cijasper->run('licencia_juridica_invalid.jrxml', ['id_dj'=>(int)$declaracion_jurada->id],['pdf']);

                            force_download($declaracion_jurada->numero.".pdf", file_get_contents($file)); 
                        }else{
                            $this->output->set_output([
                                'status' => true,
                                'message' =>  $this->lang->line('not_item_were_found'),
                            ]);
                        }
                    }
                }
            }
        }
    }
    
    /**
    * Create a new report for declaracion jurada
    * --------------------------
    * @param: token of declaracion_jurada
    * --------------------------
    * @author: RAR
    * @link: report/licencia-actividad-economica/(:any)
    */
    public function licenciaActividadEconomicaOnLine($token){

        $token_dj = $this->security->xss_clean($token);
        $this->security->xss_clean($this->input->get());

        $_token_auth = $this->input->get('auth', TRUE);
        $is_valid_token = $this->authorization_token->validateToken($_token_auth);

        if(null !== $is_valid_token && $is_valid_token['status'] === TRUE){

            $user = $is_valid_token['data'];
            if(!is_null($token_dj) && !empty($token_dj) ){
                $declaracion_jurada = $this->DeclaracionJurada_model->getByToken($token_dj);
    
                if(null !== $declaracion_jurada){

                    $estado = $this->Estado_model->getById($declaracion_jurada->id_estado);
                    $actividad_economica = $this->ActividadEconomica_model->getById( $declaracion_jurada->id_actividad_economica );

                    if($estado->code === "APROBADO"){
                        $solicitante = $this->Solicitante_model->getById( $declaracion_jurada->id_solicitante );
                        $representante_legal = $this->RepresentanteLegal_model->getByIdSolicitud( $solicitante->id );
                        $persona = $this->Persona_model->getById($representante_legal->id_persona);

                        #contruimos el codigo QR
                        $params['data'] = "N-".$declaracion_jurada->numero."--F-".$declaracion_jurada->fur."--RC-".$actividad_economica->rotulo_comercial."--CI-".$persona->numero_documento;
                        $params['level'] = 'H';
                        $params['size'] = 150;
                        $params['savename'] = FCPATH.'public/'. $declaracion_jurada->token.".png";
                        $this->ciqrcode->generate($params);

                        $img_qr = file_get_contents( FCPATH.'public/'. $declaracion_jurada->token.".png");
                        $base64_qr = base64_encode($img_qr);

                        $param_pdf['id_dj'] = (int)$declaracion_jurada->id;
                        $param_pdf['img_qr'] = strval($base64_qr);
                        $param_pdf['username'] = strval($user->username.substr($user->username, 0, strpos($user->username, '@')));

                        if((int)$solicitante->contribuyente === (int)NATURAL)
                            $file = $this->cijasper->run('licencia_natural.jrxml', $param_pdf,['pdf']);
                        else
                            $file = $this->cijasper->run('licencia_juridica.jrxml', $param_pdf,['pdf']);
    
                        self::downloadInline($declaracion_jurada->numero, $file);
                    }else{
                        if($estado->code === "COMPLETADO"){
                            $solicitante = $this->Solicitante_model->getById($declaracion_jurada->id_solicitante);
                            if((int)$solicitante->contribuyente === (int)NATURAL)
                                $file = $this->cijasper->run('licencia_natural_invalid.jrxml', ['id_dj'=>(int)$declaracion_jurada->id],['pdf']);
                            else
                                $file = $this->cijasper->run('licencia_juridica_invalid.jrxml', ['id_dj'=>(int)$declaracion_jurada->id],['pdf']);

                            self::downloadInline($declaracion_jurada->numero, $file);
                        }else{
                            $this->output->set_output([
                                'status' => true,
                                'message' =>  $this->lang->line('not_item_were_found'),
                            ]);
                        }
                    }
                }else{
                    $this->output->set_output([
                        'status' => false,
                        'message' => $this->lang->line('identifier_is_required'),
                    ]);
                }
            }else{
                $this->output->set_output([
                    'status' => false,
                    'message' => $this->lang->line('identifier_is_required'),
                ]);
            }
        }else{
            $this->output->set_output([
                'status' => false,
                'message' => $this->lang->line('token_is_invalid'),
            ]);
        }
    }

    private function downloadInline($name, $file){
        $pdf = file_get_contents($file);

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="'.$name.'.pdf"');
        header("Content-Transfer-Encoding: binary");
        header('Expires: 0');
        header('Pragma: no-cache');
        header("Content-Length: " . strlen($pdf));
        readfile($file);
        exit();

        /* esto funciona
        $path = FCPATH.'/temp_reports/report_20190927035110_5d8e682e7bbc6.pdf';
        $pdf = file_get_contents( FCPATH.'/temp_reports/report_20190927035110_5d8e682e7bbc6.pdf');

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="'.$declaracion_jurada->numero.'.pdf"');
        header("Content-Transfer-Encoding: binary");
        header('Expires: 0');
        header('Pragma: no-cache');
        header("Content-Length: " . strlen($pdf));
        readfile($path); // push it out
        exit();
        */
    }
}