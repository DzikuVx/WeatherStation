# -*- coding: utf-8 -*-

import tweetpony, os, sqlite3

from config import config

def main():
	api = tweetpony.API(consumer_key = config['consumer_key'], consumer_secret = config['consumer_secret'], access_token = config['access_token'], access_token_secret = config['access_token_secret'])
	user = api.user

	conn = sqlite3.connect(os.path.dirname(os.path.realpath(__file__)) + '/../data.db')

	cur = conn.cursor()
	cur.execute('SELECT Temperature, Humidity FROM readouts_external ORDER BY `Date` DESC LIMIT 1')

	data = cur.fetchone()

	if data == None:
		exit()

	conn.close()

	try:
		api.update_status(status = u'Witaj Dobra, mamy właśnie ' + unicode(str(data[0])) + u'C i ' + unicode(str(data[1])) + u'% wilgotności')
	except tweetpony.APIError as err:
		print "Oops, something went wrong! Twitter returned error #%i and said: %s" % (err.code, err.description)
	else:
		print "Yay! Your tweet has been sent!"

if __name__ == "__main__":
	main()