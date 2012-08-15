<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$plugin_info = array(
	'pi_name' => 'HTTP Header',
	'pi_version' => '1.0.3',
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
* content_disposition - set a Content-Disposition (ex: attachment) with a filename
* terminate - set to "yes" to prevent any other output from the template
* test_a - The left side of the test
* test_type - The test type (==, !=, <=, <, >, >=).  (The redirect will happen if the test is true.)
* test_b - The right side of the test
* skip_betterworkflow - If set to yes, the redirect will not happen when viewing a better workflow draft

## Examples

Do a 301 redirect

	{exp:http_header status="301" location="{path=site/something}" terminate="yes"}

Set a 404 Status header

	{exp:http_header status="404"}

Set the Content-Type header to application/json

	{exp:http_header content_type="application/json"}

Set Content-Disposition to force the download

	{exp:http_header content_disposition="attachment" filename="myfile.xml"}
	
Test if the last_segment is the url_title and redirect if it is not
{exp:http_header status="307" location="{path={segment_1}/{segment_2}/{url_title}}" terminate="yes" test_a="{segment_3}" test_type="!=" test_b="{url_title}"}

For the above redirect, an additional segment (skip_betterworkflow) is needed if you are using better workflow
{exp:http_header status="307" location="{path={segment_1}/{segment_2}/{url_title}}" terminate="yes" test_a="{segment_3}" test_type="!=" test_b="{url_title}" skip_betterworkflow="yes"}',
);

/**
 * HTTP Header
 *
 * Set the HTTP Headers for your template.
 *
 * @author Rob Sanchez
 * @link https://github.com/rsanchez/http_header
 * @version 1.0.3
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

		// Added By @wiseloren
		// Allows for double sided conditionals to be processed eg (segment_3 != url_title)
		if ($this->EE->TMPL->fetch_param('test_a') !== FALSE && $this->EE->TMPL->fetch_param('test_type') !== FALSE
		 && $this->EE->TMPL->fetch_param('test_b') !== FALSE) {
		 	switch ($this->EE->TMPL->fetch_param('test_type')) {
		 		// All tests are reversed with a ! since we want to return if the test fails
		 		case '==':
		 			if (!($this->EE->TMPL->fetch_param('test_a') == $this->EE->TMPL->fetch_param('test_b'))) {
		 				$this->EE->TMPL->log_item('Http Header: Test failed. Header was not processed.');
		 				return '';
		 			}
		 		break;
		 		case '!=':
		 			if (!($this->EE->TMPL->fetch_param('test_a') != $this->EE->TMPL->fetch_param('test_b'))) {
		 				$this->EE->TMPL->log_item('Http Header: Test failed. Header was not processed.');
		 				return '';
		 			}
		 		break;
		 		case '>':
		 			if (!($this->EE->TMPL->fetch_param('test_a') > $this->EE->TMPL->fetch_param('test_b'))) {
		 				$this->EE->TMPL->log_item('Http Header: Test failed. Header was not processed.');
		 				return '';
		 			}
		 		break;
		 		case '>=':
		 			if (!($this->EE->TMPL->fetch_param('test_a') >= $this->EE->TMPL->fetch_param('test_b'))) {
		 				$this->EE->TMPL->log_item('Http Header: Test failed. Header was not processed.');
		 				return '';
		 			}
		 		break;
		 		case '<':
		 			if (!($this->EE->TMPL->fetch_param('test_a') < $this->EE->TMPL->fetch_param('test_b'))) {
		 				$this->EE->TMPL->log_item('Http Header: Test failed. Header was not processed.');
		 				return '';
		 			}
		 		break;
		 		case '<=':
		 			if (!($this->EE->TMPL->fetch_param('test_a') <= $this->EE->TMPL->fetch_param('test_b'))) {
		 				$this->EE->TMPL->log_item('Http Header: Test failed. Header was not processed.');
		 				return '';
		 			}
		 		break;
		 		default :
					$this->EE->TMPL->log_item('Http Header received an invalid test_type.');
				break;
		 	}
		} 
		// Added By @wiseloren
		// Allows to skip for better workflow
		if ($this->EE->TMPL->fetch_param('skip_betterworkflow') && $this->EE->TMPL->fetch_param('skip_betterworkflow') == 'yes') {
			if (isset($this->EE->session->cache['ep_better_workflow']['is_draft']) 
				&& $this->EE->session->cache['ep_better_workflow']['is_draft']) {
				$this->EE->TMPL->log_item('Http Header: Skipping, this is a better workflow draft.');
				return '';
			}
		}
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
			$this->set_content_type($this->EE->TMPL->fetch_param('content_type'), $charset);
		}
		// Added by @pvledoux
		if ($this->EE->TMPL->fetch_param('content_disposition') !== FALSE)
		{
			$this->set_content_disposition($this->EE->TMPL->fetch_param('content_disposition'), $this->EE->TMPL->fetch_param('filename'));
		}
		else
		{
			// Conditional wrapper added by @pashamalla
			if ($this->EE->TMPL->fetch_param('content_type') === FALSE)
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