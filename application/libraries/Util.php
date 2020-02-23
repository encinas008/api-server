<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CodeIgniter Rest Controller
 * A fully RESTful server implementation for CodeIgniter using one library, one config file and one controller.
 *
 * @package         CodeIgniter
 * @subpackage      Util
 * @category        Util
 * @author          Ronald Acha R.
 * @license         MIT
 * @version         1.0.0
 */
class Util {

    private $user_agent = "estado";

    public function __construct()
    {
        $this->user_agent = $_SERVER['HTTP_USER_AGENT'];
    }

    public function encrypt($string, $key) {
        $result = '';
        for($i=0; $i<strlen($string); $i++) {
            $char = substr($string, $i, 1);
            $keychar = substr($key, ($i % strlen($key))-1, 1);
            $char = chr(ord($char)+ord($keychar));
            $result.=$char;
        }
        return base64_encode($result);
   }

   public function decrypt($string, $key) {
      $result = '';
      $string = base64_decode($string);
      for($i=0; $i<strlen($string); $i++) {
         $char = substr($string, $i, 1);
         $keychar = substr($key, ($i % strlen($key))-1, 1);
         $char = chr(ord($char)-ord($keychar));
         $result.=$char;
      }
      return $result;
   }


   function get_client_ip_env() {
      $ipaddress = '';
      if (getenv('HTTP_CLIENT_IP'))
          $ipaddress = getenv('HTTP_CLIENT_IP');
      else if(getenv('HTTP_X_FORWARDED_FOR'))
          $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
      else if(getenv('HTTP_X_FORWARDED'))
          $ipaddress = getenv('HTTP_X_FORWARDED');
      else if(getenv('HTTP_FORWARDED_FOR'))
          $ipaddress = getenv('HTTP_FORWARDED_FOR');
      else if(getenv('HTTP_FORWARDED'))
          $ipaddress = getenv('HTTP_FORWARDED');
      else if(getenv('REMOTE_ADDR'))
          $ipaddress = getenv('REMOTE_ADDR');
      else
          $ipaddress = 'UNKNOWN';
  
      return $ipaddress;
   }
  
   function get_client_ip_server() {
      $ipaddress = '';
      if ($_SERVER['HTTP_CLIENT_IP'])
          $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
      else if($_SERVER['HTTP_X_FORWARDED_FOR'])
          $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
      else if($_SERVER['HTTP_X_FORWARDED'])
          $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
      else if($_SERVER['HTTP_FORWARDED_FOR'])
          $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
      else if($_SERVER['HTTP_FORWARDED'])
          $ipaddress = $_SERVER['HTTP_FORWARDED'];
      else if($_SERVER['REMOTE_ADDR'])
          $ipaddress = $_SERVER['REMOTE_ADDR'];
      else
          $ipaddress = 'UNKNOWN';
  
      return $ipaddress;
   }

	//echo $user_agent;
	function getBrowser($user_agent){

		if(strpos($user_agent, 'Maxthon') !== FALSE)
			return "Maxthon";
		elseif(strpos($user_agent, 'SeaMonkey') !== FALSE)
			return "SeaMonkey";
		elseif(strpos($user_agent, 'Vivaldi') !== FALSE)
			return "Vivaldi";
		elseif(strpos($user_agent, 'Arora') !== FALSE)
			return "Arora";
		elseif(strpos($user_agent, 'Avant Browser') !== FALSE)
			return "Avant Browser";
		elseif(strpos($user_agent, 'Beamrise') !== FALSE)
			return "Beamrise";
		elseif(strpos($user_agent, 'Epiphany') !== FALSE)
			return 'Epiphany';
		elseif(strpos($user_agent, 'Chromium') !== FALSE)
			return 'Chromium';
		elseif(strpos($user_agent, 'Iceweasel') !== FALSE)
			return 'Iceweasel';
		elseif(strpos($user_agent, 'Galeon') !== FALSE)
			return 'Galeon';
		elseif(strpos($user_agent, 'Edge') !== FALSE)
			return 'Microsoft Edge';
		elseif(strpos($user_agent, 'Trident') !== FALSE) //IE 11
			return 'Internet Explorer';
		elseif(strpos($user_agent, 'MSIE') !== FALSE)
			return 'Internet Explorer';
		elseif(strpos($user_agent, 'Opera Mini') !== FALSE)
			return "Opera Mini";
		elseif(strpos($user_agent, 'Opera') || strpos($user_agent, 'OPR') !== FALSE)
			return "Opera";
		elseif(strpos($user_agent, 'Firefox') !== FALSE)
			return 'Mozilla Firefox';
		elseif(strpos($user_agent, 'Chrome') !== FALSE)
			return 'Google Chrome';
		elseif(strpos($user_agent, 'Safari') !== FALSE)
			return "Safari";
		elseif(strpos($user_agent, 'iTunes') !== FALSE)
			return 'iTunes';
		elseif(strpos($user_agent, 'Konqueror') !== FALSE)
			return 'Konqueror';
		elseif(strpos($user_agent, 'Dillo') !== FALSE)
			return 'Dillo';
		elseif(strpos($user_agent, 'Netscape') !== FALSE)
			return 'Netscape';
		elseif(strpos($user_agent, 'Midori') !== FALSE)
			return 'Midori';
		elseif(strpos($user_agent, 'ELinks') !== FALSE)
			return 'ELinks';
		elseif(strpos($user_agent, 'Links') !== FALSE)
			return 'Links';
		elseif(strpos($user_agent, 'Lynx') !== FALSE)
			return 'Lynx';
		elseif(strpos($user_agent, 'w3m') !== FALSE)
			return 'w3m';
		else
			return 'No hemos podido detectar su navegador';
    }
    
    /** */
    /*function Image($image, $maxWidth, $maxHeight, $padding = 0, $r = 255, $g = 255, $b = 255 )
    {
        $image = ImageCreateFromString(file_get_contents($image));

        if (is_resource($image) === true)
        {
            $x = 0;
            $y = 0;
        
            $width = imagesx($image);
            $height = imagesy($image);
        
            $result = ImageCreateTrueColor($maxWidth, $maxHeight);
        
            $ratio = min( $maxWidth / $width, $maxHeight/ $height );
        
            $newWidth = $ratio * $width;
            $newHeight = $ratio * $height;
        
            $newWidth = $newWidth - $padding;
            $newHeight = $newHeight - $padding;
        
            $new_x = ($maxWidth - $newWidth) /2;
            $new_y = ($maxHeight - $newHeight) /2;
        
            ImageSaveAlpha($result, true);
            ImageAlphaBlending($result, true);
            ImageFill($result, 0, 0, ImageColorAllocate($result, $r, $g, $b));
            ImageCopyResampled($result, $image, $new_x, $new_y, $x, $y, $newWidth, $newHeight, $width, $height);
        
            ImageInterlace($result, true);
            ImageJPEG($result, null, 90);
        }
        
        return false;
    }*/

    function base64ToImageResize($imageBase64, $name, $maxWidth, $maxHeight, $padding = 0, $r = 255, $g = 255, $b = 255 )
    {
        $bin = base64_decode($imageBase64);
        $image = ImageCreateFromString($bin);

        if (is_resource($image) === true)
        {
            $x = 0;
            $y = 0;
        
            $width = imagesx($image);
            $height = imagesy($image);
        
            $result = ImageCreateTrueColor($maxWidth, $maxHeight);
        
            $ratio = min( $maxWidth / $width, $maxHeight/ $height );
        
            $newWidth = $ratio * $width;
            $newHeight = $ratio * $height;
        
            $newWidth = $newWidth - $padding;
            $newHeight = $newHeight - $padding;
        
            $new_x = ($maxWidth - $newWidth) /2;
            $new_y = ($maxHeight - $newHeight) /2;
        
            ImageSaveAlpha($result, true);
            ImageAlphaBlending($result, true);
            ImageFill($result, 0, 0, ImageColorAllocate($result, $r, $g, $b));
            ImageCopyResampled($result, $image, $new_x, $new_y, $x, $y, $newWidth, $newHeight, $width, $height);
        
            ImageInterlace($result, true);

            $img_file = FCPATH.'public/uploads/usuario/'.$name.".png";
            //ImageJPEG($result, $img_file , 90);
            imagepng($result, $img_file , 9);
        }
        
        return false;
    }

    function base64ToImage($imageBase64, $name, $padding = 0, $r = 255, $g = 255, $b = 255 )
    {
        $bin = base64_decode($imageBase64);
        $image = ImageCreateFromString($bin);

        if (is_resource($image) === true)
        {
            $x = 0;
            $y = 0;
        
            $width = imagesx($image);
            $height = imagesy($image);

            $maxWidth = $width;
            $maxHeight = $height;

            if( $width > 1600){
                $maxWidth = $width - ((40*$width)/100);     //le quitamos el 40% de su tamaño original
                $maxHeight = $height - ((40*$height)/100);  //le quitamos el 40% de su tamaño original
            }
        
            $result = ImageCreateTrueColor($maxWidth, $maxHeight);
        
            $ratio = min( $maxWidth / $width, $maxHeight/ $height );
        
            $newWidth = $ratio * $width;
            $newHeight = $ratio * $height;
        
            $newWidth = $newWidth - $padding;
            $newHeight = $newHeight - $padding;
        
            $new_x = ($maxWidth - $newWidth) /2;
            $new_y = ($maxHeight - $newHeight) /2;
        
            ImageSaveAlpha($result, true);
            ImageAlphaBlending($result, true);
            ImageFill($result, 0, 0, ImageColorAllocate($result, $r, $g, $b));
            ImageCopyResampled($result, $image, $new_x, $new_y, $x, $y, $newWidth, $newHeight, $width, $height);
        
            ImageInterlace($result, true);

            $img_file = FCPATH.'public/uploads/usuario/'.$name.".png";
            //ImageJPEG($result, $img_file , 90);
            imagepng($result, $img_file , 9);
        }
        
        return false;
    }

    
    function is_base64($s){
        # Check if there are valid base64 characters
        if (!preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $s)) return false;

        // Decode the string in strict mode and check the results
        $decoded = base64_decode($s, true);
        if(false === $decoded) return false;

        // Encode the string again
        if(base64_encode($decoded) != $s) return false;

        return true;
    }
}