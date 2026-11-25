# ZAP Scanning Report

ZAP by [Checkmarx](https://checkmarx.com/).


## Summary of Alerts

| Risk Level | Number of Alerts |
| --- | --- |
| High | 0 |
| Medium | 3 |
| Low | 3 |
| Informational | 2 |




## Insights

| Level | Reason | Site | Description | Statistic |
| --- | --- | --- | --- | --- |
| Low | Warning |  | ZAP errors logged - see the zap.log file for details | 12    |
| Low | Warning |  | ZAP warnings logged - see the zap.log file for details | 1    |
| Info | Informational |  | Percentage of network failures | 52 % |
| Info | Informational | http://host.docker.internal:8000 | Percentage of responses with status code 2xx | 72 % |
| Info | Informational | http://host.docker.internal:8000 | Percentage of responses with status code 3xx | 18 % |
| Info | Informational | http://host.docker.internal:8000 | Percentage of responses with status code 4xx | 9 % |
| Info | Informational | http://host.docker.internal:8000 | Percentage of endpoints with content type application/javascript | 22 % |
| Info | Informational | http://host.docker.internal:8000 | Percentage of endpoints with content type image/vnd.microsoft.icon | 22 % |
| Info | Informational | http://host.docker.internal:8000 | Percentage of endpoints with content type text/css | 11 % |
| Info | Informational | http://host.docker.internal:8000 | Percentage of endpoints with content type text/html | 33 % |
| Info | Informational | http://host.docker.internal:8000 | Percentage of endpoints with content type text/plain | 11 % |
| Info | Informational | http://host.docker.internal:8000 | Percentage of endpoints with method GET | 100 % |
| Info | Informational | http://host.docker.internal:8000 | Count of total endpoints | 9    |
| Info | Informational | http://host.docker.internal:8000 | Percentage of slow responses | 100 % |







## Alerts

| Name | Risk Level | Number of Instances |
| --- | --- | --- |
| CSP: script-src unsafe-eval | Medium | 3 |
| CSP: script-src unsafe-inline | Medium | 3 |
| CSP: style-src unsafe-inline | Medium | 3 |
| Big Redirect Detected (Potential Sensitive Information Leak) | Low | 2 |
| Cookie No HttpOnly Flag | Low | 3 |
| X-Content-Type-Options Header Missing | Low | 5 |
| Modern Web Application | Informational | 2 |
| Session Management Response Identified | Informational | 3 |




## Alert Detail



### [ CSP: script-src unsafe-eval ](https://www.zaproxy.org/docs/alerts/10055/)



##### Medium (High)

### Description

Content Security Policy (CSP) is an added layer of security that helps to detect and mitigate certain types of attacks. Including (but not limited to) Cross Site Scripting (XSS), and data injection attacks. These attacks are used for everything from data theft to site defacement or distribution of malware. CSP provides a set of standard HTTP headers that allow website owners to declare approved sources of content that browsers should be allowed to load on that page — covered types are JavaScript, CSS, HTML frames, fonts, images and embeddable objects such as Java applets, ActiveX, audio and video files.

* URL: http://host.docker.internal:8000
  * Node Name: `http://host.docker.internal:8000`
  * Method: `GET`
  * Parameter: `Content-Security-Policy`
  * Attack: ``
  * Evidence: `default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net http://localhost:5173 ws://localhost:5173; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.bunny.net; font-src 'self' data: https://cdn.jsdelivr.net; img-src 'self' data: https://ui-avatars.com; connect-src 'self' http://localhost:5173 ws://localhost:5173; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'`
  * Other Info: `script-src includes unsafe-eval.`
* URL: http://host.docker.internal:8000/login
  * Node Name: `http://host.docker.internal:8000/login`
  * Method: `GET`
  * Parameter: `Content-Security-Policy`
  * Attack: ``
  * Evidence: `default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net http://localhost:5173 ws://localhost:5173; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.bunny.net; font-src 'self' data: https://cdn.jsdelivr.net; img-src 'self' data: https://ui-avatars.com; connect-src 'self' http://localhost:5173 ws://localhost:5173; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'`
  * Other Info: `script-src includes unsafe-eval.`
* URL: http://host.docker.internal:8000/sitemap.xml
  * Node Name: `http://host.docker.internal:8000/sitemap.xml`
  * Method: `GET`
  * Parameter: `Content-Security-Policy`
  * Attack: ``
  * Evidence: `default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net http://localhost:5173 ws://localhost:5173; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.bunny.net; font-src 'self' data: https://cdn.jsdelivr.net; img-src 'self' data: https://ui-avatars.com; connect-src 'self' http://localhost:5173 ws://localhost:5173; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'`
  * Other Info: `script-src includes unsafe-eval.`


Instances: 3

### Solution

Ensure that your web server, application server, load balancer, etc. is properly configured to set the Content-Security-Policy header.

### Reference


* [ https://www.w3.org/TR/CSP/ ](https://www.w3.org/TR/CSP/)
* [ https://caniuse.com/#search=content+security+policy ](https://caniuse.com/#search=content+security+policy)
* [ https://content-security-policy.com/ ](https://content-security-policy.com/)
* [ https://github.com/HtmlUnit/htmlunit-csp ](https://github.com/HtmlUnit/htmlunit-csp)
* [ https://web.dev/articles/csp#resource-options ](https://web.dev/articles/csp#resource-options)


#### CWE Id: [ 693 ](https://cwe.mitre.org/data/definitions/693.html)


#### WASC Id: 15

#### Source ID: 3

### [ CSP: script-src unsafe-inline ](https://www.zaproxy.org/docs/alerts/10055/)



##### Medium (High)

### Description

Content Security Policy (CSP) is an added layer of security that helps to detect and mitigate certain types of attacks. Including (but not limited to) Cross Site Scripting (XSS), and data injection attacks. These attacks are used for everything from data theft to site defacement or distribution of malware. CSP provides a set of standard HTTP headers that allow website owners to declare approved sources of content that browsers should be allowed to load on that page — covered types are JavaScript, CSS, HTML frames, fonts, images and embeddable objects such as Java applets, ActiveX, audio and video files.

* URL: http://host.docker.internal:8000
  * Node Name: `http://host.docker.internal:8000`
  * Method: `GET`
  * Parameter: `Content-Security-Policy`
  * Attack: ``
  * Evidence: `default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net http://localhost:5173 ws://localhost:5173; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.bunny.net; font-src 'self' data: https://cdn.jsdelivr.net; img-src 'self' data: https://ui-avatars.com; connect-src 'self' http://localhost:5173 ws://localhost:5173; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'`
  * Other Info: `script-src includes unsafe-inline.`
* URL: http://host.docker.internal:8000/login
  * Node Name: `http://host.docker.internal:8000/login`
  * Method: `GET`
  * Parameter: `Content-Security-Policy`
  * Attack: ``
  * Evidence: `default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net http://localhost:5173 ws://localhost:5173; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.bunny.net; font-src 'self' data: https://cdn.jsdelivr.net; img-src 'self' data: https://ui-avatars.com; connect-src 'self' http://localhost:5173 ws://localhost:5173; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'`
  * Other Info: `script-src includes unsafe-inline.`
* URL: http://host.docker.internal:8000/sitemap.xml
  * Node Name: `http://host.docker.internal:8000/sitemap.xml`
  * Method: `GET`
  * Parameter: `Content-Security-Policy`
  * Attack: ``
  * Evidence: `default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net http://localhost:5173 ws://localhost:5173; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.bunny.net; font-src 'self' data: https://cdn.jsdelivr.net; img-src 'self' data: https://ui-avatars.com; connect-src 'self' http://localhost:5173 ws://localhost:5173; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'`
  * Other Info: `script-src includes unsafe-inline.`


Instances: 3

### Solution

Ensure that your web server, application server, load balancer, etc. is properly configured to set the Content-Security-Policy header.

### Reference


* [ https://www.w3.org/TR/CSP/ ](https://www.w3.org/TR/CSP/)
* [ https://caniuse.com/#search=content+security+policy ](https://caniuse.com/#search=content+security+policy)
* [ https://content-security-policy.com/ ](https://content-security-policy.com/)
* [ https://github.com/HtmlUnit/htmlunit-csp ](https://github.com/HtmlUnit/htmlunit-csp)
* [ https://web.dev/articles/csp#resource-options ](https://web.dev/articles/csp#resource-options)


#### CWE Id: [ 693 ](https://cwe.mitre.org/data/definitions/693.html)


#### WASC Id: 15

#### Source ID: 3

### [ CSP: style-src unsafe-inline ](https://www.zaproxy.org/docs/alerts/10055/)



##### Medium (High)

### Description

Content Security Policy (CSP) is an added layer of security that helps to detect and mitigate certain types of attacks. Including (but not limited to) Cross Site Scripting (XSS), and data injection attacks. These attacks are used for everything from data theft to site defacement or distribution of malware. CSP provides a set of standard HTTP headers that allow website owners to declare approved sources of content that browsers should be allowed to load on that page — covered types are JavaScript, CSS, HTML frames, fonts, images and embeddable objects such as Java applets, ActiveX, audio and video files.

* URL: http://host.docker.internal:8000
  * Node Name: `http://host.docker.internal:8000`
  * Method: `GET`
  * Parameter: `Content-Security-Policy`
  * Attack: ``
  * Evidence: `default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net http://localhost:5173 ws://localhost:5173; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.bunny.net; font-src 'self' data: https://cdn.jsdelivr.net; img-src 'self' data: https://ui-avatars.com; connect-src 'self' http://localhost:5173 ws://localhost:5173; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'`
  * Other Info: `style-src includes unsafe-inline.`
* URL: http://host.docker.internal:8000/login
  * Node Name: `http://host.docker.internal:8000/login`
  * Method: `GET`
  * Parameter: `Content-Security-Policy`
  * Attack: ``
  * Evidence: `default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net http://localhost:5173 ws://localhost:5173; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.bunny.net; font-src 'self' data: https://cdn.jsdelivr.net; img-src 'self' data: https://ui-avatars.com; connect-src 'self' http://localhost:5173 ws://localhost:5173; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'`
  * Other Info: `style-src includes unsafe-inline.`
* URL: http://host.docker.internal:8000/sitemap.xml
  * Node Name: `http://host.docker.internal:8000/sitemap.xml`
  * Method: `GET`
  * Parameter: `Content-Security-Policy`
  * Attack: ``
  * Evidence: `default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net http://localhost:5173 ws://localhost:5173; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.bunny.net; font-src 'self' data: https://cdn.jsdelivr.net; img-src 'self' data: https://ui-avatars.com; connect-src 'self' http://localhost:5173 ws://localhost:5173; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'`
  * Other Info: `style-src includes unsafe-inline.`


Instances: 3

### Solution

Ensure that your web server, application server, load balancer, etc. is properly configured to set the Content-Security-Policy header.

### Reference


* [ https://www.w3.org/TR/CSP/ ](https://www.w3.org/TR/CSP/)
* [ https://caniuse.com/#search=content+security+policy ](https://caniuse.com/#search=content+security+policy)
* [ https://content-security-policy.com/ ](https://content-security-policy.com/)
* [ https://github.com/HtmlUnit/htmlunit-csp ](https://github.com/HtmlUnit/htmlunit-csp)
* [ https://web.dev/articles/csp#resource-options ](https://web.dev/articles/csp#resource-options)


#### CWE Id: [ 693 ](https://cwe.mitre.org/data/definitions/693.html)


#### WASC Id: 15

#### Source ID: 3

### [ Big Redirect Detected (Potential Sensitive Information Leak) ](https://www.zaproxy.org/docs/alerts/10044/)



##### Low (Medium)

### Description

The server has responded with a redirect that seems to provide a large response. This may indicate that although the server sent a redirect it also responded with body content (which may include sensitive details, PII, etc.).

* URL: http://host.docker.internal:8000
  * Node Name: `http://host.docker.internal:8000`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: ``
  * Other Info: `Location header URI length: 38 [http://host.docker.internal:8000/login].
Predicted response size: 338.
Response Body Length: 398.`
* URL: http://host.docker.internal:8000/
  * Node Name: `http://host.docker.internal:8000/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: ``
  * Other Info: `Location header URI length: 38 [http://host.docker.internal:8000/login].
Predicted response size: 338.
Response Body Length: 398.`


Instances: 2

### Solution

Ensure that no sensitive information is leaked via redirect responses. Redirect responses should have almost no content.

### Reference



#### CWE Id: [ 201 ](https://cwe.mitre.org/data/definitions/201.html)


#### WASC Id: 13

#### Source ID: 3

### [ Cookie No HttpOnly Flag ](https://www.zaproxy.org/docs/alerts/10010/)



##### Low (Medium)

### Description

A cookie has been set without the HttpOnly flag, which means that the cookie can be accessed by JavaScript. If a malicious script can be run on this page then the cookie will be accessible and can be transmitted to another site. If this is a session cookie then session hijacking may be possible.

* URL: http://host.docker.internal:8000
  * Node Name: `http://host.docker.internal:8000`
  * Method: `GET`
  * Parameter: `XSRF-TOKEN`
  * Attack: ``
  * Evidence: `Set-Cookie: XSRF-TOKEN`
  * Other Info: ``
* URL: http://host.docker.internal:8000/
  * Node Name: `http://host.docker.internal:8000/`
  * Method: `GET`
  * Parameter: `XSRF-TOKEN`
  * Attack: ``
  * Evidence: `Set-Cookie: XSRF-TOKEN`
  * Other Info: ``
* URL: http://host.docker.internal:8000/login
  * Node Name: `http://host.docker.internal:8000/login`
  * Method: `GET`
  * Parameter: `XSRF-TOKEN`
  * Attack: ``
  * Evidence: `Set-Cookie: XSRF-TOKEN`
  * Other Info: ``


Instances: 3

### Solution

Ensure that the HttpOnly flag is set for all cookies.

### Reference


* [ https://owasp.org/www-community/HttpOnly ](https://owasp.org/www-community/HttpOnly)


#### CWE Id: [ 1004 ](https://cwe.mitre.org/data/definitions/1004.html)


#### WASC Id: 13

#### Source ID: 3

### [ X-Content-Type-Options Header Missing ](https://www.zaproxy.org/docs/alerts/10021/)



##### Low (Medium)

### Description

The Anti-MIME-Sniffing header X-Content-Type-Options was not set to 'nosniff'. This allows older versions of Internet Explorer and Chrome to perform MIME-sniffing on the response body, potentially causing the response body to be interpreted and displayed as a content type other than the declared content type. Current (early 2014) and legacy versions of Firefox will use the declared content type (if one is set), rather than performing MIME-sniffing.

* URL: http://host.docker.internal:8000/build/assets/app-0BmDVrxS.css
  * Node Name: `http://host.docker.internal:8000/build/assets/app-0BmDVrxS.css`
  * Method: `GET`
  * Parameter: `x-content-type-options`
  * Attack: ``
  * Evidence: ``
  * Other Info: `This issue still applies to error type pages (401, 403, 500, etc.) as those pages are often still affected by injection issues, in which case there is still concern for browsers sniffing pages away from their actual content type.
At "High" threshold this scan rule will not alert on client or server error responses.`
* URL: http://host.docker.internal:8000/build/assets/app-DbY_8p28.js
  * Node Name: `http://host.docker.internal:8000/build/assets/app-DbY_8p28.js`
  * Method: `GET`
  * Parameter: `x-content-type-options`
  * Attack: ``
  * Evidence: ``
  * Other Info: `This issue still applies to error type pages (401, 403, 500, etc.) as those pages are often still affected by injection issues, in which case there is still concern for browsers sniffing pages away from their actual content type.
At "High" threshold this scan rule will not alert on client or server error responses.`
* URL: http://host.docker.internal:8000/favicon.ico
  * Node Name: `http://host.docker.internal:8000/favicon.ico`
  * Method: `GET`
  * Parameter: `x-content-type-options`
  * Attack: ``
  * Evidence: ``
  * Other Info: `This issue still applies to error type pages (401, 403, 500, etc.) as those pages are often still affected by injection issues, in which case there is still concern for browsers sniffing pages away from their actual content type.
At "High" threshold this scan rule will not alert on client or server error responses.`
* URL: http://host.docker.internal:8000/favicon.ico%3Fv=2
  * Node Name: `http://host.docker.internal:8000/favicon.ico (v)`
  * Method: `GET`
  * Parameter: `x-content-type-options`
  * Attack: ``
  * Evidence: ``
  * Other Info: `This issue still applies to error type pages (401, 403, 500, etc.) as those pages are often still affected by injection issues, in which case there is still concern for browsers sniffing pages away from their actual content type.
At "High" threshold this scan rule will not alert on client or server error responses.`
* URL: http://host.docker.internal:8000/robots.txt
  * Node Name: `http://host.docker.internal:8000/robots.txt`
  * Method: `GET`
  * Parameter: `x-content-type-options`
  * Attack: ``
  * Evidence: ``
  * Other Info: `This issue still applies to error type pages (401, 403, 500, etc.) as those pages are often still affected by injection issues, in which case there is still concern for browsers sniffing pages away from their actual content type.
At "High" threshold this scan rule will not alert on client or server error responses.`


Instances: 5

### Solution

Ensure that the application/web server sets the Content-Type header appropriately, and that it sets the X-Content-Type-Options header to 'nosniff' for all web pages.
If possible, ensure that the end user uses a standards-compliant and modern web browser that does not perform MIME-sniffing at all, or that can be directed by the web application/web server to not perform MIME-sniffing.

### Reference


* [ https://learn.microsoft.com/en-us/previous-versions/windows/internet-explorer/ie-developer/compatibility/gg622941(v=vs.85) ](https://learn.microsoft.com/en-us/previous-versions/windows/internet-explorer/ie-developer/compatibility/gg622941(v=vs.85))
* [ https://owasp.org/www-community/Security_Headers ](https://owasp.org/www-community/Security_Headers)


#### CWE Id: [ 693 ](https://cwe.mitre.org/data/definitions/693.html)


#### WASC Id: 15

#### Source ID: 3

### [ Modern Web Application ](https://www.zaproxy.org/docs/alerts/10109/)



##### Informational (Medium)

### Description

The application appears to be a modern web application. If you need to explore it automatically then the Client Spider may well be more effective than the standard one.

* URL: http://host.docker.internal:8000
  * Node Name: `http://host.docker.internal:8000`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `<script type="module" src="/build/assets/app-DbY_8p28.js" data-navigate-track="reload"></script>`
  * Other Info: `No links have been found while there are scripts, which is an indication that this is a modern web application.`
* URL: http://host.docker.internal:8000/login
  * Node Name: `http://host.docker.internal:8000/login`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `<script type="module" src="/build/assets/app-DbY_8p28.js" data-navigate-track="reload"></script>`
  * Other Info: `No links have been found while there are scripts, which is an indication that this is a modern web application.`


Instances: 2

### Solution

This is an informational alert and so no changes are required.

### Reference




#### Source ID: 3

### [ Session Management Response Identified ](https://www.zaproxy.org/docs/alerts/10112/)



##### Informational (Medium)

### Description

The given response has been identified as containing a session management token. The 'Other Info' field contains a set of header tokens that can be used in the Header Based Session Management Method. If the request is in a context which has a Session Management Method set to "Auto-Detect" then this rule will change the session management to use the tokens identified.

* URL: http://host.docker.internal:8000
  * Node Name: `http://host.docker.internal:8000`
  * Method: `GET`
  * Parameter: `laravel_session`
  * Attack: ``
  * Evidence: `laravel_session`
  * Other Info: `cookie:laravel_session
cookie:XSRF-TOKEN`
* URL: http://host.docker.internal:8000/
  * Node Name: `http://host.docker.internal:8000/`
  * Method: `GET`
  * Parameter: `laravel_session`
  * Attack: ``
  * Evidence: `laravel_session`
  * Other Info: `cookie:laravel_session
cookie:XSRF-TOKEN`
* URL: http://host.docker.internal:8000/login
  * Node Name: `http://host.docker.internal:8000/login`
  * Method: `GET`
  * Parameter: `laravel_session`
  * Attack: ``
  * Evidence: `laravel_session`
  * Other Info: `cookie:laravel_session
cookie:XSRF-TOKEN`


Instances: 3

### Solution

This is an informational alert rather than a vulnerability and so there is nothing to fix.

### Reference


* [ https://www.zaproxy.org/docs/desktop/addons/authentication-helper/session-mgmt-id/ ](https://www.zaproxy.org/docs/desktop/addons/authentication-helper/session-mgmt-id/)



#### Source ID: 3


