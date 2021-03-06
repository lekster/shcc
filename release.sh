#! /bin/sh
# svn checkout svn://192.168.1.120/myproject/php/projects/filmsLib /home/projects/php/projects/devel/filmsLib --force

#mkdir -p 
#sudo chown root:root -R
#sudo chmod 777 -R


mkdir -p /home/projects/data/majordomo/lock
mkdir -p /home/projects/data/majordomo/log
sudo chown root:root -R /home/projects/data/majordomo/lock
sudo chown root:root -R /home/projects/data/majordomo/log
sudo chmod 777 -R /home/projects/data/majordomo/lock
sudo chmod 777 -R /home/projects/data/majordomo/log

mkdir -p /home/projects/data/majordomo/backup
sudo chown root:root -R /home/projects/data/majordomo/backup
sudo chmod 777 -R /home/projects/data/majordomo/backup

mkdir -p /home/projects/data/majordomo/cache
sudo chown root:root -R /home/projects/data/majordomo/cache
sudo chmod 777 -R /home/projects/data/majordomo/cache

mkdir -p /home/projects/data/majordomo/cms/
sudo chown root:root -R /home/projects/data/majordomo/cms/
sudo chmod 777 -R /home/projects/data/majordomo/cms/

mkdir -p /home/projects/data/majordomo/texts/
sudo chown root:root -R /home/projects/data/majordomo/texts/
sudo chmod 777 -R /home/projects/data/majordomo/texts/

mkdir -p /home/projects/data/majordomo/sounds/
sudo chown root:root -R /home/projects/data/majordomo/sounds/
sudo chmod 777 -R /home/projects/data/majordomo/sounds/

rm -f /tmp/deploy_image.tar
git archive --format tar -o /tmp/deploy_image.tar $1;
tar -C /home/projects/php/projects/stable/majordomo/ -xvf /tmp/deploy_image.tar
echo `date` > /home/projects/php/projects/stable/majordomo/deployment.marker
echo 'tag version:':$1 >> /home/projects/php/projects/stable/majordomo/deployment.marker
rm -f /tmp/deploy_image.tar

rm -rf /home/projects/php/projects/stable/majordomo/config/current
ln -s  /home/projects/php/projects/stable/majordomo/config/stable/ /home/projects/php/projects/stable/majordomo/config/current

sudo chown root:root -R  /home/projects/php/projects/stable/majordomo/
sudo chmod 755 -R  /home/projects/php/projects/stable/majordomo/
#sudo chmod 777 -R  /home/projects/php/projects/stable/majordomo/


##!!!!CRON
##(crontab -l ; echo "* * * * * /usr/local/bin/php -q /home/projects/php/projects/stable/majordomo/cron_worker/device_plugin_job.php > /dev/null") | crontab
#* * * * * /usr/local/bin/php -q /home/projects/php/projects/stable/majordomo/cron_worker/cycle_work.php > /dev/null
#* * * * * /usr/local/bin/php -q /home/projects/php/projects/stable/majordomo/cron_worker/device_plugin_job.php > /dev/null

#VERSION_SVN=`svn log svn://192.168.1.120/myproject/php/projects/ha/ --revision HEAD --quiet | grep -E 'r[0-9]+' | cut -d'|' -f1 | sed 's/ //g'`
#sudo svn export  svn://192.168.1.120/myproject/php/projects/ha /home/projects/php/projects/stable/ha --force
#sudo echo $VERSION_SVN > /home/projects/php/projects/stable/ha/deployment.marker

#sudo chown root:root -R  /home/projects/php/projects/stable/ha/
#sudo chmod 755 -R  /home/projects/php/projects/stable/ha/
#sudo chmod 777 -R  /home/projects/php/projects/stable/ha/data/
#sudo chmod 777 -R /home/projects/php/projects/stable/ha/assets
#sudo chmod 777 -R  /home/projects/php/projects/stable/ha/logs/

