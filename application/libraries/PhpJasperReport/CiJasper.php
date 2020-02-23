<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include("PhpJasperReport.php");

class CiJasper
{
	protected $ci;
	public $dirReports;
	public $dbConnection;
	public $jasper;
	public function __construct()
	{
        $this->ci =& get_instance();
		$this->jasper = new PhpJasperReport($this->ci->config->item('dbConnection'), 
										$this->ci->config->item('dirJrxml'), 
										$this->ci->config->item('dirReport'));
	}

	public function run($nameJrxml, $params=array(), $formats=array())
	{
		$file = $this->jasper->run($nameJrxml, $params, $formats);
		return $file;
	}

	public function forceDowload($file, $nameNewFile)
	{
		$this->jasper->forceDowload($file, $nameNewFile);
	}

	public function removeFile($file)
	{
		$this->jasper->removeFile($file);
	}

	public function runPdf($nameJrxml, $params=array())
    {
        return $this->jasper->run($nameJrxml, $params, ['pdf']);
    }

    public function runExcel($nameJrxml, $params=array())
    {
        return $this->jasper->run($nameJrxml, $params, ['xls']);
    }

}

/* End of file CiJasper.php */
/* Location: ./application/libraries/PhpJasperReport/CiJasper.php */
