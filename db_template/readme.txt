Installation

1. Make database and install db_terminal.sql dump into it

2. Copy contents of ./html/ folder into www-root of your web-server (or virtual host)

3. Apply write-for-all (0777) attributes for these folders (including sub-folders and files; use 0666 for files inside):
./cached/
./cms/
./debmes/
./modules/
./templates/
./objects/
./rc/
./saverestore/

4. Update ./config.php with your server settings including database (DB_ constants), PROJECT_DOMAIN, DOC_ROOT

5. Now you should be able to run the web-interface using http://<PROJECT_DOMAIN>/ in your browser.

6. There is also ./cycle.php script that should be running in the background for scheduling tasks.
You can run it using following procedure:

cd <DOC_ROOT>  
php ./cycle.php

(note: you should replace <DOC_ROOT> with your own path, for example: /home/projects/smarthome/html/)


Please use http://smartliving.ru/ for help and references.

Enjoy! :)

Best regards,
Sergei Dzheigalo (jey@tut.by)