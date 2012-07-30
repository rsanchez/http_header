<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$plugin_info = array(
	'pi_name' => 'HTTP Header',
	'pi_version' => '1.0.2',
	'pi_author' => 'Rob Sanchez',
	'pi_author_url' => 'http://github.com/rsanchez',
	'pi_description' => 'Set the HTTP Headers for your template.',
	'pi_usage' => '# HTTP Header #

Set the HTTP Headers for your template.

## Parameters

* status - input an HTTP Status code
* location - set a location for redirection
* content_type - set a Content-Type header
* charset - set a charset in the Content-Type header
* terminate - set to "yes" to prevent any other output from the template

## Examples

Do a 301 redirect
	{exp:http_header status="301" location="site/foo" terminate="yes"}

Set a 404 Status header
	{exp:http_header status="404"}

Set the Content-Type header to application/json
	{exp:http_header content_type="application/json" charset="utf-8"}',
);

/**
 * HTTP Header
 *
 * Set the HTTP Headers for your template.
 *
 * @author Rob Sanchez
 * @link https://github.com/rsanchez/http_header
 * @version 1.0.2
 *
 * @property CI_Controller $EE
 */
class Http_header
{
	/**
	 * @var string the plugin result
	 */
	public $return_data = '';

	/**
	 * constructor and plugin renderer
	 *
	 * @return string
	 */
	public function Http_header()
	{
		$this->EE =& get_instance();

		if ($this->EE->TMPL->fetch_param('status') !== FALSE)
		{
			$this->set_status($this->EE->TMPL->fetch_param('status'));
		}

		if ($this->EE->TMPL->fetch_param('location') !== FALSE)
		{
			$this->set_location($this->EE->TMPL->fetch_param('location'));
		}

		$charset = $this->EE->TMPL->fetch_param('charset') !== FALSE ? $this->EE->TMPL->fetch_param('charset') : $this->EE->config->item('charset');

		if ($this->EE->TMPL->fetch_param('content_type') !== FALSE)
		{
			$this->set_content_type($this->EE->TMPL->fetch_param('content_type'), $this->EE->TMPL->fetch_param('charset'));
		}
		// Added by @pvledoux
		if ($this->EE->TMPL->fetch_param('content_disposition') !== FALSE)
		{
			$this->set_content_disposition($this->EE->TMPL->fetch_param('content_disposition'), $this->EE->TMPL->fetch_param('filename'));
		}
		else
		{
			//thanks @mistermuckle
			switch ($this->EE->TMPL->template_type)
			{
				case 'js':
					$this->set_content_type('text/javascript', $charset);
					break;
				case 'css':
					$this->set_content_type('text/css', $charset);
					break;
				default:
					$this->set_content_type('text/html', $charset);
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

		return $this->return_data = $this->EE->TMPL->tagdata;
	}

	/**
	 * set the http status code
	 *
	 * @param int $code ex. 404
	 *
	 * @return void
	 */
	protected function set_status($code)
	{
		$this->EE->output->set_status_header($code);
	}

	/**
	 * set the Location header
	 *
	 * @param string $location full url or template/template string
	 *
	 * @return void
	 */
	protected function set_location($location)
	{
		if (strpos($location, '{site_url}') !== FALSE)
		{
			$location = str_replace('{site_url}', $this->EE->functions->fetch_site_index(1), $location);
		}

		if (strpos($location, LD.'path=') !== FALSE)
		{
			$location = preg_replace_callback('/'.LD.'path=[\042\047]?(.*?)[\042\047]?'.RD.'/', array($this->EE->functions, 'create_url'), $location);
		}

		//it's not a proper url, so it's a template/template string, make it a proper url
		if ( ! preg_match('#^/|[a-z]+://#', $location))
		{
			$location = $this->EE->functions->create_url($location);
		}

		$this->EE->output->set_header('Location: '.$location);
	}

	/**
	 * set the Content-Type header
	 *
	 * @param string $content_type ex. "text/html", "application/json"
	 * @param string $charset ex. "utf-8", "iso-8859-1" (optional)
	 *
	 * @return void
	 */
	protected function set_content_type($content_type, $charset = '')
	{
		//add a charset if there isn't one already defined in the $content_type string
		if ($charset && strpos($content_type, 'charset=') === FALSE)
		{
			$content_type .= '; charset='.strtolower($charset);
		}

		$this->EE->output->set_header('Content-Type: '.$content_type);
	}

	/**
	 * set the Content-Disposition header
	 *
	 * @author Pv Ledoux (@pvledoux)
	 * @param string $content_disposition ex. "attachment"
	 * @param string $filename (optional)
	 *
	 * @return void
	 */
	protected function set_content_disposition($content_disposition, $filename = '')
	{
		//add a filename if there isn't one already defined in the $content_disposition string
		if ($filename && strpos($content_disposition, 'filename=') === FALSE)
		{
			$content_disposition .= '; filename='.strtolower($filename);
		}

		$this->EE->output->set_header('Content-Disposition: '.$content_disposition);
	}
}

/* End of file pi.http_header.php */
/* Location: ./system/expressionengine/third_party/http_header/pi.http_header.php */