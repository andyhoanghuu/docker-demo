cd /home/hieu/CMS/ce/magentoce241 && sh bin/stop
crontab -u hieu -l | grep -v 'sh /home/hieu/CMS/ce/magentoce241/stop-docker.sh'  | crontab -u hieu -
