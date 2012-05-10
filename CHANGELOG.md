DEVELOPER NOTES
====

**TO-DO:**
>* sub-controllers support (this strategy could be named HMVC+ ? )
* adapt Ac_PDO
* views and templates with multiple theme support
* i18n strings
* Models with ActiveRecord pattern (Ac_Model_Record)
* code documentation, wiki and demos (fb-style wall share demo)

<br>
**CHANGELOG:**

v4.0.0-WIP
> * __mod:__   Now the framework targets only PHP 5.3+
* __mod:__     Ac_Global renamed to Ac_Model_Globals, Ac_Model_Globals_*,
               Ac_Dbc renamed to Ac_Storage_Pdo, Ac_Array to Ac_Object,
               Ac_Cache to Ac_Storage_Cache,
               Ac_Record to Ac_Model_Record, Ac_Logger to Ac_Log_file, ...
* __new:__     Introducing magic static calls (on<eventname>, trigger<eventname>, etc) 
* __new:__     Ac_Singleton, Ac_Loader, Ac_Log, Ac_Observer, ...
* __mod:__     The main Ac class has been now split into different singleton classes: Ac_Loader, Ac_Observer
* __new:__     index.php now detects if there is an install.php file in the 'app' dir
* __wip:__     Oauth2 consumer / provider classes
* __wip:__     JSON-RPC client / server classes with JSONP compat.
* __new:__     Set Access-Control Headers is now easier
* __new:__     Improved Log classes & Ac_Log_Flash for flash messaging via session
* __other:__     improved security making app and system folders private using .htaccess. check.php is now a private file.
               other bugfixes and improvements (better code readability and naming conventions)
* __mod:__     Ac::context, loader, request, router and response accessors are now setters too
* __mod:__     Version changed to 4.0.0-WIP to target our internal versioning system of previous Anidcore (1.0-3.0 are not Open-sourced).
               This one has been rewritten from scratch.