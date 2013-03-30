import subprocess

p = subprocess.Popen('sudo ./dht11_sensor', shell=True, stdout=subprocess.PIPE, stderr=subprocess.STDOUT)
for line in p.stdout.readlines():
    
    humidity = 0
    temperature = 0

    data = line.split("|")

    print data[0]
    print data[1]

    #print line