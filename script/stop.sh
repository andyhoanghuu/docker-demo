#write out current crontab
crontab -l > lofstopdemo
#echo new cron into cron file
echo "*/1 * * * * sh /home/hieu/CMS/ce/magentoce241/script/stop-docker.sh" >> lofstopdemo
#install new cron file
crontab lofstopdemo
#rm mycron
rm lofstopdemo