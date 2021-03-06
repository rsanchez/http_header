# HTTP Header

Set the HTTP Headers for your template.

## Parameters

* status - set an HTTP Status code
* location - set a location for redirection
* content_type - set a Content-Type header
* charset - set a charset in the Content-Type header
* content_disposition - set a Content-Disposition header (ex: attachment) with a filename
* content_language - set a Content-Language header
* cache_seconds - set to a non-zero number to set caching headers; set to 0 to force no-cache
* terminate - set to "yes" to prevent any other output from the template
* vary - set a Vary header
* access_control_allow_origin - set a Access-Control-Allow-Origin header
* x_frame_options - set X-Frame-Options header to protect from clickjacking (control usage in iFrames)

## Examples

Do a 301 redirect

	{exp:http_header status="301" location="{path=site/something}" terminate="yes"}

Set a 404 Status header

	{exp:http_header status="404"}

Set the Content-Type header to application/json

	{exp:http_header content_type="application/json"}

Set Content-Disposition to force the download

	{exp:http_header content_disposition="attachment" filename="myfile.xml"}

Set the Pragma, Cache-control, and Expires headers to set a 5 minute (300 second) cache

	{exp:http_header cache_seconds="300"}

Set the Content-Language header to "en"

	{exp:http_header content_language="en"}

Set the Vary header to User-Agent

    {exp:http_header vary="User-Agent"}

Set the Access-Control-Allow-Origin header to allow all

    {exp:http_header access_control_allow_origin="*"}

Set the X-Frame-Options header to a same website origin

    {exp:http_header x_frame_options="SAMEORIGIN"}

Options available for x_frame_options: DENY, SAMEORIGIN or ALLOW-FROM uri (replace uri for valid uri)
