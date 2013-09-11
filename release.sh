#! /bin/sh
# svn checkout svn://192.168.1.120/myproject/php/projects/filmsLib /home/projects/php/projects/devel/filmsLib --force

mkdir -p /home/projects/data/majordomo/lock
mkdir -p /home/projects/data/majordomo/log
sudo chown root:root -R /home/projects/data/majordomo/lock
sudo chown root:root -R /home/projects/data/majordomo/log
sudo chmod 777 -R /home/projects/data/majordomo/lock
sudo chmod 777 -R /home/projects/data/majordomo/log

rm -f /tmp/deploy_image.tar
git archive --format tar -o /tmp/deploy_image.tar $1;
tar -C /home/projects/php/projects/stable/majordomo/ -xvf /tmp/deploy_image.tar
echo $1 > /home/projects/php/projects/stable/majordomo/deployment.marker
rm -f /tmp/deploy_image.tar

rm -rf /home/projects/php/projects/stable/majordomo/config/current
ln -s  /home/projects/php/projects/stable/majordomo/config/stable/ /home/projects/php/projects/stable/majordomo/config/current

sudo chown root:root -R  /home/projects/php/projects/stable/majordomo/
sudo chmod 755 -R  /home/projects/php/projects/stable/majordomo/
#sudo chmod 777 -R  /home/projects/php/projects/stable/majordomo/

##(crontab -l ; echo "0 * * * * wget -O - -q http://www.example.com/cron.php") | crontab


#VERSION_SVN=`svn log svn://192.168.1.120/myproject/php/projects/ha/ --revision HEAD --quiet | grep -E 'r[0-9]+' | cut -d'|' -f1 | sed 's/ //g'`
#sudo svn export  svn://192.168.1.120/myproject/php/projects/ha /home/projects/php/projects/stable/ha --force
#sudo echo $VERSION_SVN > /home/projects/php/projects/stable/ha/deployment.marker

#sudo chown root:root -R  /home/projects/php/projects/stable/ha/
#sudo chmod 755 -R  /home/projects/php/projects/stable/ha/
#sudo chmod 777 -R  /home/projects/php/projects/stable/ha/data/
#sudo chmod 777 -R /home/projects/php/projects/stable/ha/assets
#sudo chmod 777 -R  /home/projects/php/projects/stable/ha/logs/

