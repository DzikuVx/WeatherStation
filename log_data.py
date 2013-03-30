import subprocess

p = subprocess.Popen('sudo ./dht11_sensor', shell=True, stdout=subprocess.PIPE, stderr=subprocess.STDOUT)
for line in p.stdout.readlines():
    print line