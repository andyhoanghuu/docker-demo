
## Instructions
Start and schedule a stop time for docker use PHP.
Steps:
1. Login SSH. Use http://phpseclib.sourceforge.net/. Example: https://phpseclib.com/docs/connect
2. Create 2 crontab sh files. One for register crontab, one for job actions. They will be written into the parent docker server when login success.
3. Execute the cron tab and start docker.
## Install
1. Run ``docker-compose up -d`` in the folder that contains docker-compose.yml
2. Create ``config.php`` file from ``config-sample.php`` for testing.
3. Try it!
