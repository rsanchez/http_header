<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$plugin_info = array(
	'pi_name' => 'HTTP Header',
	'pi_version' => '1.0.1',
	'pi_author' => 'Rob Sanchez',
	'pi_author_url' => 'http://github.com/rsanchez',
	'pi_description' => 'Set the HTTP Headers for your template.',
	'pi_usage' => Http_header::usage()
);

/**
 * @property CI_Controller $EE
 */
class Http_header
{
	public $return_data = '';

	public function Http_header()
	{
		$this->EE =& get_instance();
		
		foreach ($this->EE->TMPL->tagparams as $key => $value)
		{
			$method = 'set_'.$key;
			
			if (method_exists($this, $method))
			{
				$this->{$method}($value);
			}
		}
		
		if ($this->EE->TMPL->fetch_param('terminate') === 'yes')
		{
			foreach ($this->EE->output->headers as $header)
			{
				@header($header[0], $header[1]);
			}
			
			exit;
		}
		
		//this tricks the output class into NOT sending its own headers
		$this->EE->TMPL->template_type = 'cp_asset';
		
		return $this->EE->TMPL->tagdata;
	}
	
	protected function parse_path($path)
	{
		if ( ! $path)
		{
			return '';
		}
		
		if (strpos($path, '{site_url}') !== FALSE)
		{
			$path = str_replace('{site_url}', get_instance()->functions->fetch_site_index(1), $path);
		}
		
		if (strpos($path, LD.'path=') !== FALSE)
		{
			$path = preg_replace_callback('/'.LD.'path=[\042\047]?(.*?)[\042\047]?'.RD.'/', array($this->EE->functions, 'create_url'), $path);
		}
		
		if ( ! preg_match('#^/|[a-z]+://#', $path))
		{
			$path = get_instance()->functions->create_url($path);
		}
		
		return $path;
	}
	
	protected function set_status($code)
	{
		$this->EE->output->set_status_header($code);
	}
	
	protected function set_location($location)
	{
		$this->EE->output->set_header('Location: '.$this->parse_path($location));
	}
	
	protected function set_content_type($content_type)
	{
		$this->EE->output->set_header('Content-Type: '.$content_type);
	}
	
	public static function usage()
	{
		ob_start(); 
?>
# HTTP Header #

Set the HTTP Headers for your template.

## Parameters

* status - input an HTTP Status code
* location - set a location for redirection
* content_type - set a Content-Type header
* terminate - set to "yes" to prevent any other output from the template

## Examples

Do a 301 redirect
	{exp:http_header status="301" location="{path=site/something}" terminate="yes"}

Set a 404 Status header
	{exp:http_header status="404"}

Set the Content-Type header to application/json
	{exp:http_header content_type="application/json"}
<?php
		$buffer = ob_get_contents();
		      
		ob_end_clean(); 
	      
		return $buffer;
	}
}
/* End of file pi.http_header.php */ 
/* Location: ./system/expressionengine/third_party/http_header/pi.http_header.php */ 