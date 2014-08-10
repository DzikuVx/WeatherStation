import sqlite3, os, subprocess

from openweatherconfig import config

def main():

	conn = sqlite3.connect(os.path.dirname(os.path.realpath(__file__)) + '/data.db')

	cur = conn.cursor()
	cur.execute('SELECT Temperature, Humidity FROM readouts_external ORDER BY `Date` DESC LIMIT 1')

	data = cur.fetchone()

	if data == None:
		exit()

	cur = conn.cursor()
	cur.execute('SELECT Pressure FROM external_data ORDER BY `Date` DESC LIMIT 1')

	dataExt = cur.fetchone()

	if dataExt == None:
		exit()

	conn.close()

	callScript = "curl -d 'pressure="+str(dataExt[0])+"&humidity="+str(data[1])+"&temp="+str(data[0])+"&"+config['coords']+"' --user '"+config['user']+":"+config['password']+"' http://openweathermap.org/data/post"

	#print callScript

	p = subprocess.Popen(callScript, shell=True, stdout=subprocess.PIPE, stderr=subprocess.STDOUT)	

	#for line in p.stdout.readlines():
	#	print line
		
if __name__ == "__main__":
	main()