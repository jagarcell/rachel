chgrp -R www-data /var/www
chmod -R 775 /var/www

chgrp -R www-data /etc/apache2
chmod -R 775 /etc/apache2

chgrp www-data /etc/hosts
chmod 775 /etc/hosts

cat >> /etc/sudoers <<EOF
www-data ALL=NOPASSWD:ALL
EOF