#! /bin/sh
# svn checkout svn://192.168.1.120/myproject/php/projects/filmsLib /home/projects/php/projects/devel/filmsLib --force

git archive --format tar -o ./deploy_image.tar $1;
tar -C /home/projects/php/projects/stable/majordomo/ -xvf ./deploy_image.tar
echo $1 > /home/projects/php/projects/stable/majordomo/deployment.marker
rm -f ./deploy_image.tar

#VERSION_SVN=`svn log svn://192.168.1.120/myproject/php/projects/ha/ --revision HEAD --quiet | grep -E 'r[0-9]+' | cut -d'|' -f1 | sed 's/ //g'`
#sudo svn export  svn://192.168.1.120/myproject/php/projects/ha /home/projects/php/projects/stable/ha --force
#sudo echo $VERSION_SVN > /home/projects/php/projects/stable/ha/deployment.marker

#sudo chown root:root -R  /home/projects/php/projects/stable/ha/
#sudo chmod 755 -R  /home/projects/php/projects/stable/ha/
#sudo chmod 777 -R  /home/projects/php/projects/stable/ha/data/
#sudo chmod 777 -R /home/projects/php/projects/stable/ha/assets
#sudo chmod 777 -R  /home/projects/php/projects/stable/ha/logs/

