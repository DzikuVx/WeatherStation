#set SHELL='/bin/bash'
#set PWD='/home/pi/raspberry_temperature_log'

set > /home/pi/run.log

python /home/pi/raspberry_temperature_log/log_data.py

