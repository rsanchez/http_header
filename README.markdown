# HTTP Header #

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

	{exp:http_header status="302" location="{path=site/something}" terminate="yes"}

Set a 404 Status header

	{exp:http_header status="404"}

Set the Content-Type header to application/json

	{exp:http_header content_type="application/json"}

Set Content-Disposition to force the download

	{exp:http_header content_disposition="attachment" filename="myfile.xml"}
	
Test if the last_segment is the url_title and redirect if it is not
{exp:http_header status="307" location="{path={segment_1}/{segment_2}/{url_title}}" terminate="yes" test_a="{segment_3}" test_type="!=" test_b="{url_title}"}

For the above redirect, an additional segment (skip_betterworkflow) is needed if you are using better workflow
{exp:http_header status="307" location="{path={segment_1}/{segment_2}/{url_title}}" terminate="yes" test_a="{segment_3}" test_type="!=" test_b="{url_title}" skip_betterworkflow="yes"}

