import sqlite3, os, subprocess
import urllib,urllib2

from thingspeakconfig import config

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

	encodedAttributes = {
		'key': config['api'],
		'field1': data[0],
		'field2': data[1],
		'field3': dataExt[0]
	}
	req = urllib2.Request(config['url'] + "?" + urllib.urlencode(encodedAttributes))
	response=urllib2.urlopen(req)
	print "Posted!"

if __name__ == "__main__":
	main()