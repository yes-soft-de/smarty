
/bin/bash -c 'ls -la /var/www/html/wp-content/themes; chmod -R 777 /var/www/html/wp-content/themes; ls -la /var/www/html/wp-content/themes';
service apache2 start;

